import { loadMindAR } from './mindar.js';

const loadImage = (url) =>
    new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = () => reject(new Error(`Cannot load ${url}`));
        img.src = url;
    });

// Alpine component for the admin "Compile AR targets" modal.
window.arCompiler = ({ uploadUrl, targets, csrf }) => ({
    progress: 0,
    state: 'idle', // idle | running | done | error
    message: '',

    async run() {
        this.state = 'running';
        this.progress = 0;
        try {
            const { Compiler } = await loadMindAR();
            const images = await Promise.all(targets.map((t) => loadImage(t.url)));
            const compiler = new Compiler();
            await compiler.compileImageTargets(images, (p) => (this.progress = Math.round(p)));
            const buffer = await compiler.exportData();

            const body = new FormData();
            body.append('file', new Blob([buffer], { type: 'application/octet-stream' }), 'targets.mind');
            targets.forEach((t) => body.append('item_ids[]', t.id));

            const response = await fetch(uploadUrl, {
                method: 'POST',
                body,
                headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
            });
            if (!response.ok) throw new Error((await response.json().catch(() => ({}))).message || response.statusText);

            this.state = 'done';
        } catch (error) {
            this.state = 'error';
            this.message = error.message;
        }
    },
});
