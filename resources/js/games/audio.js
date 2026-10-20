// Procedural stadium sound (no audio files): crowd bed, claps, whistle, kick, net, post, roars.
// Starts on the first user gesture (browsers block audio before one).

export function createAudio(isClapping) {
    const SFX = {
        ctx: null, out: null, crowd: null, crowdF: null, muted: false, noise: null, clapT: null,

        init() {
            if (this.ctx) {
                if (this.ctx.state === 'suspended') this.ctx.resume();
                return;
            }
            const C = window.AudioContext || window.webkitAudioContext;
            if (!C) return;
            const c = (this.ctx = new C());
            const comp = c.createDynamicsCompressor();
            comp.threshold.value = -14;
            comp.ratio.value = 4;
            comp.connect(c.destination);
            this.out = c.createGain();
            this.out.gain.value = 0.9;
            this.out.connect(comp);

            const len = c.sampleRate * 3, buf = c.createBuffer(1, len, c.sampleRate), d = buf.getChannelData(0);
            let b0 = 0, b1 = 0, b2 = 0;
            for (let i = 0; i < len; i++) {
                const w = Math.random() * 2 - 1;
                b0 = 0.997 * b0 + w * 0.03; b1 = 0.985 * b1 + w * 0.06; b2 = 0.95 * b2 + w * 0.15;
                d[i] = (b0 + b1 + b2 + w * 0.18) * 0.45;
            }
            this.noise = buf;

            // crowd bed: two layered filtered noise beds with slow swells
            this.crowd = c.createGain();
            this.crowd.gain.value = 0;
            this.crowd.connect(this.out);
            this.crowdF = c.createBiquadFilter();
            this.crowdF.type = 'bandpass';
            this.crowdF.frequency.value = 700;
            this.crowdF.Q.value = 0.5;
            this.crowdF.connect(this.crowd);
            [[1, 0], [0.6, 1.3]].forEach(([g, off]) => {
                const s = c.createBufferSource();
                s.buffer = buf;
                s.loop = true;
                const gg = c.createGain();
                gg.gain.value = g;
                const lfo = c.createOscillator(), lg = c.createGain();
                lfo.frequency.value = 0.13 + off * 0.07;
                lg.gain.value = 0.35 * g;
                lfo.connect(lg);
                lg.connect(gg.gain);
                s.connect(gg);
                gg.connect(this.crowdF);
                s.start(0, off);
                lfo.start();
            });
        },

        ok() { return this.ctx && !this.muted; },

        src(dur) {
            const s = this.ctx.createBufferSource();
            s.buffer = this.noise;
            return [s, Math.random() * (this.noise.duration - dur - 0.1)];
        },

        env(g, t, a, peak, dec) {
            g.gain.setValueAtTime(0.0001, t);
            g.gain.exponentialRampToValueAtTime(peak, t + a);
            g.gain.exponentialRampToValueAtTime(0.0001, t + a + dec);
        },

        burst(type, freq, q, peak, a, dec, when, sweepTo) {
            if (!this.ok()) return;
            const c = this.ctx, t = c.currentTime + (when || 0);
            const [s, off] = this.src(a + dec);
            const f = c.createBiquadFilter();
            f.type = type;
            f.frequency.setValueAtTime(freq, t);
            if (sweepTo) f.frequency.exponentialRampToValueAtTime(sweepTo, t + a + dec);
            f.Q.value = q;
            const g = c.createGain();
            this.env(g, t, a, peak, dec);
            s.connect(f); f.connect(g); g.connect(this.out);
            s.start(t, Math.max(0, off), a + dec + 0.05);
        },

        tone(freq, to, type, peak, a, dec, when) {
            if (!this.ok()) return;
            const c = this.ctx, t = c.currentTime + (when || 0);
            const o = c.createOscillator();
            o.type = type;
            o.frequency.setValueAtTime(freq, t);
            if (to) o.frequency.exponentialRampToValueAtTime(to, t + a + dec);
            const g = c.createGain();
            this.env(g, t, a, peak, dec);
            o.connect(g); g.connect(this.out);
            o.start(t);
            o.stop(t + a + dec + 0.05);
        },

        crowdTo(v, secs, freq) {
            if (!this.ctx) return;
            const t = this.ctx.currentTime, g = this.crowd.gain;
            g.cancelScheduledValues(t);
            g.setValueAtTime(g.value, t);
            g.linearRampToValueAtTime(this.muted ? 0 : v, t + secs);
            if (freq) {
                this.crowdF.frequency.cancelScheduledValues(t);
                this.crowdF.frequency.setValueAtTime(this.crowdF.frequency.value, t);
                this.crowdF.frequency.exponentialRampToValueAtTime(freq, t + secs);
            }
        },

        start() { this.crowdTo(0.38, 1.5, 700); this.claps(true); },

        claps(on) {
            clearInterval(this.clapT);
            if (!on) return;
            let beat = 0;
            const pat = [1, 0, 1, 0, 1, 1, 1, 0];
            this.clapT = setInterval(() => {
                if (pat[beat++ % 8] && isClapping()) {
                    for (let k = 0; k < 5; k++) this.burst('highpass', 1400 + Math.random() * 900, 0.8, 0.11, 0.002, 0.07, Math.random() * 0.04);
                }
            }, 190);
        },

        whistle() { this.tone(2900, 2850, 'square', 0.05, 0.02, 0.32); this.tone(3100, 3050, 'sine', 0.08, 0.02, 0.32); },
        hush(on) { on ? this.crowdTo(0.16, 0.4, 520) : this.crowdTo(0.38, 0.6, 700); },
        kick(p) {
            const v = 0.5 + Math.min(p, 1.3) * 0.5;
            this.tone(150, 42, 'sine', 0.9 * v, 0.003, 0.18);
            this.burst('highpass', 1800, 0.7, 0.5 * v, 0.001, 0.045);
            this.burst('lowpass', 400, 1, 0.4 * v, 0.002, 0.09);
        },
        net() { this.burst('bandpass', 2600, 1.2, 0.35, 0.01, 0.28, 0, 1200); this.tone(90, 60, 'sine', 0.25, 0.005, 0.2); },
        roar() {
            this.crowdTo(1.25, 0.25, 1400);
            setTimeout(() => this.crowdTo(0.7, 2.2, 900), 900);
            setTimeout(() => this.crowdTo(0.38, 2, 700), 3200);
            this.burst('bandpass', 1100, 0.6, 0.55, 0.15, 1.8, 0, 1700);
            this.burst('highpass', 2500, 0.5, 0.25, 0.05, 1.4);
        },
        groan() {
            this.crowdTo(0.9, 0.2, 480);
            setTimeout(() => this.crowdTo(0.35, 1.8, 700), 600);
            this.burst('bandpass', 420, 1.4, 0.5, 0.12, 1.3, 0, 240);
        },
        ooh() {
            this.burst('bandpass', 650, 1.6, 0.45, 0.15, 1.1, 0, 380);
            this.crowdTo(0.95, 0.2, 900);
            setTimeout(() => this.crowdTo(0.38, 1.6, 700), 700);
        },
        glove() { this.burst('lowpass', 700, 1, 0.7, 0.002, 0.09); this.tone(110, 70, 'sine', 0.4, 0.003, 0.1); },
        post() {
            [523, 1307, 2140, 3330].forEach((f, i) => this.tone(f, f * 0.995, 'sine', 0.25 / (i + 1), 0.002, 1.1 - i * 0.2));
            this.burst('highpass', 3000, 1, 0.3, 0.001, 0.05);
        },
        final() {
            [0, 0.45, 0.9].forEach((w, i) => {
                this.tone(2900, 2850, 'square', 0.05, 0.02, i === 2 ? 0.9 : 0.3, w);
                this.tone(3100, 3050, 'sine', 0.08, 0.02, i === 2 ? 0.9 : 0.3, w);
            });
            setTimeout(() => this.crowdTo(0.9, 0.5, 1000), 1300);
            setTimeout(() => this.crowdTo(0.3, 3, 700), 2500);
        },
        setMuted(m) {
            this.muted = m;
            if (this.ctx) this.crowd.gain.setTargetAtTime(m ? 0 : 0.38, this.ctx.currentTime, 0.1);
        },
    };

    return SFX;
}
