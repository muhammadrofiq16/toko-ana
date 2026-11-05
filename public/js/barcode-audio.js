/**
 * Barcode Scanner Audio & Notification Helper
 * Menggunakan Web Audio API murni tanpa ketergantungan file audio eksternal.
 */
class BarcodeFeedback {
    constructor() {
        this.audioCtx = null;
    }

    getAudioContext() {
        if (!this.audioCtx) {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (AudioContext) {
                this.audioCtx = new AudioContext();
            }
        }
        if (this.audioCtx && this.audioCtx.state === 'suspended') {
            this.audioCtx.resume();
        }
        return this.audioCtx;
    }

    /**
     * Nada 'Beep' sukses ala mesin kasir retail minimarket (880Hz, 70ms)
     */
    playSuccess() {
        try {
            const ctx = this.getAudioContext();
            if (!ctx) return;

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime); // Nada A5

            gain.gain.setValueAtTime(0.001, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.25, ctx.currentTime + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.08);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.08);
        } catch (e) {
            console.debug('Audio error:', e);
        }
    }

    /**
     * Nada 'Buzz' peringatan jika barcode tidak ditemukan
     */
    playError() {
        try {
            const ctx = this.getAudioContext();
            if (!ctx) return;

            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(220, ctx.currentTime); // Nada A3 rendah

            gain.gain.setValueAtTime(0.2, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.25);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.25);
        } catch (e) {
            console.debug('Audio error:', e);
        }
    }

    /**
     * Tampilkan toast popup kecil di layar
     */
    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        const isSuccess = type === 'success';
        toast.className = `fixed bottom-6 right-6 z-[9999] px-4 py-3 rounded-xl shadow-xl text-xs font-bold flex items-center gap-2 transform transition-all duration-300 translate-y-4 opacity-0 ${
            isSuccess 
                ? 'bg-emerald-600 text-white shadow-emerald-600/30' 
                : 'bg-rose-600 text-white shadow-rose-600/30'
        }`;
        
        toast.innerHTML = `
            <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                ${isSuccess 
                    ? '<path d="M20 6L9 17l-5-5"></path>' 
                    : '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line>'}
            </svg>
            <span>${message}</span>
        `;

        document.body.appendChild(toast);

        // Animate in
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-4', 'opacity-0');
        });

        // Auto remove
        setTimeout(() => {
            toast.classList.add('translate-y-4', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 2500);
    }
}

window.barcodeFeedback = new BarcodeFeedback();
