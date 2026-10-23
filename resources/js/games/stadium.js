// The floodlit stadium shared by the game and the public leaderboard: pitch, goal and net, crowd
// stands, a tifo and LED boards painted with the client's branding (logo, colors, texts), the
// goalkeeper and the striker (characters.js), and the effects (net bulge, confetti, ball trail).
// Everything brand-specific comes from `brand` and `texts`.
import * as THREE from 'three';
import { RoomEnvironment } from 'three/addons/environments/RoomEnvironment.js';
import { createKeeper, createStriker } from './characters.js';

export const GW = 3.66; // half goal width (m)
export const GH = 2.44; // goal height
export const PR = 0.06; // post radius
export const BR = 0.11; // ball radius
export const SPOT = new THREE.Vector3(0, BR, 11);
export const CAM = new THREE.Vector3(0, 1.7, 15.6);
export const LOOK = new THREE.Vector3(0, 1.05, 0);

export const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
export const lerp = (a, b, t) => a + (b - a) * t;
export const easeOut = (t) => 1 - Math.pow(1 - t, 3);

const isArabic = (s) => /[؀-ۿ]/.test(s || '');
const FONT = 'Almarai, "Segoe UI", sans-serif';

function shade(hex, amount) {
    const c = new THREE.Color(hex);
    c.offsetHSL(0, 0, amount);
    return '#' + c.getHexString();
}

/** Fit text on one line inside maxWidth by shrinking the font. */
function fitText(g, text, maxWidth, size, weight = 800) {
    let fs = size;
    g.font = `${weight} ${fs}px ${FONT}`;
    while (g.measureText(text).width > maxWidth && fs > 14) {
        fs -= 2;
        g.font = `${weight} ${fs}px ${FONT}`;
    }
    g.direction = isArabic(text) ? 'rtl' : 'ltr';
    return fs;
}

function loadImage(url) {
    return new Promise((resolve) => {
        if (!url) return resolve(null);
        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => resolve(img);
        img.onerror = () => resolve(null);
        img.src = url;
    });
}

export function webglAvailable() {
    try {
        const c = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
    } catch (e) {
        return false;
    }
}

export function createStadium({ canvas, brand, texts, withPlayers = true }) {
    const lowEnd = (navigator.hardwareConcurrency || 8) <= 4 || /Android [4-8]\./.test(navigator.userAgent);
    const renderer = new THREE.WebGLRenderer({ canvas, antialias: !lowEnd, powerPreference: 'high-performance' });
    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, lowEnd ? 1.5 : 2));
    renderer.shadowMap.enabled = !lowEnd;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.08;

    const scene = new THREE.Scene();
    // soft image-based light for the players' kits and skin
    const pmrem = new THREE.PMREMGenerator(renderer);
    scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
    scene.environmentIntensity = 0.35;
    pmrem.dispose();
    const camera = new THREE.PerspectiveCamera(50, 1, 0.1, 250);
    camera.position.copy(CAM);
    camera.lookAt(LOOK);

    const canvasTex = (w, h, draw) => {
        const c = document.createElement('canvas');
        c.width = w;
        c.height = h;
        draw(c.getContext('2d'), w, h);
        const t = new THREE.CanvasTexture(c);
        t.colorSpace = THREE.SRGBColorSpace;
        t.anisotropy = Math.min(8, renderer.capabilities.getMaxAnisotropy());
        return t;
    };

    scene.background = canvasTex(4, 256, (g, w, h) => {
        const gr = g.createLinearGradient(0, 0, 0, h);
        gr.addColorStop(0, '#16243a');
        gr.addColorStop(0.55, '#0c1a1a');
        gr.addColorStop(1, '#08120d');
        g.fillStyle = gr;
        g.fillRect(0, 0, w, h);
    });
    scene.fog = new THREE.Fog(0x0a1513, 40, 95);

    // lights (physically based intensities: the legacy values times π)
    scene.add(new THREE.HemisphereLight(0xdfe8ff, 0x1a3020, 0.75 * Math.PI));
    const sun = new THREE.DirectionalLight(0xfff1d6, 0.85 * Math.PI);
    sun.position.set(7, 20, 14);
    sun.target.position.set(0, 0, 4);
    sun.castShadow = renderer.shadowMap.enabled;
    Object.assign(sun.shadow.camera, { left: -9, right: 9, top: 12, bottom: -6, near: 2, far: 50 });
    sun.shadow.mapSize.set(1024, 1024);
    sun.shadow.bias = -0.0005;
    scene.add(sun, sun.target);
    const fill = new THREE.DirectionalLight(0xcfe0ff, 0.3 * Math.PI);
    fill.position.set(-10, 14, -8);
    scene.add(fill);

    // pitch
    const grass = canvasTex(128, 256, (g, w, h) => {
        g.fillStyle = '#2e7a3e';
        g.fillRect(0, 0, w, h / 2);
        g.fillStyle = '#276a35';
        g.fillRect(0, h / 2, w, h / 2);
        for (let i = 0; i < 4000; i++) {
            g.fillStyle = Math.random() < 0.5 ? 'rgba(10,40,15,.12)' : 'rgba(120,180,90,.08)';
            g.fillRect(Math.random() * w, Math.random() * h, 1.5, 2);
        }
    });
    grass.wrapS = grass.wrapT = THREE.RepeatWrapping;
    grass.repeat.set(5, 8);
    const pitch = new THREE.Mesh(new THREE.PlaneGeometry(100, 90), new THREE.MeshLambertMaterial({ map: grass }));
    pitch.rotation.x = -Math.PI / 2;
    pitch.position.z = 15;
    pitch.receiveShadow = true;
    scene.add(pitch);

    const lineMat = new THREE.MeshBasicMaterial({ color: 0xe9eee4 });
    const line = (x1, z1, x2, z2, w = 0.12) => {
        const len = Math.hypot(x2 - x1, z2 - z1);
        const m = new THREE.Mesh(new THREE.PlaneGeometry(len, w), lineMat);
        m.rotation.x = -Math.PI / 2;
        m.rotation.z = -Math.atan2(z2 - z1, x2 - x1);
        m.position.set((x1 + x2) / 2, 0.012, (z1 + z2) / 2);
        scene.add(m);
    };
    line(-34, 0, 34, 0);
    line(-9.16, 0, -9.16, 5.5); line(9.16, 0, 9.16, 5.5); line(-9.16, 5.5, 9.16, 5.5);
    line(-20.16, 0, -20.16, 16.5); line(20.16, 0, 20.16, 16.5); line(-20.16, 16.5, 20.16, 16.5);
    const spot = new THREE.Mesh(new THREE.CircleGeometry(0.13, 24), lineMat);
    spot.rotation.x = -Math.PI / 2;
    spot.position.set(0, 0.013, 11);
    scene.add(spot);
    const arc = new THREE.Mesh(new THREE.RingGeometry(9.09, 9.21, 48, 1, 3.786, 1.853), lineMat);
    arc.rotation.x = -Math.PI / 2;
    arc.position.set(0, 0.012, 11);
    scene.add(arc);

    // goal frame
    const postMat = new THREE.MeshLambertMaterial({ color: 0xffffff, emissive: 0x333333 });
    [-1, 1].forEach((s) => {
        const p = new THREE.Mesh(new THREE.CylinderGeometry(PR, PR, GH + PR, 16), postMat);
        p.position.set(s * (GW + PR), (GH + PR) / 2, 0);
        p.castShadow = true;
        scene.add(p);
    });
    const bar = new THREE.Mesh(new THREE.CylinderGeometry(PR, PR, 2 * (GW + 2 * PR), 16), postMat);
    bar.rotation.z = Math.PI / 2;
    bar.position.set(0, GH + PR, 0);
    bar.castShadow = true;
    scene.add(bar);
    const supMat = new THREE.MeshLambertMaterial({ color: 0xbfc6bd });
    [-1, 1].forEach((s) => {
        const b = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, 2.0, 8), supMat);
        b.rotation.x = Math.PI / 2;
        b.position.set(s * (GW + 0.1), 0.02, -1.0);
        scene.add(b);
    });
    const backBar = new THREE.Mesh(new THREE.CylinderGeometry(0.025, 0.025, 2 * GW + 0.2, 8), supMat);
    backBar.rotation.z = Math.PI / 2;
    backBar.position.set(0, 0.02, -2);
    scene.add(backBar);

    // net
    const CELL = 0.14;
    const netTex = (rep) => {
        const t = canvasTex(32, 32, (g, w, h) => {
            g.strokeStyle = 'rgba(240,244,236,.8)';
            g.lineWidth = 2;
            g.strokeRect(0, 0, w, h);
        });
        t.wrapS = t.wrapT = THREE.RepeatWrapping;
        t.repeat.set(rep[0], rep[1]);
        return t;
    };
    const netMat = (t) => new THREE.MeshBasicMaterial({ map: t, transparent: true, side: THREE.DoubleSide, depthWrite: false, opacity: 0.9 });
    const backLen = Math.hypot(GH, 1);
    const netBack = new THREE.Mesh(new THREE.PlaneGeometry(2 * GW, backLen, 40, 14), netMat(netTex([(2 * GW) / CELL, backLen / CELL])));
    netBack.rotation.x = Math.atan2(1, GH);
    netBack.position.set(0, GH / 2, -1.5);
    scene.add(netBack);
    const roof = new THREE.Mesh(new THREE.PlaneGeometry(2 * GW, 1), netMat(netTex([(2 * GW) / CELL, 1 / CELL])));
    roof.rotation.x = -Math.PI / 2;
    roof.position.set(0, GH, -0.5);
    scene.add(roof);
    const sideShape = new THREE.Shape();
    sideShape.moveTo(0, 0); sideShape.lineTo(-2, 0); sideShape.lineTo(-1, GH); sideShape.lineTo(0, GH); sideShape.lineTo(0, 0);
    [-1, 1].forEach((s) => {
        const m = new THREE.Mesh(new THREE.ShapeGeometry(sideShape), netMat(netTex([1 / CELL, 1 / CELL])));
        m.rotation.y = -Math.PI / 2;
        m.position.x = s * GW;
        scene.add(m);
    });

    // stands + crowd, in the brand's colors among neutral ones
    const crowd = canvasTex(1024, 256, (g, w, h) => {
        g.fillStyle = '#0c1612';
        g.fillRect(0, 0, w, h);
        const cols = ['#e7e2d6', brand.primary, brand.secondary, brand.accent, '#3b4b44', '#8a8f86', '#ffffff'];
        for (let i = 0; i < 11000; i++) {
            g.fillStyle = cols[(Math.random() * cols.length) | 0];
            g.globalAlpha = 0.2 + Math.random() * 0.55;
            g.fillRect(Math.random() * w, Math.random() * h, 2, 3);
        }
        g.globalAlpha = 1;
        for (let r = 0; r < h; r += 14) {
            g.fillStyle = 'rgba(0,0,0,.4)';
            g.fillRect(0, r, w, 3);
        }
    });
    crowd.wrapS = THREE.RepeatWrapping;
    crowd.repeat.set(2, 1);
    const stand = new THREE.Mesh(new THREE.PlaneGeometry(130, 24), new THREE.MeshBasicMaterial({ map: crowd, color: 0x8e9890, toneMapped: false }));
    stand.position.set(0, 9.5, -22);
    stand.rotation.x = -0.42;
    scene.add(stand);
    const roofBand = new THREE.Mesh(new THREE.BoxGeometry(130, 1.6, 3), new THREE.MeshBasicMaterial({ color: 0x0b1310 }));
    roofBand.position.set(0, 21.5, -27);
    scene.add(roofBand);

    // tifo: the client's logo and name across the stand behind the goal
    const tifoCanvas = document.createElement('canvas');
    tifoCanvas.width = 1024;
    tifoCanvas.height = 420;
    const tifoTex = new THREE.CanvasTexture(tifoCanvas);
    tifoTex.colorSpace = THREE.SRGBColorSpace;
    let logoImg = null;
    const drawTifo = () => {
        const g = tifoCanvas.getContext('2d'), w = 1024, h = 420;
        for (let i = 0; i < 8; i++) {
            g.fillStyle = i % 2 ? shade(brand.primary, -0.04) : brand.primary;
            g.fillRect((i * w) / 8, 0, w / 8, h);
        }
        g.fillStyle = '#f2f5ee';
        g.fillRect(0, 0, w, 14);
        g.fillRect(0, h - 14, w, 14);
        g.fillStyle = brand.accent;
        g.fillRect(0, 14, w, 8);
        g.fillRect(0, h - 22, w, 8);
        if (logoImg) {
            const ch = 300, cw = Math.min(380, (ch * logoImg.naturalWidth) / logoImg.naturalHeight);
            g.fillStyle = 'rgba(255,255,255,.94)';
            g.beginPath();
            g.ellipse(w / 2, h / 2, Math.max(cw, ch) * 0.62, ch * 0.62, 0, 0, Math.PI * 2);
            g.fill();
            g.drawImage(logoImg, w / 2 - cw / 2, h / 2 - (cw * logoImg.naturalHeight) / logoImg.naturalWidth / 2, cw, (cw * logoImg.naturalHeight) / logoImg.naturalWidth);
        }
        g.fillStyle = '#ffffff';
        g.textAlign = 'center';
        g.textBaseline = 'middle';
        const side = logoImg ? 300 : 900;
        const left = texts.headline || '', right = texts.siteName || '';
        if (logoImg) {
            fitText(g, left, side, 60);
            g.fillText(left, 165, h / 2);
            fitText(g, right, side, 60);
            g.fillText(right, w - 165, h / 2);
        } else {
            fitText(g, left, side, 96);
            g.fillText(left, w / 2, h / 2 - 50);
            fitText(g, right, side, 72);
            g.fillText(right, w / 2, h / 2 + 60);
        }
        g.direction = 'ltr';
        tifoTex.needsUpdate = true;
    };
    const tifo = new THREE.Mesh(new THREE.PlaneGeometry(22, 9), new THREE.MeshBasicMaterial({ map: tifoTex, color: 0xc9d3cb, toneMapped: false }));
    tifo.position.set(0, 8.2, -19.9);
    tifo.rotation.x = -0.42;
    scene.add(tifo);

    // LED boards behind the goal
    const adCanvas = document.createElement('canvas');
    adCanvas.width = 2048;
    adCanvas.height = 96;
    const adTex = new THREE.CanvasTexture(adCanvas);
    adTex.colorSpace = THREE.SRGBColorSpace;
    adTex.wrapS = THREE.RepeatWrapping;
    adTex.repeat.set(2, 1);
    const drawAds = () => {
        const g = adCanvas.getContext('2d'), w = adCanvas.width, h = adCanvas.height;
        const seg = [
            [brand.primary, '#ffffff', texts.board],
            [shade(brand.primary, -0.12), '#f2f5ee', texts.siteName],
            ['#141b17', brand.accent, texts.prize || texts.headline],
            [brand.secondary, '#ffffff', texts.headline],
        ].filter((s) => s[2]);
        const sw = w / seg.length;
        seg.forEach((s, i) => {
            g.fillStyle = s[0];
            g.fillRect(i * sw, 0, sw, h);
            g.fillStyle = s[1];
            g.textAlign = 'center';
            g.textBaseline = 'middle';
            const pad = logoImg ? 70 : 20;
            fitText(g, s[2], sw - pad - 20, 52);
            g.fillText(s[2], i * sw + sw / 2 + (logoImg ? 28 : 0), h / 2 + 3);
            g.direction = 'ltr';
            if (logoImg) {
                const ch = h - 18, cw = Math.min(90, (ch * logoImg.naturalWidth) / logoImg.naturalHeight);
                g.drawImage(logoImg, i * sw + 14, (h - (cw * logoImg.naturalHeight) / logoImg.naturalWidth) / 2, cw, (cw * logoImg.naturalHeight) / logoImg.naturalWidth);
            }
        });
        adTex.needsUpdate = true;
    };
    const ads = new THREE.Mesh(new THREE.PlaneGeometry(44, 0.9), new THREE.MeshBasicMaterial({ map: adTex, toneMapped: false }));
    ads.position.set(0, 0.45, -5);
    scene.add(ads);

    // floodlight glows
    const glowTex = canvasTex(128, 128, (g, w, h) => {
        const gr = g.createRadialGradient(w / 2, h / 2, 0, w / 2, h / 2, w / 2);
        gr.addColorStop(0, 'rgba(255,248,220,1)');
        gr.addColorStop(0.18, 'rgba(255,232,170,.55)');
        gr.addColorStop(1, 'rgba(255,220,140,0)');
        g.fillStyle = gr;
        g.fillRect(0, 0, w, h);
    });
    [[-34, 27, -26], [34, 27, -26], [-16, 29, -30], [16, 29, -30]].forEach((p) => {
        const s = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowTex, blending: THREE.AdditiveBlending, depthWrite: false, fog: false }));
        s.position.set(p[0], p[1], p[2]);
        s.scale.set(14, 14, 1);
        scene.add(s);
    });

    // ball
    const ballTex = canvasTex(512, 256, (g, w, h) => {
        g.fillStyle = '#f7f7f2';
        g.fillRect(0, 0, w, h);
        g.fillStyle = '#17201b';
        const pent = (cx, cy, r) => {
            g.beginPath();
            for (let i = 0; i < 5; i++) {
                const a = -Math.PI / 2 + (i * 2 * Math.PI) / 5;
                g.lineTo(cx + Math.cos(a) * r * 1.7, cy + Math.sin(a) * r);
            }
            g.closePath();
            g.fill();
        };
        for (let k = 0; k < 5; k++) {
            pent((k * w) / 5 + 20, h * 0.22, 20);
            pent((k * w) / 5 + 71, h * 0.5, 22);
            pent((k * w) / 5 + 20, h * 0.78, 20);
        }
    });
    const ball = new THREE.Mesh(new THREE.SphereGeometry(BR, 32, 20), new THREE.MeshPhongMaterial({ map: ballTex, shininess: 40 }));
    ball.castShadow = true;
    ball.position.copy(SPOT);
    scene.add(ball);

    const castShadow = renderer.shadowMap.enabled;
    const keeper = createKeeper(scene, { kit: brand.keeper_kit, trim: '#14181a', castShadow });
    const striker = createStriker(scene, { kit: brand.kit, trim: brand.accent, castShadow });

    keeper.root.visible = withPlayers;
    striker.root.visible = withPlayers;

    // logo arrives after first paint
    drawTifo();
    drawAds();
    loadImage(brand.logo).then((img) => {
        if (!img) return;
        logoImg = img;
        keeper.setLogo(img);
        striker.setLogo(img);
        drawTifo();
        drawAds();
    });
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            drawTifo();
            drawAds();
            striker.redraw();
            keeper.body.redraw();
        });
    }

    /* ---- effects ---- */

    // net bulge where the ball hits the back net
    const netPos = netBack.geometry.attributes.position;
    const netBase = netPos.array.slice();
    let bulge = null;
    const netImpact = (x, y) => {
        bulge = { x, y: ((y - GH / 2) * backLen) / GH, t: 0 };
    };
    const updateNet = (dt) => {
        if (!bulge) return;
        bulge.t += dt;
        const k = bulge.t / 0.9;
        const amp = k >= 1 ? 0 : 0.5 * Math.sin(Math.min(1, k * 2.2) * Math.PI) * (1 - k) + 0.06 * Math.sin(k * 18) * (1 - k);
        for (let i = 0; i < netPos.count; i++) {
            const dx = netBase[i * 3] - bulge.x, dy = netBase[i * 3 + 1] - bulge.y;
            netPos.array[i * 3 + 2] = netBase[i * 3 + 2] - amp * Math.exp(-(dx * dx + dy * dy) / 0.55);
        }
        netPos.needsUpdate = true;
        if (k >= 1) bulge = null;
    };

    // confetti in the club's colors
    const CONF = 180;
    const confGeo = new THREE.BufferGeometry();
    const confPos = new Float32Array(CONF * 3), confCol = new Float32Array(CONF * 3), confVel = new Float32Array(CONF * 3);
    confGeo.setAttribute('position', new THREE.BufferAttribute(confPos, 3));
    confGeo.setAttribute('color', new THREE.BufferAttribute(confCol, 3));
    const confMat = new THREE.PointsMaterial({ size: 0.085, vertexColors: true, transparent: true, depthWrite: false, toneMapped: false });
    const confetti = new THREE.Points(confGeo, confMat);
    confetti.frustumCulled = false;
    confetti.visible = false;
    scene.add(confetti);
    let confLife = 0;
    const palette = [brand.primary, brand.secondary, brand.accent, '#ffffff'].map((c) => new THREE.Color(c));
    const burst = (origin) => {
        for (let i = 0; i < CONF; i++) {
            confPos[i * 3] = origin.x + (Math.random() - 0.5) * 1.2;
            confPos[i * 3 + 1] = origin.y + Math.random() * 0.4;
            confPos[i * 3 + 2] = origin.z + (Math.random() - 0.5) * 0.6;
            confVel[i * 3] = (Math.random() - 0.5) * 6;
            confVel[i * 3 + 1] = 2.5 + Math.random() * 4.5;
            confVel[i * 3 + 2] = 0.5 + Math.random() * 3.5;
            const c = palette[(Math.random() * palette.length) | 0];
            confCol[i * 3] = c.r;
            confCol[i * 3 + 1] = c.g;
            confCol[i * 3 + 2] = c.b;
        }
        confGeo.attributes.color.needsUpdate = true;
        confLife = 2.4;
        confetti.visible = true;
    };
    const updateConfetti = (dt) => {
        if (confLife <= 0) return;
        confLife -= dt;
        const drag = Math.exp(-1.6 * dt);
        for (let i = 0; i < CONF; i++) {
            confVel[i * 3 + 1] -= 5.5 * dt;
            confVel[i * 3] *= drag;
            confVel[i * 3 + 1] *= drag;
            confVel[i * 3 + 2] *= drag;
            confPos[i * 3] += confVel[i * 3] * dt + Math.sin((confLife + i) * 9) * 0.004;
            confPos[i * 3 + 1] = Math.max(0.02, confPos[i * 3 + 1] + confVel[i * 3 + 1] * dt);
            confPos[i * 3 + 2] += confVel[i * 3 + 2] * dt;
        }
        confGeo.attributes.position.needsUpdate = true;
        confMat.opacity = Math.min(1, confLife / 0.6);
        if (confLife <= 0) confetti.visible = false;
    };

    // a fading trail behind the ball while it flies
    const trailTex = canvasTex(64, 64, (g, w, h) => {
        const gr = g.createRadialGradient(w / 2, h / 2, 0, w / 2, h / 2, w / 2);
        gr.addColorStop(0, 'rgba(255,255,255,1)');
        gr.addColorStop(1, 'rgba(255,255,255,0)');
        g.fillStyle = gr;
        g.fillRect(0, 0, w, h);
    });
    const TRAIL = 12;
    const trailSprites = Array.from({ length: TRAIL }, () => {
        const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: trailTex, color: brand.accent, transparent: true, depthWrite: false, toneMapped: false }));
        sp.visible = false;
        scene.add(sp);
        return sp;
    });
    const history = [];
    let trailOn = false;
    const setTrail = (on) => {
        trailOn = on;
        if (!on) history.length = 0;
    };
    const updateTrail = () => {
        if (trailOn) {
            history.unshift(ball.position.clone());
            if (history.length > TRAIL) history.length = TRAIL;
        } else if (history.length) {
            history.pop();
        }
        trailSprites.forEach((sp, i) => {
            const p = history[i];
            sp.visible = !!p && i > 0;
            if (!p) return;
            const k = 1 - i / TRAIL;
            sp.position.copy(p);
            sp.scale.setScalar(0.26 * k + 0.04);
            sp.material.opacity = 0.55 * k;
        });
    };

    // phones that cannot keep a smooth frame rate drop resolution and shadows instead of stuttering
    const perf = { frames: 0, time: 0, warm: 90, degraded: lowEnd };
    let sizedEl = null;
    const degrade = () => {
        perf.degraded = true;
        renderer.setPixelRatio(1);
        sun.castShadow = false;
        keeper.setShadows(false);
        striker.setShadows(false);
        if (sizedEl) resize(sizedEl);
    };
    const monitor = (rdt) => {
        if (perf.degraded) return;
        if (perf.warm > 0) {
            perf.warm -= 1;
            return;
        }
        perf.frames += 1;
        perf.time += rdt;
        if (perf.frames >= 120) {
            if (perf.time / perf.frames > 1 / 36) degrade();
            perf.frames = 0;
            perf.time = 0;
        }
    };

    const update = (dt, rdt = dt) => {
        updateNet(dt);
        updateConfetti(dt);
        updateTrail();
        monitor(rdt);
    };

    let W = 1, H = 1;
    const resize = (el) => {
        sizedEl = el;
        W = el.clientWidth;
        H = el.clientHeight;
        renderer.setSize(W, H, false);
        camera.aspect = W / H;
        const halfH = (20 * Math.PI) / 180;
        camera.fov = clamp((2 * Math.atan(Math.tan(halfH) / camera.aspect) * 180) / Math.PI, 42, 80);
        camera.updateProjectionMatrix();
    };
    const toScreen = (v) => {
        const p = v.clone().project(camera);
        return { x: ((p.x + 1) / 2) * W, y: ((1 - p.y) / 2) * H };
    };

    return {
        THREE, renderer, scene, camera, ball, netBack, keeper, striker,
        resize, toScreen, update, netImpact, setTrail,
        confetti: burst,
        size: () => ({ W, H }),
        render: () => renderer.render(scene, camera),
    };
}
