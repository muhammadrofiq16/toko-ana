<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashierController extends Controller
{
    public function index()
    {
        $products   = Product::with('units.priceTier')->where('stock', '>', 0)->orderBy('name')->get();
        $categories = Product::select('category')->distinct()->pluck('category');

        return view('kasir.index', compact('products', 'categories'));
    }

    public function searchProduct(Request $request)
    {
        $search   = $request->get('q', '');
        $products = Product::with('units.priceTier')
            ->where('stock', '>', 0)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku',     'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->take(20)
            ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        // 1. Normalize payment method (Kasir POS: Hanya Tunai & Transfer)
        $rawMethod = strtolower($request->payment_method ?? '');
        $paymentMethod = match($rawMethod) {
            'tunai'    => 'Tunai',
            'transfer' => 'Transfer',
            default    => ucfirst($request->payment_method ?? '')
        };
        $request->merge(['payment_method' => $paymentMethod]);

        // 2. Sanitize customer_id
        if (empty($request->customer_id)) {
            $request->merge(['customer_id' => null]);
        }

        // 3. Resolve customer_name if customer_id is provided
        if ($request->customer_id) {
            $customer = Customer::find($request->customer_id);
            if ($customer && empty($request->customer_name)) {
                $request->merge(['customer_name' => $customer->name]);
            }
        }

        $validated = $request->validate([
            'items'          => 'required|string',
            'payment_method' => 'required|string|in:Tunai,Transfer',
            'paid_amount'    => 'required|numeric|min:0',
            'sale_mode'      => 'required|in:eceran,grosir',
            'customer_id'    => 'nullable|exists:customers,id',
            'customer_name'  => 'nullable|string',
            'notes'          => 'nullable|string',
        ]);

        $cartItems = json_decode($request->items, true);

        if (empty($cartItems)) {
            return back()->with('error', 'Keranjang belanja kosong.');
        }

        $saleMode = $request->sale_mode;

        try {
            return DB::transaction(function () use ($request, $cartItems, $saleMode, $paymentMethod) {
                // Generate collision-proof invoice number
                $todayPrefix = 'INV-' . Carbon::today()->format('Ymd') . '-';
                $lastTrans = Transaction::where('invoice_number', 'like', $todayPrefix . '%')
                    ->orderByDesc('id')
                    ->first();

                $seq = 1;
                if ($lastTrans) {
                    $lastSeq = (int) str_replace($todayPrefix, '', $lastTrans->invoice_number);
                    $seq = $lastSeq + 1;
                }

                $invoiceNumber = $todayPrefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
                while (Transaction::where('invoice_number', $invoiceNumber)->exists()) {
                    $seq++;
                    $invoiceNumber = $todayPrefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
                }

                // Calculate total & build items
                $totalAmount   = 0;
                $itemsToCreate = [];
                $cashierName   = Auth::check() ? Auth::user()->name : \App\Models\Setting::get('cashier_name', 'Budi Santoso');
                $customer      = !empty($request->customer_id) ? Customer::find($request->customer_id) : null;
                $customerType  = $customer ? $customer->type : null;

                foreach ($cartItems as $item) {
                    $product = Product::find($item['product_id']);
                    if (!$product) continue;

                    $qty = (int) $item['quantity'];
                    if ($qty <= 0) continue;

                    $selectedUnitId = $item['selected_unit_id'] ?? null;

                    // Lookup the product unit
                    $productUnit = null;
                    if ($selectedUnitId) {
                        $productUnit = ProductUnit::with('priceTier')->find($selectedUnitId);
                    }

                    // Fallback to base unit if not found
                    if (!$productUnit || $productUnit->product_id !== $product->id) {
                        $productUnit = $product->units()->where('sort_order', 0)->with('priceTier')->first();
                    }

                    if (!$productUnit) continue;

                    // Determine price based on mode + qty + customer type
                    $unitPrice   = $productUnit->getPriceFor($saleMode, $qty, $customerType);
                    $unitName    = $productUnit->unit_name;
                    $stockDeduct = $qty * $productUnit->conversion_factor;

                    $subtotal     = $unitPrice * $qty;
                    $totalAmount += $subtotal;

                    $itemsToCreate[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'quantity'     => $qty,
                        'unit'         => $unitName,
                        'unit_price'   => $unitPrice,
                        'subtotal'     => $subtotal,
                    ];

                    // Reduce stock in base units
                    $product->stock = max(0, $product->stock - $stockDeduct);
                    $product->save();

                    // Record stock movement
                    StockMovement::create([
                        'product_id'      => $product->id,
                        'type'            => 'sale',
                        'quantity'        => $stockDeduct,
                        'unit'            => $product->unit,
                        'source_category' => 'Kasir POS - ' . ucfirst($saleMode),
                        'notes'           => 'Struk ' . $invoiceNumber . ' (' . $qty . ' ' . $unitName . ')',
                        'user_name'       => $cashierName,
                    ]);
                }

                if (empty($itemsToCreate)) {
                    throw new \Exception('Tidak ada item valid dalam keranjang untuk diproses.');
                }

                $paidAmount = (float) $request->paid_amount;
                // For Transfer, payment is always exact total amount
                if ($paymentMethod === 'Transfer' || $paidAmount <= 0) {
                    $paidAmount = $totalAmount;
                }

                $changeAmount = max(0, $paidAmount - $totalAmount);

                // Create transaction
                $transaction = Transaction::create([
                    'invoice_number' => $invoiceNumber,
                    'payment_method' => $paymentMethod,
                    'total_amount'   => $totalAmount,
                    'paid_amount'    => $paidAmount,
                    'change_amount'  => $changeAmount,
                    'cashier_name'   => $cashierName,
                    'customer_name'  => $request->customer_name ?: 'Pelanggan Umum',
                    'customer_id'    => $request->customer_id ?: null,
                    'sale_mode'      => ($customerType === 'reseller' || $saleMode === 'grosir') ? 'grosir' : 'eceran',
                    'status'         => 'success',
                    'notes'          => $request->notes,
                ]);

                // Create transaction items
                foreach ($itemsToCreate as $itemData) {
                    $transaction->items()->create($itemData);
                }

                return redirect()->route('kasir.receipt', $transaction->id)->with('success', 'Transaksi berhasil disimpan!');
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage())->withInput();
        }
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load('items.product', 'customer');
        return view('kasir.receipt', compact('transaction'));
    }
}
