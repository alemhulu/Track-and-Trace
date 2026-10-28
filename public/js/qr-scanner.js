// Alpine data for <x-scan.scanner>; loaded globally so scanners inside lazily rendered tabs work too
window.qrScanner = function (method) {
    return {
        typed: '',
        running: false,
        paused: false,
        error: '',
        camera: null,
        readerId: 'qr-reader-' + Math.random().toString(36).slice(2),

        // Livewire screens get a server call; the offline field app listens for the browser event
        component() {
            const root = this.$el.closest('[wire\\:id]');
            return root && window.Livewire ? window.Livewire.find(root.getAttribute('wire:id')) : null;
        },

        uuid() {
            if (window.crypto && window.crypto.randomUUID) {
                return window.crypto.randomUUID();
            }
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) => {
                const r = Math.random() * 16 | 0;
                return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
            });
        },

        send(text) {
            text = (text || '').trim();
            if (!text) {
                return;
            }
            const component = this.component();
            if (component) {
                component.call(method, text, this.uuid());
            } else {
                window.dispatchEvent(new CustomEvent('qr-scanned', { detail: { text: text } }));
            }
        },

        submitTyped() {
            this.send(this.typed);
            this.typed = '';
            this.$refs.typed.focus();
        },

        // Short buzz for a clean scan, long buzz and low tone for anything that needs attention
        feedback(detail) {
            const ok = detail && detail.ok;
            if (navigator.vibrate) {
                navigator.vibrate(ok ? 60 : [200, 80, 200]);
            }
            try {
                const audio = new (window.AudioContext || window.webkitAudioContext)();
                const tone = audio.createOscillator();
                tone.frequency.value = ok ? 880 : 220;
                tone.connect(audio.destination);
                tone.start();
                tone.stop(audio.currentTime + (ok ? 0.08 : 0.35));
            } catch (e) {}
        },

        loadLibrary() {
            if (window.Html5Qrcode) {
                return Promise.resolve();
            }
            return new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = '/js/vendor/html5-qrcode.min.js';
                script.onload = resolve;
                script.onerror = () => reject(new Error('The camera scanner could not be loaded. Open this page once while online, or use a handheld scanner or type the code.'));
                document.head.appendChild(script);
            });
        },

        async start() {
            this.error = '';
            if (!window.isSecureContext) {
                this.error = 'The camera only works over HTTPS. Use a handheld scanner or type the code.';
                return;
            }
            try {
                await this.loadLibrary();
                this.running = true;
                await this.$nextTick();
                this.camera = new window.Html5Qrcode(this.readerId);
                await this.camera.start({ facingMode: 'environment' }, { fps: 10, qrbox: 240 }, (text) => {
                    if (this.paused) {
                        return;
                    }
                    // Packages for one zone share the same QR text, so pause instead of ignoring repeats
                    this.paused = true;
                    this.send(text);
                    setTimeout(() => this.paused = false, 1500);
                });
            } catch (e) {
                this.running = false;
                this.error = e && e.message ? e.message : 'The camera could not be started.';
            }
        },

        async stop() {
            if (this.camera) {
                try { await this.camera.stop(); } catch (e) {}
                this.camera = null;
            }
            this.running = false;
        },
    };
};
