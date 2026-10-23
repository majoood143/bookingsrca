// The public leaderboard (and its full-screen stadium display mode): the branded stadium with a
// 3D podium for the top three in front of the goal, the list below, refreshed on a timer.
// Only masked mobiles (955**928) ever reach this page.
import * as THREE from 'three';
import { call } from './api.js';
import { fmtTime, renderBoard } from './board.js';
import { createStadium, webglAvailable } from './stadium.js';

const C = window.__GAME__;
const T = C.i18n;
const $ = (s) => document.querySelector(s);
const t = (key, params = {}) => Object.entries(params).reduce((s, [k, v]) => s.replaceAll(':' + k, v), T[key] ?? key);
const FONT = 'Almarai, "Segoe UI", sans-serif';
const app = $('#gApp');

let board = C.board;

function renderList() {
    renderBoard($('#gBoard'), board, { shots: C.campaign.shots, you: t('you'), empty: t('empty') });
    $('#gBoardMeta').textContent = (board.frozen ? t('frozen') + ' · ' : '') + (board.players === 1 ? t('players_count_one') : t('players_count', { n: board.players }));
    const updated = new Date(board.updated_at);
    $('#gUpdated').textContent = t('updated') + ' ' + updated.toLocaleTimeString(C.locale === 'ar' ? 'ar-OM' : 'en-GB', { hour: '2-digit', minute: '2-digit' });
}

async function refresh() {
    try {
        board = await call(C.urls.leaderboard, { i18n: T });
        renderList();
        podium && podium.update(board.rows);
    } catch (e) { /* keep the last board on screen */ }
}

/* ================= 3D PODIUM ================= */
let podium = null;

function createPodium(stadium) {
    const { scene } = stadium;
    const group = new THREE.Group();
    group.position.set(0, 0, 3.2);
    scene.add(group);

    const spec = [
        { rank: 2, x: -2.3, h: 1.2, color: '#c9ced6' },
        { rank: 1, x: 0, h: 1.75, color: C.brand.accent },
        { rank: 3, x: 2.3, h: 0.85, color: '#d08a4e' },
    ];

    const blocks = spec.map((s) => {
        const mat = new THREE.MeshLambertMaterial({ color: new THREE.Color(C.brand.primary), emissive: new THREE.Color(C.brand.primary), emissiveIntensity: 0.35 });
        const mesh = new THREE.Mesh(new THREE.BoxGeometry(2.1, s.h, 1.4), mat);
        mesh.position.set(s.x, s.h / 2, 0);
        mesh.castShadow = true;
        mesh.receiveShadow = true;
        group.add(mesh);

        const trim = new THREE.Mesh(new THREE.BoxGeometry(2.14, 0.08, 1.44), new THREE.MeshLambertMaterial({ color: new THREE.Color(s.color) }));
        trim.position.set(s.x, s.h, 0);
        group.add(trim);

        // front face: rank, masked mobile, score
        const c = document.createElement('canvas');
        c.width = 512;
        c.height = 384;
        const tex = new THREE.CanvasTexture(c);
        tex.colorSpace = THREE.SRGBColorSpace;
        const face = new THREE.Mesh(new THREE.PlaneGeometry(2.0, Math.min(s.h - 0.1, 1.5)), new THREE.MeshBasicMaterial({ map: tex, transparent: true }));
        face.position.set(s.x, Math.min(s.h - 0.1, 1.5) / 2 + 0.04, 0.71);
        group.add(face);

        // floating label above the block
        const lc = document.createElement('canvas');
        lc.width = 512;
        lc.height = 160;
        const ltex = new THREE.CanvasTexture(lc);
        ltex.colorSpace = THREE.SRGBColorSpace;
        const label = new THREE.Sprite(new THREE.SpriteMaterial({ map: ltex, transparent: true, depthWrite: false }));
        label.scale.set(2.4, 0.75, 1);
        label.position.set(s.x, s.h + 0.7, 0);
        group.add(label);

        return { ...s, c, tex, lc, ltex, label };
    });

    const draw = (b, row) => {
        let g = b.c.getContext('2d');
        g.clearRect(0, 0, 512, 384);
        g.fillStyle = b.color;
        g.textAlign = 'center';
        g.textBaseline = 'middle';
        g.font = `800 ${b.rank === 1 ? 210 : 180}px ${FONT}`;
        g.fillText(String(b.rank), 256, 170);
        b.tex.needsUpdate = true;

        g = b.lc.getContext('2d');
        g.clearRect(0, 0, 512, 160);
        if (row) {
            g.fillStyle = 'rgba(8,18,13,.78)';
            g.beginPath();
            g.roundRect(16, 10, 480, 140, 28);
            g.fill();
            g.fillStyle = '#ffffff';
            g.textAlign = 'center';
            g.textBaseline = 'middle';
            g.font = `800 58px ${FONT}`;
            g.direction = 'ltr';
            g.fillText(row.player, 256, 62);
            g.fillStyle = b.color;
            g.font = `700 38px ${FONT}`;
            g.fillText(row.goals + '/' + C.campaign.shots + '  ·  ' + fmtTime(row.duration_ms), 256, 118);
        }
        b.ltex.needsUpdate = true;
    };

    const update = (rows) => blocks.forEach((b) => draw(b, rows.find((r) => r.rank === b.rank)));
    update(board.rows);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(() => update(board.rows));

    return {
        update,
        animate: (time) => blocks.forEach((b, i) => (b.label.position.y = b.h + 0.7 + Math.sin(time * 1.4 + i) * 0.06)),
    };
}

/* ================= BOOT ================= */
renderList();
setInterval(refresh, C.display ? 15000 : 30000);

if (webglAvailable()) {
    const stadium = createStadium({
        canvas: $('#gScene'),
        brand: C.brand,
        texts: { board: C.campaign.boardText, headline: C.campaign.headline, siteName: C.brand.site_name, prize: C.campaign.prizeText },
        withPlayers: false,
    });
    podium = createPodium(stadium);
    const { camera } = stadium;
    const resize = () => stadium.resize(app);
    window.addEventListener('resize', resize);
    resize();

    // aimed low so the podium sits in the upper half, clear of the list below it
    const base = new THREE.Vector3(0, 3.6, 11.5);
    const target = new THREE.Vector3(0, 0.2, 2.2);
    const clock = new THREE.Clock();
    let time = 0;
    const loop = () => {
        // scheduled first: a stadium screen must keep running whatever happens in one frame
        requestAnimationFrame(loop);
        try {
            const dt = Math.min(clock.getDelta(), 0.05);
            time += dt;
            camera.position.set(base.x + Math.sin(time * 0.12) * 2.2, base.y + Math.sin(time * 0.2) * 0.15, base.z);
            camera.lookAt(target.x, target.y, target.z);
            podium.animate(time);
            stadium.update(dt);
            stadium.render();
        } catch (e) {
            console.error(e);
        }
    };
    loop();
}

$('#gLoading').hidden = true;
