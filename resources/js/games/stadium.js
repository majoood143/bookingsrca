// The floodlit stadium shared by the game and the public leaderboard: pitch, goal and net, crowd
// stands, a tifo and LED boards painted with the client's branding (logo, colors, texts), the
// goalkeeper and the striker. Everything brand-specific comes from `brand` and `texts`.
import * as THREE from 'three';

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

    const scene = new THREE.Scene();
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
    const netBack = new THREE.Mesh(new THREE.PlaneGeometry(2 * GW, backLen), netMat(netTex([(2 * GW) / CELL, backLen / CELL])));
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
    const stand = new THREE.Mesh(new THREE.PlaneGeometry(130, 24), new THREE.MeshBasicMaterial({ map: crowd, color: 0x8e9890 }));
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
    const tifo = new THREE.Mesh(new THREE.PlaneGeometry(22, 9), new THREE.MeshBasicMaterial({ map: tifoTex, color: 0xc9d3cb }));
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
    const ads = new THREE.Mesh(new THREE.PlaneGeometry(44, 0.9), new THREE.MeshBasicMaterial({ map: adTex }));
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

    const box = (w, h, d, c) => {
        const m = new THREE.Mesh(new THREE.BoxGeometry(w, h, d), new THREE.MeshLambertMaterial({ color: c }));
        m.castShadow = true;
        return m;
    };

    const logoTex = new THREE.Texture();
    logoTex.colorSpace = THREE.SRGBColorSpace;

    // goalkeeper
    const keeper = (() => {
        const KIT = new THREE.Color(brand.keeper_kit), SHORTS = 0x14181a, SKIN = 0x9a6a46, GLOVE = 0xf3f6ef;
        const root = new THREE.Group(), piv = new THREE.Group();
        root.add(piv);
        piv.position.y = 1.0;
        const torso = box(0.52, 0.62, 0.26, KIT);
        torso.position.y = 0.28;
        piv.add(torso);
        const crest = new THREE.Mesh(new THREE.PlaneGeometry(0.15, 0.15), new THREE.MeshBasicMaterial({ map: logoTex, transparent: true }));
        crest.position.set(-0.12, 0.4, 0.131);
        crest.visible = false;
        piv.add(crest);
        const shorts = box(0.5, 0.28, 0.27, SHORTS);
        shorts.position.y = -0.1;
        piv.add(shorts);
        [-1, 1].forEach((s) => {
            const leg = box(0.17, 0.62, 0.18, KIT);
            leg.position.set(s * 0.13, -0.55, 0);
            piv.add(leg);
            const thigh = box(0.18, 0.2, 0.19, SKIN);
            thigh.position.set(s * 0.13, -0.32, 0);
            piv.add(thigh);
            const boot = box(0.16, 0.1, 0.28, 0x111111);
            boot.position.set(s * 0.13, -0.94, 0.05);
            piv.add(boot);
        });
        const neck = box(0.12, 0.1, 0.12, SKIN);
        neck.position.y = 0.63;
        piv.add(neck);
        const head = new THREE.Mesh(new THREE.SphereGeometry(0.13, 20, 16), new THREE.MeshLambertMaterial({ color: SKIN }));
        head.position.y = 0.77;
        head.castShadow = true;
        piv.add(head);
        const hair = new THREE.Mesh(new THREE.SphereGeometry(0.135, 20, 16, 0, Math.PI * 2, 0, Math.PI / 2.2), new THREE.MeshLambertMaterial({ color: 0x1a1410 }));
        hair.position.y = 0.79;
        piv.add(hair);
        const arm = (s) => {
            const g = new THREE.Group();
            g.position.set(s * 0.32, 0.52, 0);
            const a = box(0.13, 0.56, 0.13, KIT);
            a.position.y = -0.28;
            g.add(a);
            const gl = new THREE.Mesh(new THREE.SphereGeometry(0.095, 14, 10), new THREE.MeshLambertMaterial({ color: GLOVE }));
            gl.position.y = -0.6;
            gl.scale.set(1, 1.2, 0.6);
            gl.castShadow = true;
            g.add(gl);
            piv.add(g);
            return g;
        };
        const armL = arm(-1), armR = arm(1);
        root.position.set(0, 0, 0.4);
        scene.add(root);

        const K = { dive: null, startX: 0, root, piv, armL, armR, crest };
        K.reset = () => {
            K.dive = null;
            root.position.set(0, 0, 0.4);
            piv.position.y = 1.0;
            piv.rotation.set(0, 0, 0);
            armL.rotation.z = -0.35;
            armR.rotation.z = 0.35;
        };
        K.diveTo = (d) => {
            K.startX = root.position.x;
            K.dive = { ...d, t: 0 };
        };
        K.update = (dt, time) => {
            if (!K.dive) {
                root.position.x = 0.32 * Math.sin(time * 1.5);
                piv.position.y = 1.0 - 0.04 * Math.abs(Math.sin(time * 3));
                armL.rotation.z = -0.45 - 0.12 * Math.sin(time * 3);
                armR.rotation.z = 0.45 + 0.12 * Math.sin(time * 3);
                return;
            }
            const d = K.dive;
            d.t += dt;
            const q = easeOut(clamp((d.t - d.delay) / d.dur, 0, 1));
            const side = Math.sign(d.x) || 1, high = d.y > 1.2;
            if (Math.abs(d.x) > 1.0) {
                root.position.x = lerp(K.startX, d.x - side * 1.1, q);
                piv.rotation.z = -side * (high ? 1.15 : 1.42) * q;
                piv.position.y = lerp(1.0, high ? 1.5 : 0.52, q);
                armL.rotation.z = lerp(-0.45, -(Math.PI - 0.15), q);
                armR.rotation.z = lerp(0.45, Math.PI - 0.15, q);
            } else {
                root.position.x = lerp(K.startX, d.x, q);
                piv.position.y = lerp(1.0, high ? 1.45 : 0.85, q);
                armL.rotation.z = lerp(-0.45, high ? -(Math.PI - 0.3) : -0.9, q);
                armR.rotation.z = lerp(0.45, high ? Math.PI - 0.3 : 0.9, q);
            }
        };
        K.reset();
        return K;
    })();

    // striker in the brand's kit, the player's name on the back
    const striker = (() => {
        const KIT = brand.kit, WHITE = 0xf2f5ee, SKIN = 0x8d5e3c;
        const root = new THREE.Group();
        scene.add(root);
        const body = new THREE.Group();
        root.add(body);
        const t = box(0.5, 0.62, 0.26, new THREE.Color(KIT));
        t.position.y = 1.28;
        body.add(t);
        const collar = box(0.3, 0.05, 0.27, WHITE);
        collar.position.y = 1.6;
        body.add(collar);
        const shorts = box(0.48, 0.28, 0.27, WHITE);
        shorts.position.y = 0.9;
        body.add(shorts);
        const head = new THREE.Mesh(new THREE.SphereGeometry(0.125, 20, 16), new THREE.MeshLambertMaterial({ color: SKIN }));
        head.position.y = 1.76;
        head.castShadow = true;
        body.add(head);
        const hair = new THREE.Mesh(new THREE.SphereGeometry(0.13, 20, 16, 0, Math.PI * 2, 0, Math.PI / 2), new THREE.MeshLambertMaterial({ color: 0x14100c }));
        hair.position.y = 1.78;
        body.add(hair);
        const leg = (x) => {
            const g = new THREE.Group();
            g.position.set(x, 0.8, 0);
            const th = box(0.17, 0.32, 0.18, SKIN);
            th.position.y = -0.16;
            g.add(th);
            const sock = box(0.16, 0.44, 0.17, new THREE.Color(KIT));
            sock.position.y = -0.52;
            g.add(sock);
            const bt = box(0.15, 0.09, 0.27, 0x0d0d0d);
            bt.position.set(0, -0.76, 0.05);
            g.add(bt);
            body.add(g);
            return g;
        };
        const legL = leg(0.13), legR = leg(-0.13); // model faces +z, so its right side is local -x
        const arm = (x) => {
            const g = new THREE.Group();
            g.position.set(x, 1.52, 0);
            const a = box(0.12, 0.3, 0.13, new THREE.Color(KIT));
            a.position.y = -0.15;
            g.add(a);
            const f = box(0.11, 0.28, 0.12, SKIN);
            f.position.y = -0.43;
            g.add(f);
            g.rotation.z = x > 0 ? 0.12 : -0.12;
            body.add(g);
            return g;
        };
        const armL = arm(0.31), armR = arm(-0.31);

        const shirtCanvas = document.createElement('canvas');
        shirtCanvas.width = 256;
        shirtCanvas.height = 320;
        const shirtTex = new THREE.CanvasTexture(shirtCanvas);
        shirtTex.colorSpace = THREE.SRGBColorSpace;
        let shirtName = '';
        const drawShirt = () => {
            const g = shirtCanvas.getContext('2d'), w = 256, h = 320;
            g.fillStyle = KIT;
            g.fillRect(0, 0, w, h);
            g.fillStyle = shade(KIT, -0.05);
            for (let x = 0; x < w; x += 32) g.fillRect(x, 0, 16, h);
            g.fillStyle = '#f2f5ee';
            g.fillRect(0, 0, 10, h);
            g.fillRect(w - 10, 0, 10, h);
            if (logoImg) {
                const lw = 40, lh = (lw * logoImg.naturalHeight) / logoImg.naturalWidth;
                g.drawImage(logoImg, w / 2 - lw / 2, 12, lw, Math.min(lh, 48));
            }
            const n = (shirtName || texts.siteName || '').trim().split(/\s+/)[0].toUpperCase().slice(0, 12);
            g.fillStyle = '#ffffff';
            g.textAlign = 'center';
            g.textBaseline = 'middle';
            fitText(g, n, w - 40, 56);
            g.fillText(n, w / 2, 92);
            g.direction = 'ltr';
            g.font = `800 160px ${FONT}`;
            g.lineWidth = 8;
            g.strokeStyle = brand.accent;
            g.strokeText('10', w / 2, 214);
            g.fillText('10', w / 2, 214);
            shirtTex.needsUpdate = true;
        };
        drawShirt();
        const back = new THREE.Mesh(new THREE.PlaneGeometry(0.48, 0.6), new THREE.MeshBasicMaterial({ map: shirtTex }));
        back.position.set(0, 1.28, -0.132);
        back.rotation.y = Math.PI;
        body.add(back);

        const REST = new THREE.Vector3(-1.7, 0, 13.4), KICK = new THREE.Vector3(-0.3, 0, 11.4), OFF = new THREE.Vector3(-2.6, 0, 12.2);
        const S = { mode: 'rest', t: 0, onContact: null, root, body, legL, legR, REST, KICK };
        S.place = (v = REST) => {
            root.position.copy(v);
            root.rotation.y = Math.PI - 0.35;
            body.position.y = 0;
            legL.rotation.x = legR.rotation.x = 0;
            armL.rotation.x = armR.rotation.x = 0;
            S.mode = 'rest';
        };
        // Runs up; holds the swing at the ball until `ready` resolves (the server's decision).
        S.run = (ready) => {
            S.mode = 'run';
            S.t = 0;
            S.ready = ready;
        };
        S.setName = (name) => {
            shirtName = name || '';
            drawShirt();
        };
        S.redraw = drawShirt;
        S.update = (dt, time) => {
            if (S.mode === 'rest') {
                body.position.y = 0.015 * Math.sin(time * 3);
                armL.rotation.x = 0.08 * Math.sin(time * 2);
                armR.rotation.x = -0.08 * Math.sin(time * 2);
                return;
            }
            if (S.mode === 'hold') {
                legR.rotation.x = 1.1;
                if (S.ready && S.ready()) {
                    S.mode = 'swing';
                    S.t = 0;
                }
                return;
            }
            S.t += dt;
            if (S.mode === 'run') {
                const q = clamp(S.t / 0.42, 0, 1);
                root.position.lerpVectors(REST, KICK, q * q * (3 - 2 * q));
                root.rotation.y = Math.PI - 0.35 * (1 - q) + 0.15 * q;
                const stride = Math.sin(q * Math.PI * 3);
                body.position.y = 0.05 * Math.abs(stride);
                legL.rotation.x = 0.6 * stride;
                armL.rotation.x = -0.6 * stride;
                armR.rotation.x = 0.6 * stride;
                legR.rotation.x = q < 0.7 ? -0.6 * stride + 0.9 * q : lerp(0.63, 1.1, (q - 0.7) / 0.3);
                if (q >= 1) {
                    S.mode = S.ready && S.ready() ? 'swing' : 'hold';
                    S.t = 0;
                }
            } else if (S.mode === 'swing') {
                const q = clamp(S.t / 0.08, 0, 1);
                legR.rotation.x = lerp(1.1, -1.35, q);
                if (q >= 1) {
                    S.mode = 'follow';
                    S.t = 0;
                    const cb = S.onContact;
                    S.onContact = null;
                    cb && cb();
                }
            } else if (S.mode === 'follow') {
                const q = clamp(S.t / 0.9, 0, 1);
                legR.rotation.x = lerp(-1.35, 0, Math.min(1, q * 2.5));
                legL.rotation.x = 0.4 * Math.sin(q * Math.PI * 4);
                if (q > 0.25) {
                    root.position.lerpVectors(KICK, OFF, (q - 0.25) / 0.75);
                    root.rotation.y = -1.2;
                }
            }
        };
        S.place();
        return S;
    })();

    keeper.root.visible = withPlayers;
    striker.root.visible = withPlayers;

    // logo arrives after first paint
    drawTifo();
    drawAds();
    loadImage(brand.logo).then((img) => {
        if (!img) return;
        logoImg = img;
        logoTex.image = img;
        logoTex.needsUpdate = true;
        keeper.crest.visible = true;
        drawTifo();
        drawAds();
        striker.redraw();
    });
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(() => {
            drawTifo();
            drawAds();
            striker.redraw();
        });
    }

    let W = 1, H = 1;
    const resize = (el) => {
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
        resize, toScreen,
        size: () => ({ W, H }),
        render: () => renderer.render(scene, camera),
    };
}
