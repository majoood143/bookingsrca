// The goalkeeper and the striker: jointed athletes built from smooth primitives (hips, knees,
// ankles, shoulders, elbows, neck), dressed in kit textures painted with the club's colors, crest,
// the player's name and number, and animated procedurally from named poses (no model files to
// download). Each model faces +z; its left side is +x.
import * as THREE from 'three';

const clamp = (v, a, b) => Math.max(a, Math.min(b, v));
const lerp = (a, b, t) => a + (b - a) * t;
const easeOut = (t) => 1 - Math.pow(1 - t, 3);
const easeInOut = (t) => (t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2);
const FONT = 'Almarai, "Segoe UI", sans-serif';
const isArabic = (s) => /[؀-ۿ]/.test(s || '');

const JOINTS = ['hips', 'spine', 'neck', 'head', 'shoulderL', 'elbowL', 'shoulderR', 'elbowR', 'hipL', 'kneeL', 'ankleL', 'hipR', 'kneeR', 'ankleR'];
const HIP_HEIGHT = 0.96;

function shade(hex, amount) {
    const c = new THREE.Color(hex);
    c.offsetHSL(0, 0, amount);
    return '#' + c.getHexString();
}

/* ================= BODY ================= */

/**
 * @param {object} o
 * @param {string} o.kit shirt color   @param {string} o.trim collar / side panel color
 * @param {string} o.shorts            @param {string} o.socks
 * @param {string} o.skin              @param {string} o.hair
 * @param {boolean} o.longSleeves      @param {string|null} o.gloves glove color (keeper)
 * @param {string} o.number            @param {boolean} o.castShadow
 */
function buildBody(o) {
    const std = (color, extra = {}) => new THREE.MeshStandardMaterial({ color, roughness: 0.82, metalness: 0, ...extra });
    const skin = std(o.skin, { roughness: 0.6 });
    const shortsMat = std(o.shorts);
    const sockMat = std(o.socks);
    const sleeveMat = std(o.kit);
    const bootMat = std('#121212', { roughness: 0.45 });
    const hairMat = std(o.hair, { roughness: 0.9 });
    const gloveMat = o.gloves ? std(o.gloves, { roughness: 0.5 }) : null;

    // the shirt: one canvas wrapped around the torso (u = 0 is the chest, u = 0.5 the back)
    const shirtCanvas = document.createElement('canvas');
    shirtCanvas.width = 1024;
    shirtCanvas.height = 512;
    const shirtTex = new THREE.CanvasTexture(shirtCanvas);
    shirtTex.colorSpace = THREE.SRGBColorSpace;
    shirtTex.anisotropy = 4;
    const shirtMat = std('#ffffff', { map: shirtTex });

    const meshes = [];
    const mesh = (geo, mat, parent, pos = [0, 0, 0], rot = null, scale = null) => {
        const m = new THREE.Mesh(geo, mat);
        m.position.set(pos[0], pos[1], pos[2]);
        if (rot) m.rotation.set(rot[0], rot[1], rot[2]);
        if (scale) m.scale.set(scale[0], scale[1], scale[2]);
        m.castShadow = o.castShadow;
        parent.add(m);
        meshes.push(m);
        return m;
    };
    const capsule = (r, len, segs = 10) => new THREE.CapsuleGeometry(r, len, 4, segs);
    const group = (parent, pos) => {
        const g = new THREE.Group();
        g.position.set(pos[0], pos[1], pos[2]);
        parent.add(g);
        return g;
    };

    const root = new THREE.Group();
    const j = {};
    j.hips = group(root, [0, HIP_HEIGHT, 0]);

    // shorts
    mesh(new THREE.CylinderGeometry(0.165, 0.178, 0.24, 16), shortsMat, j.hips, [0, -0.03, 0], null, [1, 1, 0.74]);

    // legs
    for (const [side, x] of [['L', 0.095], ['R', -0.095]]) {
        const hip = (j['hip' + side] = group(j.hips, [x, -0.08, 0]));
        mesh(new THREE.CylinderGeometry(0.09, 0.084, 0.2, 12), shortsMat, hip, [0, -0.07, 0]);
        mesh(capsule(0.072, 0.3), skin, hip, [0, -0.22, 0]);
        const knee = (j['knee' + side] = group(hip, [0, -0.44, 0]));
        mesh(new THREE.SphereGeometry(0.066, 12, 8), skin, knee, [0, 0, 0.005]);
        mesh(capsule(0.058, 0.28), sockMat, knee, [0, -0.21, -0.008]);
        mesh(new THREE.CylinderGeometry(0.062, 0.062, 0.05, 12), std(shade(o.socks, -0.15)), knee, [0, -0.08, -0.008]);
        const ankle = (j['ankle' + side] = group(knee, [0, -0.43, 0]));
        mesh(capsule(0.046, 0.17, 8), bootMat, ankle, [0, -0.015, 0.055], [Math.PI / 2, 0, 0], [1, 0.75, 1]);
    }

    // torso
    j.spine = group(j.hips, [0, 0.07, 0]);
    mesh(new THREE.CylinderGeometry(0.205, 0.168, 0.5, 24, 1), shirtMat, j.spine, [0, 0.24, 0], null, [1, 1, 0.64]);
    mesh(new THREE.SphereGeometry(0.205, 20, 10, 0, Math.PI * 2, 0, Math.PI / 2), sleeveMat, j.spine, [0, 0.49, 0], null, [1, 0.32, 0.64]);
    mesh(new THREE.TorusGeometry(0.058, 0.016, 8, 20), std(o.trim), j.spine, [0, 0.535, 0.004], [Math.PI / 2, 0, 0], [1, 0.8, 1]);

    // arms
    for (const [side, x] of [['L', 0.215], ['R', -0.215]]) {
        const sh = (j['shoulder' + side] = group(j.spine, [x, 0.46, 0]));
        mesh(new THREE.SphereGeometry(0.075, 14, 10), sleeveMat, sh, [0, 0, 0]);
        if (o.longSleeves) {
            mesh(capsule(0.056, 0.22), sleeveMat, sh, [0, -0.15, 0]);
        } else {
            mesh(new THREE.CylinderGeometry(0.066, 0.06, 0.15, 12), sleeveMat, sh, [0, -0.07, 0]);
            mesh(capsule(0.047, 0.2), skin, sh, [0, -0.16, 0]);
        }
        const el = (j['elbow' + side] = group(sh, [0, -0.3, 0]));
        mesh(capsule(o.longSleeves ? 0.048 : 0.041, 0.2), o.longSleeves ? sleeveMat : skin, el, [0, -0.13, 0]);
        if (gloveMat) {
            mesh(new THREE.SphereGeometry(0.075, 14, 10), gloveMat, el, [0, -0.3, 0.01], null, [1, 1.25, 0.62]);
            mesh(new THREE.CylinderGeometry(0.055, 0.06, 0.06, 12), gloveMat, el, [0, -0.23, 0]);
        } else {
            mesh(new THREE.SphereGeometry(0.048, 12, 8), skin, el, [0, -0.29, 0.005], null, [0.8, 1.15, 0.55]);
        }
    }

    // neck and head
    j.neck = group(j.spine, [0, 0.52, 0]);
    mesh(new THREE.CylinderGeometry(0.05, 0.056, 0.1, 12), skin, j.neck, [0, 0.04, 0]);
    j.head = group(j.neck, [0, 0.09, 0]);
    mesh(new THREE.SphereGeometry(0.112, 22, 16), skin, j.head, [0, 0.1, 0.005], null, [0.9, 1.08, 0.98]);
    mesh(new THREE.SphereGeometry(0.118, 22, 12, 0, Math.PI * 2, 0, Math.PI / 2.05), hairMat, j.head, [0, 0.115, -0.006], [-0.18, 0, 0], [0.92, 1.0, 1.0]);
    const eyeMat = new THREE.MeshStandardMaterial({ color: '#1a1410', roughness: 0.3 });
    [-1, 1].forEach((s) => {
        mesh(new THREE.SphereGeometry(0.014, 8, 6), eyeMat, j.head, [s * 0.037, 0.11, 0.1]);
        mesh(new THREE.SphereGeometry(0.026, 8, 6), skin, j.head, [s * 0.103, 0.1, 0], null, [0.5, 1, 0.8]);
    });
    mesh(new THREE.ConeGeometry(0.017, 0.045, 8), skin, j.head, [0, 0.085, 0.112], [Math.PI / 2, 0, 0]);

    // soft contact shadow, for phones that render without shadow maps
    const blob = new THREE.Mesh(
        new THREE.CircleGeometry(0.42, 24),
        new THREE.MeshBasicMaterial({ color: 0x000000, transparent: true, opacity: 0.28, depthWrite: false }),
    );
    blob.rotation.x = -Math.PI / 2;
    blob.position.y = 0.014;
    root.add(blob);

    let logo = null, name = '';
    const drawShirt = () => {
        const g = shirtCanvas.getContext('2d'), w = shirtCanvas.width, h = shirtCanvas.height;
        g.fillStyle = o.kit;
        g.fillRect(0, 0, w, h);
        // subtle vertical pinstripes and side panels in the trim color
        g.fillStyle = shade(o.kit, -0.05);
        for (let x = 0; x < w; x += 48) g.fillRect(x, 0, 22, h);
        g.fillStyle = o.trim;
        g.fillRect(w * 0.235, 0, w * 0.03, h);
        g.fillRect(w * 0.735, 0, w * 0.03, h);
        g.fillRect(0, 0, w, 14);

        // chest crest (the wearer's left = u slightly above 0) and a matching one near the end of u
        if (logo) {
            const lh = 96, lw = (lh * logo.naturalWidth) / logo.naturalHeight;
            g.drawImage(logo, w * 0.075 - lw / 2, 90, lw, lh);
        }

        // back: name and number
        const cx = w * 0.5;
        g.fillStyle = '#ffffff';
        g.textAlign = 'center';
        g.textBaseline = 'middle';
        const n = (name || '').trim().split(/\s+/)[0].toUpperCase().slice(0, 12);
        if (n) {
            let fs = 64;
            g.font = `800 ${fs}px ${FONT}`;
            while (g.measureText(n).width > w * 0.36 && fs > 24) {
                fs -= 2;
                g.font = `800 ${fs}px ${FONT}`;
            }
            g.direction = isArabic(n) ? 'rtl' : 'ltr';
            g.fillText(n, cx, 92);
            g.direction = 'ltr';
        }
        g.font = `800 250px ${FONT}`;
        g.lineWidth = 12;
        g.strokeStyle = o.trim;
        g.strokeText(o.number, cx, 300);
        g.fillText(o.number, cx, 300);
        shirtTex.needsUpdate = true;
    };
    drawShirt();

    return {
        root,
        j,
        blob,
        setLogo(img) {
            logo = img;
            drawShirt();
        },
        setName(v) {
            name = v || '';
            drawShirt();
        },
        redraw: drawShirt,
        setShadows(on) {
            meshes.forEach((m) => (m.castShadow = on));
        },
    };
}

/* ================= POSES ================= */

/** Joint rotations (radians) plus `y`, the hips' height offset. Missing joints are neutral. */
const POSES = {
    stand: {
        spine: [0.04, 0, 0], shoulderL: [0.04, 0, 0.13], shoulderR: [0.04, 0, -0.13],
        elbowL: [-0.2, 0, 0], elbowR: [-0.2, 0, 0], hipL: [0, 0, 0.04], hipR: [0, 0, -0.04],
        kneeL: [0.06, 0, 0], kneeR: [0.06, 0, 0], ankleL: [-0.05, 0, 0], ankleR: [-0.05, 0, 0],
    },
    // striker, back foot loaded just before the strike (right foot kicks)
    windup: {
        y: -0.06, spine: [0.12, 0.28, 0.1], neck: [0.1, -0.1, 0],
        shoulderL: [-0.9, 0, 1.0], elbowL: [-0.5, 0, 0], shoulderR: [0.45, 0, -0.65], elbowR: [-0.4, 0, 0],
        hipL: [-0.35, 0, 0.06], kneeL: [0.42, 0, 0], ankleL: [-0.1, 0, 0],
        hipR: [0.85, 0, -0.05], kneeR: [1.65, 0, 0], ankleR: [0.55, 0, 0],
    },
    strike: {
        y: -0.04, spine: [-0.04, -0.2, 0.04], neck: [0.2, 0.05, 0],
        shoulderL: [-0.45, 0, 1.2], elbowL: [-0.4, 0, 0], shoulderR: [0.6, 0, -0.8], elbowR: [-0.3, 0, 0],
        hipL: [-0.12, 0, 0.05], kneeL: [0.3, 0, 0], ankleL: [0.05, 0, 0],
        hipR: [-0.95, 0, -0.02], kneeR: [0.12, 0, 0], ankleR: [-0.55, 0, 0],
    },
    follow: {
        y: 0.02, spine: [-0.22, -0.3, 0], neck: [0.12, 0.1, 0],
        shoulderL: [-0.25, 0, 1.3], elbowL: [-0.3, 0, 0], shoulderR: [0.3, 0, -1.15], elbowR: [-0.3, 0, 0],
        hipL: [0.05, 0, 0.04], kneeL: [0.22, 0, 0], ankleL: [0.25, 0, 0],
        hipR: [-1.55, 0, 0], kneeR: [0.35, 0, 0], ankleR: [-0.4, 0, 0],
    },
    celebrate: {
        y: 0.02, spine: [-0.12, 0, 0], neck: [-0.35, 0, 0],
        shoulderL: [-0.25, 0, 2.75], elbowL: [-0.5, 0, 0], shoulderR: [-0.25, 0, -2.75], elbowR: [-0.5, 0, 0],
        hipL: [-0.1, 0, 0.08], kneeL: [0.2, 0, 0], hipR: [0.05, 0, -0.08], kneeR: [0.25, 0, 0],
    },
    dejected: {
        y: -0.02, spine: [0.32, 0, 0], neck: [0.25, 0, 0], head: [0.15, 0, 0],
        shoulderL: [-0.9, 0.3, 2.1], elbowL: [-2.3, 0, 0], shoulderR: [-0.9, -0.3, -2.1], elbowR: [-2.3, 0, 0],
        hipL: [-0.05, 0, 0.05], kneeL: [0.12, 0, 0], hipR: [0.05, 0, -0.05], kneeR: [0.12, 0, 0],
    },
    // goalkeeper
    ready: {
        y: -0.15, spine: [0.42, 0, 0], neck: [-0.2, 0, 0], head: [-0.15, 0, 0],
        shoulderL: [-0.55, 0, 0.78], elbowL: [-0.95, 0, 0], shoulderR: [-0.55, 0, -0.78], elbowR: [-0.95, 0, 0],
        hipL: [-0.58, 0, 0.2], kneeL: [1.08, 0, 0], ankleL: [-0.45, 0, 0],
        hipR: [-0.58, 0, -0.2], kneeR: [1.08, 0, 0], ankleR: [-0.45, 0, 0],
    },
    diveL: { // to the model's left (+x): arms stretched over the head, legs trailing
        spine: [0.05, 0, -0.18], neck: [0, 0, -0.15],
        shoulderL: [-0.15, 0, 2.95], elbowL: [-0.08, 0, 0], shoulderR: [-0.15, 0, -2.75], elbowR: [-0.15, 0, 0],
        hipL: [-0.25, 0, -0.05], kneeL: [0.35, 0, 0], ankleL: [-0.3, 0, 0],
        hipR: [-0.05, 0, -0.25], kneeR: [0.75, 0, 0], ankleR: [-0.3, 0, 0],
    },
    jump: {
        y: 0.0, spine: [-0.05, 0, 0], neck: [-0.25, 0, 0],
        shoulderL: [-0.2, 0, 2.95], elbowL: [-0.1, 0, 0], shoulderR: [-0.2, 0, -2.95], elbowR: [-0.1, 0, 0],
        hipL: [-0.5, 0, 0.1], kneeL: [1.0, 0, 0], ankleL: [-0.2, 0, 0], hipR: [-0.3, 0, -0.1], kneeR: [0.7, 0, 0],
    },
    block: {
        y: -0.42, spine: [0.55, 0, 0], neck: [-0.4, 0, 0],
        shoulderL: [-0.95, 0, 0.32], elbowL: [-0.15, 0, 0], shoulderR: [-0.95, 0, -0.32], elbowR: [-0.15, 0, 0],
        hipL: [-1.25, 0, 0.35], kneeL: [1.9, 0, 0], ankleL: [-0.6, 0, 0],
        hipR: [-1.25, 0, -0.35], kneeR: [1.9, 0, 0], ankleR: [-0.6, 0, 0],
    },
    fistPump: {
        y: 0.0, spine: [-0.1, 0, 0], neck: [-0.3, 0, 0],
        shoulderL: [-0.4, 0, 2.6], elbowL: [-1.6, 0, 0], shoulderR: [-0.4, 0, -2.6], elbowR: [-1.6, 0, 0],
        hipL: [0, 0, 0.12], hipR: [0, 0, -0.12], kneeL: [0.15, 0, 0], kneeR: [0.15, 0, 0],
    },
};

/** Mirror a pose left↔right (diving the other way). */
function mirror(p) {
    const out = { y: p.y };
    for (const k of Object.keys(p)) {
        if (k === 'y') continue;
        const v = p[k];
        const mk = k.endsWith('L') ? k.slice(0, -1) + 'R' : k.endsWith('R') ? k.slice(0, -1) + 'L' : k;
        out[mk] = [v[0], -v[1], -v[2]];
    }
    return out;
}

function mix(a, b, k) {
    const out = { y: lerp(a.y || 0, b.y || 0, k) };
    for (const name of JOINTS) {
        const va = a[name] || [0, 0, 0], vb = b[name] || [0, 0, 0];
        out[name] = [lerp(va[0], vb[0], k), lerp(va[1], vb[1], k), lerp(va[2], vb[2], k)];
    }
    return out;
}

/** Ease every joint toward the pose (frame-rate independent); `rate` higher = snappier. */
function approach(body, pose, rate, dt) {
    const k = 1 - Math.exp(-rate * dt);
    for (const name of JOINTS) {
        const v = pose[name] || [0, 0, 0], r = body.j[name].rotation;
        r.x += (v[0] - r.x) * k;
        r.y += (v[1] - r.y) * k;
        r.z += (v[2] - r.z) * k;
    }
    const p = body.j.hips.position;
    p.y += (HIP_HEIGHT + (pose.y || 0) - p.y) * k;
}

function setPose(body, pose) {
    for (const name of JOINTS) {
        const v = pose[name] || [0, 0, 0];
        body.j[name].rotation.set(v[0], v[1], v[2]);
    }
    body.j.hips.position.y = HIP_HEIGHT + (pose.y || 0);
}

/** A running / walking stride at phase `ph`; `amt` 0 = walk, 1 = sprint. */
function stride(ph, amt) {
    const swing = lerp(0.42, 0.8, amt), kneeLift = lerp(0.55, 1.25, amt), arm = lerp(0.32, 0.7, amt);
    const sL = Math.sin(ph), sR = Math.sin(ph + Math.PI);
    return {
        y: -0.02 + lerp(0.015, 0.045, amt) * Math.abs(Math.sin(ph * 2)),
        spine: [lerp(0.05, 0.2, amt), 0.08 * Math.sin(ph), 0],
        neck: [-lerp(0.02, 0.12, amt), 0, 0],
        hipL: [-swing * sL, 0, 0.03], kneeL: [0.18 + kneeLift * Math.max(0, Math.cos(ph)), 0, 0], ankleL: [-0.15 + 0.3 * sL, 0, 0],
        hipR: [-swing * sR, 0, -0.03], kneeR: [0.18 + kneeLift * Math.max(0, Math.cos(ph + Math.PI)), 0, 0], ankleR: [-0.15 + 0.3 * sR, 0, 0],
        shoulderL: [arm * sR, 0, 0.14], elbowL: [-lerp(0.35, 1.25, amt), 0, 0],
        shoulderR: [arm * sL, 0, -0.14], elbowR: [-lerp(0.35, 1.25, amt), 0, 0],
    };
}

/* ================= GOALKEEPER ================= */

export function createKeeper(scene, { kit, trim, castShadow }) {
    const body = buildBody({
        kit, trim, shorts: '#15191c', socks: kit, skin: '#9a6a46', hair: '#1a1410',
        longSleeves: true, gloves: '#f3f6ef', number: '1', castShadow,
    });
    scene.add(body.root);
    body.root.rotation.y = 0; // faces the striker / camera (+z)
    setPose(body, POSES.ready);

    const K = { dive: null, startX: 0, root: body.root, body, reaction: null, t: 0 };

    K.reset = () => {
        K.dive = null;
        K.reaction = null;
        body.root.position.set(0, 0, 0.4);
        body.root.rotation.set(0, 0, 0);
        body.j.hips.rotation.set(0, 0, 0);
        body.blob.visible = true;
        setPose(body, POSES.ready);
    };

    /** Dive toward the server's spot (goal-plane x, y) after its reaction delay. */
    K.diveTo = (d) => {
        K.startX = body.root.position.x;
        K.dive = { ...d, t: 0 };
    };

    /** After the shot: 'save' celebrates, 'goal' slumps. */
    K.react = (outcome) => {
        K.reaction = { outcome, t: 0 };
    };

    K.update = (dt, time) => {
        if (!K.dive) {
            // set position, weight shifting from foot to foot, small bounce on the toes
            body.root.position.x = 0.32 * Math.sin(time * 1.5);
            const bounce = Math.abs(Math.sin(time * 3));
            const pose = mix(POSES.ready, { ...POSES.ready, y: POSES.ready.y + 0.035 }, bounce);
            pose.spine = [POSES.ready.spine[0], 0, 0.06 * Math.cos(time * 1.5)];
            approach(body, pose, 10, dt);
            return;
        }

        const d = K.dive;
        d.t += dt;
        const q = easeOut(clamp((d.t - d.delay) / d.dur, 0, 1));
        const side = Math.sign(d.x) || 1, high = d.y > 1.2;

        if (Math.abs(d.x) > 1.0) {
            // a full-stretch dive: the body rolls to the side, hands lead to the spot
            const roll = (high ? 1.12 : 1.42) * q;
            body.root.position.x = lerp(K.startX, d.x - side * 1.0, q);
            const pose = mix(POSES.ready, side > 0 ? POSES.diveL : mirror(POSES.diveL), Math.min(1, q * 1.4));
            pose.hips = [0, side * 0.25 * q, -side * roll];
            const targetY = high ? 0.45 : -0.55;
            pose.y = lerp(POSES.ready.y, targetY, q) + (high ? Math.sin(q * Math.PI) * 0.15 : 0);
            approach(body, pose, 28, dt);
            body.blob.scale.set(1 + q * 0.6, 1, 1);
        } else if (high) {
            body.root.position.x = lerp(K.startX, d.x, q);
            const pose = mix(POSES.ready, POSES.jump, q);
            pose.y = lerp(POSES.ready.y, 0.42, Math.sin(Math.min(1, q) * Math.PI * 0.5));
            approach(body, pose, 26, dt);
        } else {
            body.root.position.x = lerp(K.startX, d.x, q);
            approach(body, mix(POSES.ready, POSES.block, q), 26, dt);
        }

        // reactions once the dive is done
        const r = K.reaction;
        if (r && d.t > d.delay + d.dur) {
            r.t += dt;
            if (r.outcome === 'save' && Math.abs(d.x) <= 1.0) {
                const pump = Math.abs(Math.sin(r.t * 7)) * 0.1;
                approach(body, { ...POSES.fistPump, y: pump }, 12, dt);
            } else if (r.outcome === 'goal') {
                // head drops, fists hit the turf
                body.j.neck.rotation.x = lerp(body.j.neck.rotation.x, 0.55, 1 - Math.exp(-4 * dt));
            }
        }
    };

    K.setLogo = body.setLogo;
    K.setShadows = body.setShadows;
    K.reset();
    return K;
}

/* ================= STRIKER ================= */

export function createStriker(scene, { kit, trim, castShadow }) {
    const body = buildBody({
        kit, trim, shorts: '#f2f5ee', socks: kit, skin: '#8d5e3c', hair: '#14100c',
        longSleeves: false, gloves: null, number: '10', castShadow,
    });
    scene.add(body.root);

    // SIDE: where he steps after the kick, out of the camera's line to the keeper
    const REST = new THREE.Vector3(-1.7, 0, 13.4), KICK = new THREE.Vector3(-0.32, 0, 11.38);
    const CARRY = new THREE.Vector3(-0.72, 0, 11.72), SIDE = new THREE.Vector3(-1.35, 0, 12.25), OFF = new THREE.Vector3(-3.4, 0, 12.6);
    const S = { mode: 'rest', t: 0, onContact: null, ready: null, reaction: null, root: body.root, body, REST, KICK };

    S.place = (v = REST) => {
        body.root.position.copy(v);
        body.root.rotation.set(0, Math.PI - 0.35, 0);
        S.mode = 'rest';
        S.reaction = null;
        S.ready = null;
        setPose(body, POSES.stand);
    };

    /** Walk from the ball out to the run-up mark during the intro; q goes 0 → 1. */
    S.beginIntro = () => {
        body.root.position.set(0, 0, 11.75);
        body.root.rotation.set(0, Math.PI, 0);
        S.mode = 'intro';
        setPose(body, POSES.stand);
    };
    const INTRO_FROM = new THREE.Vector3(0, 0, 11.75);
    S.introStep = (q, dt) => {
        body.root.position.lerpVectors(INTRO_FROM, REST, q);
        body.root.rotation.y = Math.PI - 0.35 * q;
        const pose = q < 1 ? stride(q * Math.PI * 6, 0) : POSES.stand;
        approach(body, pose, 14, dt);
    };

    /** Run up; holds the swing at the ball until `ready()` is true (the server has decided). */
    S.run = (ready) => {
        S.mode = 'run';
        S.t = 0;
        S.ready = ready;
    };

    /** After the shot: celebrate a goal, hands on head for a miss. */
    S.react = (outcome) => {
        S.reaction = { outcome };
    };

    S.setName = body.setName;
    S.setLogo = body.setLogo;
    S.redraw = body.redraw;
    S.setShadows = body.setShadows;

    S.update = (dt, time) => {
        if (S.mode === 'intro') return;

        if (S.mode === 'rest') {
            // breathing, small weight shifts while waiting for the swipe
            const br = Math.sin(time * 2.2);
            const pose = { ...POSES.stand, y: 0.008 * br, spine: [0.05 + 0.02 * br, 0, 0.02 * Math.sin(time * 0.7)] };
            approach(body, pose, 8, dt);
            return;
        }

        if (S.mode === 'hold') {
            approach(body, POSES.windup, 16, dt);
            if (S.ready && S.ready()) {
                S.mode = 'swing';
                S.t = 0;
            }
            return;
        }

        S.t += dt;

        if (S.mode === 'run') {
            const D = 0.42, q = clamp(S.t / D, 0, 1);
            body.root.position.lerpVectors(REST, KICK, easeInOut(q));
            body.root.rotation.y = Math.PI - 0.35 * (1 - q) + 0.12 * q;
            // three quick strides, blending into the plant over the last third
            const pose = q < 0.7 ? stride(q * Math.PI * 4.2, 1) : mix(stride(0.7 * Math.PI * 4.2, 1), POSES.windup, (q - 0.7) / 0.3);
            approach(body, pose, 30, dt);
            if (q >= 1) {
                S.mode = S.ready && S.ready() ? 'swing' : 'hold';
                S.t = 0;
            }
        } else if (S.mode === 'swing') {
            const q = clamp(S.t / 0.09, 0, 1);
            setPose(body, mix(POSES.windup, POSES.strike, easeInOut(q)));
            if (q >= 1) {
                S.mode = 'follow';
                S.t = 0;
                const cb = S.onContact;
                S.onContact = null;
                cb && cb();
            }
        } else if (S.mode === 'follow') {
            const q = clamp(S.t / 0.35, 0, 1);
            setPose(body, mix(POSES.strike, POSES.follow, easeOut(q)));
            // momentum carries him off the line between the camera and the keeper
            body.root.position.lerpVectors(KICK, CARRY, easeOut(q));
            if (q >= 1) {
                S.mode = 'after';
                S.t = 0;
            }
        } else if (S.mode === 'after') {
            // a few steps aside first, so the keeper's dive stays in view
            if (S.t < 0.55) {
                const k = easeInOut(S.t / 0.55);
                body.root.position.lerpVectors(CARRY, SIDE, k);
                body.root.rotation.y = Math.PI + 0.12 - 0.3 * k;
                approach(body, stride(S.t * 16, 0.25), 12, dt);
                return;
            }
            const r = S.reaction, rt = S.t - 0.55;
            if (!r) {
                approach(body, POSES.stand, 4, dt);
            } else if (r.outcome === 'goal') {
                // jump with both fists up, then wheel away arms out like a plane
                if (rt < 0.9) {
                    const hop = Math.abs(Math.sin(rt * 7)) * 0.16;
                    approach(body, { ...POSES.celebrate, y: hop }, 14, dt);
                } else {
                    const k = clamp((rt - 0.9) / 1.2, 0, 1);
                    body.root.position.lerpVectors(SIDE, OFF, easeInOut(k));
                    body.root.rotation.y = lerp(body.root.rotation.y, -1.25, 1 - Math.exp(-6 * dt));
                    const pose = stride(rt * 13, 0.85);
                    pose.shoulderL = [-0.1, 0, 1.5];
                    pose.shoulderR = [-0.1, 0, -1.5];
                    pose.elbowL = pose.elbowR = [0, 0, 0];
                    pose.spine = [0.1, 0, 0.25 * Math.sin(rt * 2)];
                    approach(body, pose, 14, dt);
                }
            } else {
                approach(body, POSES.dejected, 5, dt);
            }
        }
    };

    S.place();
    return S;
}
