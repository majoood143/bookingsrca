import { loadMindAR } from './mindar.js';

const root = document.getElementById('ar-scanner');
const { mindUrl, cards, i18n } = JSON.parse(document.getElementById('ar-data').textContent);

const video = root.querySelector('video');
const status = root.querySelector('[data-status]');
const card = root.querySelector('[data-card]');
const listen = card.querySelector('[data-listen]');
const audio = new Audio();
let current = null;
let stream = null;

const setStatus = (text) => {
    status.textContent = text;
    status.hidden = !text;
};

function show(index) {
    const data = cards[index];
    // Null = item unpublished since the zone was compiled; ignore that image.
    if (!data || index === current) return;

    current = index;
    audio.pause();
    card.querySelector('[data-title]').textContent = data.title;
    card.querySelector('[data-summary]').textContent = data.summary ?? '';
    card.querySelector('[data-link]').href = data.url;
    listen.hidden = !data.audio;
    listen.textContent = listen.dataset.labelPlay;
    if (data.audio) audio.src = data.audio;
    card.hidden = false;
    setStatus('');
}

listen.addEventListener('click', () => (audio.paused ? audio.play() : audio.pause()));
audio.addEventListener('play', () => (listen.textContent = listen.dataset.labelPause));
audio.addEventListener('pause', () => (listen.textContent = listen.dataset.labelPlay));
audio.addEventListener('ended', () => (listen.textContent = listen.dataset.labelPlay));

card.querySelector('[data-close]').addEventListener('click', () => {
    audio.pause();
    card.hidden = true;
    current = null; // lets the same item open again
    setStatus(i18n.point);
});

async function start() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
    } catch {
        setStatus(i18n.camera_denied);
        return;
    }

    try {
        video.srcObject = stream;
        await video.play();
        // MindAR reads frames at the element's width/height attributes; unset they are 0 and it sees a blank image.
        video.setAttribute('width', video.videoWidth);
        video.setAttribute('height', video.videoHeight);

        const { Controller } = await loadMindAR();
        const controller = new Controller({
            inputWidth: video.videoWidth,
            inputHeight: video.videoHeight,
            maxTrack: 1,
            onUpdate: ({ type, targetIndex, worldMatrix }) => {
                if (type === 'updateMatrix' && worldMatrix) show(targetIndex);
            },
        });
        await controller.addImageTargets(mindUrl);
        controller.dummyRun(video);
        controller.processVideo(video);
        setStatus(i18n.point);

        window.addEventListener('pagehide', () => {
            controller.stopProcessVideo();
            stream.getTracks().forEach((track) => track.stop());
        });
    } catch (error) {
        console.error(error);
        setStatus(i18n.error);
    }
}

start();
