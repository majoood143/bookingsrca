// The penalty challenge page. The server decides every shot (keeper, outcome, time); this file
// signs the player up (WhatsApp code), turns their swipe into goal-relative numbers, and animates
// what the server answers. Config comes from the page (window.__GAME__).
import * as THREE from 'three';
import { call, deviceSignature } from './api.js';
import { createAudio } from './audio.js';
import { fmtTime as fmt, renderBoard } from './board.js';
import { BR, CAM, GH, GW, LOOK, SPOT, clamp, createStadium, easeOut, lerp, webglAvailable } from './stadium.js';

const C = window.__GAME__;
const T = C.i18n;
const $ = (s) => document.querySelector(s);
const t = (key, params = {}) => Object.entries(params).reduce((s, [k, v]) => s.replaceAll(':' + k, v), T[key] ?? key);
const buzz = (p) => {
    try {
        navigator.vibrate && navigator.vibrate(p);
    } catch (e) { /* no vibration */ }
};

const app = $('#gApp');
const screens = { start: $('#scrStart'), result: $('#scrResult'), board: $('#scrBoard') };
const show = (name) => Object.entries(screens).forEach(([k, el]) => (el.hidden = k !== name));

if (!webglAvailable()) {
    $('#gLoading').textContent = t('no_webgl');
    throw new Error('WebGL unavailable');
}

const stadium = createStadium({
    canvas: $('#gScene'),
    brand: C.brand,
    texts: { board: C.campaign.boardText, headline: C.campaign.headline, siteName: C.brand.site_name, prize: C.campaign.prizeText },
});
const { camera, ball, keeper, striker } = stadium;
const trail = $('#gTrail'), tctx = trail.getContext('2d');

const resize = () => {
    stadium.resize(app);
    const { W, H } = stadium.size();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    trail.width = W * dpr;
    trail.height = H * dpr;
    tctx.setTransform(dpr, 0, 0, dpr, 0, 0);
};
window.addEventListener('resize', resize);
resize();

/* ================= STATE ================= */
let state = 'menu'; // menu | intro | aim | runup | flight | over
let player = C.player;
let attempt = null; // { id, shots, results: [], startedAt }
let flight = null, intro = null, pts = null, pending = null;
let timeScale = 1, shake = 0, hintShown = true;
const SFX = createAudio(() => state === 'aim' && !pts);
const banner = $('#gBanner');

// Browsers (iPhones especially) only start or wake audio inside a user gesture: every touch counts.
['pointerdown', 'touchend', 'click', 'keydown'].forEach((type) => document.addEventListener(type, () => SFX.unlock(), { capture: true, passive: true }));
document.addEventListener('visibilitychange', () => SFX.visibility(document.hidden));

/** setTimeout that cannot leave the game frozen: a failing step sends the player back to the menu. */
function later(fn, ms) {
    return setTimeout(() => {
        try {
            fn();
        } catch (e) {
            console.error(e);
            endToMenu(t('generic'));
        }
    }, ms);
}

/* ================= START SCREEN ================= */
function renderStart() {
    const phase = C.campaign.phase;
    const live = phase === 'live';
    const eyebrow = $('#gPhase');
    eyebrow.classList.toggle('is-live', live);
    eyebrow.classList.toggle('is-off', !live);

    $('#gReg').hidden = !live || !!player || !$('#gOtp').hidden;
    $('#gWelcome').hidden = !live || !player;
    $('#gClosed').hidden = live;

    if (player) {
        $('#gWelcomeName').textContent = t('welcome', { name: player.firstName });
        $('#gWelcomeMobile').textContent = t('playing_as', { mobile: player.mobile });
        const r = player.remaining;
        $('#gWelcomeAttempts').textContent = !r.can_play
            ? (r.total === 0 ? t('no_attempts_total') : t('no_attempts_today'))
            : r.today !== null ? t('attempts_left_today', { n: r.today }) : r.total !== null ? t('attempts_left_total', { n: r.total }) : t('rules_unlimited');
        const st = player.standing;
        $('#gWelcomeBest').textContent = st ? t('your_best', { goals: st.goals, shots: C.campaign.shots, time: fmt(st.duration_ms) }) + ' · ' + t('your_rank', { rank: st.rank, of: st.of }) : '';
        $('#gWelcomeBest').hidden = !st;
        $('#gPlayBtn').disabled = !r.can_play || player.blocked;
    }
}

function tickCountdown() {
    const phase = C.campaign.phase;
    const target = new Date(phase === 'upcoming' ? C.campaign.startsAt : C.campaign.endsAt).getTime();
    let ms = target - Date.now();

    if (phase === 'upcoming' && ms <= 0) return location.reload();
    if (phase === 'live' && ms <= 0) {
        C.campaign.phase = 'ended';
        $('#gPhaseText').textContent = t('ended');
        $('#gCountdown').textContent = '';
        renderStart();
        return;
    }
    if (phase === 'ended' || phase === 'draft') return;

    ms = Math.max(0, ms);
    const d = Math.floor(ms / 864e5), h = Math.floor(ms / 36e5) % 24, m = Math.floor(ms / 6e4) % 60, s = Math.floor(ms / 1e3) % 60;
    $('#gCountdown').textContent = (d ? d + t('unit_d') + ' ' : '') + h + t('unit_h') + ' ' + String(m).padStart(2, '0') + t('unit_m') + (d ? '' : ' ' + String(s).padStart(2, '0') + t('unit_s'));
}
setInterval(tickCountdown, 1000);
tickCountdown();

/* ---- sign up with a one-time code ---- */
let resendTimer = null;
function startResendCountdown(seconds) {
    const btn = $('#gResend');
    clearInterval(resendTimer);
    let left = seconds;
    btn.disabled = true;
    btn.textContent = t('resend_in', { s: left });
    resendTimer = setInterval(() => {
        left -= 1;
        if (left <= 0) {
            clearInterval(resendTimer);
            btn.disabled = false;
            btn.textContent = t('resend');
        } else {
            btn.textContent = t('resend_in', { s: left });
        }
    }, 1000);
}

async function requestCode() {
    const err = $('#gRegErr'), btn = $('#gRegBtn');
    err.textContent = '';
    btn.disabled = true;
    try {
        const res = await call(C.urls.requestCode, {
            method: 'POST',
            i18n: T,
            body: {
                name: $('#gName').value.trim(),
                mobile: $('#gMobile').value.trim(),
                accept_terms: $('#gTerms').checked,
                marketing_opt_in: $('#gMarketing') ? $('#gMarketing').checked : false,
            },
        });
        $('#gOtpTo').textContent = t('code_label', { to: res.to });
        $('#gOtpVia').textContent = res.channel === 'sms' ? t('code_via_sms') : t('code_via_whatsapp');
        $('#gDemo').hidden = !res.demo_code;
        if (res.demo_code) $('#gDemo').innerHTML = t('demo_code', { code: '<code>' + res.demo_code + '</code>' });
        $('#gReg').hidden = true;
        $('#gOtp').hidden = false;
        $('#gCode').value = '';
        $('#gOtpErr').textContent = '';
        startResendCountdown(res.retry_after || 60);
        setTimeout(() => $('#gCode').focus(), 50);
    } catch (e) {
        if ($('#gOtp').hidden) err.textContent = e.message;
        else $('#gOtpErr').textContent = e.message;
        if (e.retryAfter && !$('#gOtp').hidden) startResendCountdown(e.retryAfter);
    } finally {
        btn.disabled = false;
    }
}

$('#gReg').addEventListener('submit', (e) => {
    e.preventDefault();
    SFX.init();
    requestCode();
});
$('#gResend').addEventListener('click', () => requestCode());
$('#gChange').addEventListener('click', () => {
    $('#gOtp').hidden = true;
    $('#gReg').hidden = false;
});
$('#gCode').addEventListener('input', (e) => {
    if (e.target.value.replace(/\D/g, '').length === 4) $('#gOtp').requestSubmit();
});
$('#gOtp').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = $('#gVerifyBtn');
    btn.disabled = true;
    $('#gOtpErr').textContent = '';
    try {
        const res = await call(C.urls.verifyCode, { method: 'POST', i18n: T, body: { code: $('#gCode').value } });
        player = res.player;
        $('#gOtp').hidden = true;
        renderStart();
        if (player.remaining.can_play) startAttempt();
    } catch (err) {
        $('#gOtpErr').textContent = err.message;
    } finally {
        btn.disabled = false;
    }
});
$('#gNotYou').addEventListener('click', async () => {
    try {
        await call(C.urls.logout, { method: 'POST', i18n: T, body: {} });
    } catch (e) { /* signed out locally anyway */ }
    player = null;
    $('#gOtp').hidden = true;
    renderStart();
});
$('#gPlayBtn').addEventListener('click', () => startAttempt());
$('#gAgain').addEventListener('click', () => startAttempt());

async function refreshPlayer() {
    try {
        const res = await call(C.urls.me, { i18n: T });
        player = res.player;
    } catch (e) { /* keep what we have */ }
    renderStart();
}

/* ================= ATTEMPT ================= */
async function startAttempt() {
    SFX.init();
    const btns = [$('#gPlayBtn'), $('#gAgain')];
    btns.forEach((b) => (b.disabled = true));
    try {
        const res = await call(C.urls.start, { method: 'POST', i18n: T, body: { device: deviceSignature() } });
        attempt = { id: res.attempt, shots: res.shots, results: [], startedAt: 0, endedAt: 0 };
        player.remaining = res.remaining;
    } catch (e) {
        show('start');
        $('#gWelcomeAttempts').textContent = e.message;
        if (e.status === 401) {
            player = null;
            renderStart();
        }
        return;
    } finally {
        btns.forEach((b) => (b.disabled = false));
    }

    SFX.start();
    striker.setName(player.firstName);
    show(null);
    $('#gHud').hidden = false;
    updateHud();
    $('#gHint').style.opacity = 0;
    keeper.reset();
    ball.position.copy(SPOT);
    state = 'intro';
    striker.beginIntro();
    intro = { t: 0 };
    $('#gTime').textContent = fmt(0);
    showBanner('goal', player.firstName, t('steps_up'));
}

const IC0 = new THREE.Vector3(0.45, 1.5, 13.55), IL0 = new THREE.Vector3(0, 1.25, 11.6);
function introUpdate(dt) {
    if (!intro) return;
    intro.t += dt;
    const tt = intro.t;
    if (tt < 1.7) {
        camera.position.copy(IC0);
        camera.position.x += Math.sin(tt * 0.8) * 0.12;
        camera.lookAt(IL0);
        return;
    }
    const q = easeOut(clamp((tt - 1.7) / 1.1, 0, 1));
    camera.position.lerpVectors(IC0, CAM, q);
    camera.lookAt(new THREE.Vector3().lerpVectors(IL0, LOOK, q));
    striker.introStep(q, dt);
    if (q >= 1) {
        intro = null;
        hintShown = true;
        $('#gHint').style.opacity = 1;
        attempt.startedAt = performance.now();
        resetShot();
    }
}

function resetShot() {
    striker.place();
    ball.position.copy(SPOT);
    ball.rotation.set(0, 0, 0);
    keeper.reset();
    camera.position.copy(CAM);
    camera.lookAt(LOOK);
    flight = null;
    pending = null;
    stadium.setTrail(false);
    state = 'aim';
    timeScale = 1;
    shake = 0;
    updateHud();
    setTimeout(() => state === 'aim' && SFX.whistle(), 350);
}

function updateHud() {
    const shots = attempt ? attempt.results : [];
    $('#gScore').textContent = shots.filter((s) => s.outcome === 'goal').length;
    $('#gShotsTotal').textContent = '/' + (attempt ? attempt.shots : C.campaign.shots);
    const d = $('#gDots');
    d.innerHTML = '';
    for (let i = 0; i < (attempt ? attempt.shots : C.campaign.shots); i++) {
        const e = document.createElement('span');
        e.className = shots[i] ? (shots[i].outcome === 'goal' ? 'goal' : 'miss') : i === shots.length ? 'now' : '';
        d.appendChild(e);
    }
}

function showBanner(kind, big, sub) {
    banner.className = 'g-banner';
    void banner.offsetWidth;
    banner.className = 'g-banner show ' + (kind === 'goal' ? 'goal' : 'bad');
    $('#gBnBig').textContent = big;
    $('#gBnSub').textContent = sub || '';
}

/* ---- swipe → goal-relative input → server ---- */
function pxy(e) {
    const r = app.getBoundingClientRect();
    return { x: e.clientX - r.left, y: e.clientY - r.top, t: performance.now() };
}
const cv = $('#gScene');
cv.addEventListener('pointerdown', (e) => {
    if (state !== 'aim') return;
    pts = [pxy(e)];
    SFX.hush(true);
    try {
        cv.setPointerCapture(e.pointerId);
    } catch (_) { /* older browsers */ }
});
cv.addEventListener('pointermove', (e) => {
    if (!pts) return;
    const p = pxy(e), l = pts[pts.length - 1];
    pts.push(p);
    tctx.strokeStyle = C.brand.accent;
    tctx.globalAlpha = 0.9;
    tctx.lineWidth = 6;
    tctx.lineCap = 'round';
    tctx.beginPath();
    tctx.moveTo(l.x, l.y);
    tctx.lineTo(p.x, p.y);
    tctx.stroke();
    tctx.globalAlpha = 1;
});
// Stop the browser from turning a swipe into scrolling or a pull-to-refresh.
cv.addEventListener('touchmove', (e) => e.preventDefault(), { passive: false });
cv.addEventListener('pointerup', (e) => {
    if (!pts) return;
    const a = pts;
    pts = null;
    a.push(pxy(e));
    release(a);
});
// iOS cancels a pointer when a swipe looks like a system gesture: still shoot if it was a real swipe.
cv.addEventListener('pointercancel', () => {
    if (!pts) return;
    const a = pts;
    pts = null;
    release(a);
});

function release(a) {
    if (state !== 'aim' || a.length < 2) return SFX.hush(false);

    const p0 = a[0], p1 = a[a.length - 1], dx = p1.x - p0.x, dy = p1.y - p0.y;
    if (dy > -35) {
        SFX.hush(false);
        return flashHint();
    }

    // speed over the last ~110 ms of the swipe, scaled to an 800px-tall screen
    let k = a.length - 1;
    while (k > 0 && p1.t - a[k - 1].t < 110) k--;
    const s0 = a[Math.max(0, k - 1)];
    const segT = Math.max(16, p1.t - s0.t);
    const { H } = stadium.size();
    const speed = Math.hypot(p1.x - s0.x, p1.y - s0.y) / segT / (H / 800);

    // lengths in "ball-to-crossbar" units, so the server's mapping fits any screen
    const b = stadium.toScreen(SPOT), gb = stadium.toScreen(new THREE.Vector3(0, GH, 0));
    const span = Math.max(40, b.y - gb.y);
    const step = Math.max(1, Math.ceil(a.length / 30));
    const points = a.filter((_, i) => i % step === 0 || i === a.length - 1)
        .map((q) => [+((q.x - p0.x) / span).toFixed(4), +((q.y - p0.y) / span).toFixed(4), Math.round(q.t - p0.t)]);

    shoot({
        n: attempt.results.length + 1,
        u: +(dx / span).toFixed(4),
        v: +(-dy / span).toFixed(4),
        speed: +speed.toFixed(4),
        ms: Math.round(p1.t - p0.t),
        points,
        webdriver: navigator.webdriver === true,
    });
}

function flashHint() {
    const h = $('#gHint');
    h.style.opacity = 1;
    clearTimeout(flashHint.t);
    flashHint.t = setTimeout(() => {
        if (!hintShown) h.style.opacity = 0;
    }, 1800);
}

function shoot(input) {
    state = 'runup';
    if (hintShown) {
        hintShown = false;
        $('#gHint').style.opacity = 0;
    }
    SFX.crowdTo(0.45, 0.4, 850);

    pending = { result: null, error: null, at: performance.now() };
    const req = pending;
    const url = C.urls.shot.replace('__ATTEMPT__', attempt.id);
    const send = (retry) => call(url, { method: 'POST', i18n: T, body: input, timeout: 9000 })
        .then((res) => (req.result = res))
        .catch((e) => {
            // a dropped or slow connection: ask once more (if the first one got through, the
            // server answers with the same, already-decided shot)
            if (retry && e.status === 0 && pending === req) return send(false);
            req.error = e;
        });
    send(true);

    striker.onContact = () => kick(req.result);
    striker.run(() => {
        if (req.error) {
            // once, and after the striker's own update, so its reset is not overwritten
            if (!req.handled) {
                req.handled = true;
                setTimeout(() => shotFailed(req.error), 0);
            }
            return false;
        }
        return !!req.result;
    });
}

function shotFailed(error) {
    pending = null;
    striker.place();
    SFX.hush(false);
    // the attempt cannot go on (closed, expired, or the result was lost): back to the menu
    if (error.status && error.status !== 429 && error.status !== 0) {
        endToMenu(error.message);
        return;
    }
    state = 'aim';
    showBanner('bad', '!', error.message);
}

async function endToMenu(message) {
    state = 'menu';
    attempt = null;
    flight = null;
    intro = null;
    pending = null;
    timeScale = 1;
    stadium.setTrail(false);
    striker.place();
    keeper.reset();
    ball.position.copy(SPOT);
    camera.position.copy(CAM);
    camera.lookAt(LOOK);
    $('#gHud').hidden = true;
    await refreshPlayer();
    show('start');
    if (message) $('#gWelcomeAttempts').textContent = message;
}

/* ---- the server's shot, animated ---- */
function kick(res) {
    stadium.setTrail(true);
    const g = 9.8, v = res.flight.speed, Tf = res.flight.time;
    const aim = res.aim;
    const vy = (aim.y - BR + 0.5 * g * Tf * Tf) / Tf, vx = aim.x / Tf;
    keeper.diveTo({ x: res.keeper.x, y: res.keeper.y, delay: res.keeper.delay, dur: res.keeper.dur });
    flight = { t: 0, T: Tf, v, vx, vy, aim, res, phase: 'air', pos: new THREE.Vector3(), vel: new THREE.Vector3() };
    state = 'flight';
    SFX.kick(res.power);
    SFX.crowdTo(0.6, 0.5, 1000);
    buzz(20);
}

function resolve(f) {
    const res = f.res, outcome = res.outcome;
    const vyT = f.vy - 9.8 * f.T;
    f.pos.copy(ball.position);
    const s = Math.sign(f.aim.x) || 1;
    if (outcome === 'goal') {
        f.vel.set(f.vx * 0.9, vyT, -f.v);
        stadium.netImpact(f.aim.x, Math.min(f.aim.y, GH - 0.2));
        stadium.confetti(new THREE.Vector3(clamp(f.aim.x, -3, 3), 2.2, -0.6));
    } else if (outcome === 'save') f.vel.set((Math.random() < 0.5 ? -1 : 1) * (2 + Math.random() * 3), Math.abs(vyT) * 0.3 + 2.5, f.v * 0.28);
    else if (outcome === 'post') {
        if (f.aim.y > GH - BR) f.vel.set(f.vx * 0.4, 2 + Math.random() * 2, f.v * 0.35);
        else f.vel.set(-s * (2 + Math.random() * 2), vyT * 0.5, f.v * 0.3);
    } else f.vel.set(f.vx, vyT, -f.v);
    f.phase = 'loose';
    timeScale = 1;

    attempt.results.push({ n: res.n, outcome });
    updateHud();
    keeper.react(outcome);
    striker.react(outcome);
    later(() => stadium.setTrail(false), 350);
    ({
        goal: () => { SFX.net(); SFX.roar(); shake = 0.35; buzz([40, 60, 80]); },
        save: () => { SFX.glove(); SFX.ooh(); buzz(60); },
        post: () => { SFX.post(); SFX.ooh(); shake = 0.12; buzz(90); },
        wide: () => SFX.groan(),
        over: () => SFX.groan(),
    })[outcome]();
    showBanner(outcome === 'goal' ? 'goal' : 'bad', t(outcome), t('power') + ' ' + Math.round(Math.min(res.power, 1) * 100) + '%' + (res.power > 1 ? ' +' : ''));

    if (res.done) attempt.endedAt = performance.now();
    later(() => (res.done ? finish(res.result) : resetShot()), 1700);
}

function flightUpdate(dt) {
    const f = flight;
    if (!f) return;
    if (f.phase === 'air') {
        if (f.res.outcome !== 'wide' && f.res.outcome !== 'over' && f.T - f.t < 0.16) timeScale = 0.32;
        f.t += dt;
        const tt = Math.min(f.t, f.T);
        ball.position.set(f.vx * tt, BR + f.vy * tt - 4.9 * tt * tt, SPOT.z - f.v * tt);
        ball.rotation.x -= (f.v * dt) / BR * 0.5;
        ball.rotation.z -= (f.vx * dt) / BR * 0.3;
        if (f.t >= f.T) resolve(f);
    } else {
        const p = f.pos, v = f.vel;
        v.y -= 9.8 * dt;
        p.addScaledVector(v, dt);
        if (p.y < BR) {
            p.y = BR;
            v.y = Math.abs(v.y) * 0.45;
            v.x *= 0.85;
            v.z *= 0.85;
            if (v.y < 0.4) v.y = 0;
        }
        if (f.res.outcome === 'goal') {
            if (p.z < -1.75) { p.z = -1.75; v.z = -v.z * 0.1; v.x *= 0.4; v.y *= 0.5; }
            if (Math.abs(p.x) > GW - BR) { p.x = Math.sign(p.x) * (GW - BR); v.x *= -0.3; }
            if (p.y > GH - BR) { p.y = GH - BR; v.y = -Math.abs(v.y) * 0.2; }
        } else if (p.z < -4.85 && p.z > -5.3 && p.y < 0.9 && Math.abs(p.x) < 22) {
            p.z = -4.85;
            v.z = Math.abs(v.z) * 0.3;
        }
        ball.position.copy(p);
        ball.rotation.x -= (v.z * dt) / BR * 0.3;
    }
    // a little to the right of the striker, so the keeper's dive is never hidden behind him
    camera.position.x = lerp(camera.position.x, 0.55 + ball.position.x * 0.18, 0.08);
    camera.position.z = lerp(camera.position.z, f.phase === 'air' ? CAM.z - 1.2 : CAM.z - 0.8, 0.04);
    camera.lookAt(LOOK.x + ball.position.x * 0.1, LOOK.y, LOOK.z);
    if (shake > 0) {
        camera.position.x += (Math.random() - 0.5) * shake * 0.3;
        camera.position.y = CAM.y + (Math.random() - 0.5) * shake * 0.2;
        shake = Math.max(0, shake - 0.02);
    } else camera.position.y = CAM.y;
}

/* ================= RESULT ================= */
function finish(result) {
    state = 'over';
    $('#gHud').hidden = true;
    SFX.final();
    const shots = attempt.shots;

    $('#gResTitle').textContent = player.firstName + ' · ' + t('full_time');
    $('#gResGoals').innerHTML = result.goals + '<small>/' + shots + '</small>';
    $('#gResTime').textContent = fmt(result.duration_ms);
    const rs = $('#gResShots');
    rs.innerHTML = '';
    result.shots.forEach((s) => {
        const c = document.createElement('div');
        c.className = 'g-shot ' + (s.outcome === 'goal' ? 'goal' : 'miss');
        const b = document.createElement('b');
        b.textContent = s.n;
        c.append(b, document.createTextNode(t(s.outcome)));
        rs.appendChild(c);
    });

    const rl = $('#gResRank');
    rl.textContent = '';
    const st = result.standing;
    if (st) {
        // "You are ranked <b>#3</b> of 120." with the rank highlighted, in either language
        const [before, after = ''] = t(result.is_best ? 'rank_line' : 'rank_best', { of: st.of }).split(':rank');
        const b = document.createElement('b');
        b.textContent = '#' + st.rank;
        rl.append(before, b, after + ' ');
        rl.append(st.rank === 1 ? t('leader') : st.leader_goals > st.goals ? t('to_lead_goals', { n: st.leader_goals - st.goals }) : t('to_lead_time', { time: fmt(st.leader_duration_ms) }));
    }
    rl.hidden = !st;
    $('#gResReview').hidden = !result.under_review;

    const share = (C.campaign.shareText || t('share_default'))
        .replaceAll(':goals', result.goals).replaceAll(':shots', shots).replaceAll(':time', fmt(result.duration_ms)).replaceAll(':campaign', C.campaign.name);
    $('#gShare').href = 'https://wa.me/?text=' + encodeURIComponent(share + ' ' + C.urls.play);

    player.remaining = result.remaining;
    player.standing = st;
    $('#gAgain').hidden = !result.remaining.can_play;
    $('#gResAttempts').textContent = result.remaining.can_play
        ? (result.remaining.today !== null ? t('attempts_left_today', { n: result.remaining.today }) : result.remaining.total !== null ? t('attempts_left_total', { n: result.remaining.total }) : '')
        : (result.remaining.total === 0 ? t('no_attempts_total') : t('no_attempts_today'));
    attempt = null;
    renderStart();
    show('result');
}

/* ================= LEADERBOARD ================= */
let boardBack = 'start';
document.querySelectorAll('[data-go="board"]').forEach((b) => b.addEventListener('click', () => {
    boardBack = screens.result.hidden ? 'start' : 'result';
    show('board');
    loadBoard();
}));
$('#gBoardBack').addEventListener('click', () => show(boardBack));

async function loadBoard() {
    const list = $('#gBoard');
    try {
        const res = await call(C.urls.leaderboard, { i18n: T });
        renderBoard(list, res, { shots: C.campaign.shots, you: t('you'), empty: t('empty') });
        $('#gBoardMeta').textContent = (res.frozen ? t('frozen') + ' · ' : '') + (res.players === 1 ? t('players_count_one') : t('players_count', { n: res.players }));
    } catch (e) {
        list.innerHTML = '';
        const p = document.createElement('p');
        p.className = 'g-empty';
        p.textContent = e.message;
        list.appendChild(p);
    }
}

/* ================= SOUND ================= */
$('#gSound').addEventListener('click', () => {
    const m = !SFX.muted;
    SFX.setMuted(m);
    const b = $('#gSound');
    b.textContent = m ? '🔇' : '🔊';
    b.setAttribute('aria-label', m ? t('sound_on') : t('sound_off'));
});

/* ================= LOOP ================= */
const clock = new THREE.Clock();
let time = 0, frameErrors = 0;
function loop() {
    // scheduled first: an error in one frame must not stop the game
    requestAnimationFrame(loop);
    try {
        frame();
        frameErrors = 0;
    } catch (e) {
        console.error(e);
        if (++frameErrors === 3 && state !== 'menu') endToMenu(t('generic'));
    }
}

function frame() {
    const rdt = Math.min(clock.getDelta(), 0.05), dt = rdt * timeScale;
    time += dt;
    keeper.update(dt, time);
    striker.update(dt, time);
    flightUpdate(dt);
    introUpdate(rdt);
    stadium.update(dt, rdt);

    // the server has not answered for 20 s (two 9 s tries end sooner, so this is a last resort)
    if (state === 'runup' && pending && !pending.result && !pending.error && performance.now() - pending.at > 20000) {
        pending.error = { status: 0, message: t('network') };
    }

    if (state === 'menu' || state === 'over') {
        camera.position.x = Math.sin(time * 0.15) * 1.2;
        camera.lookAt(LOOK);
    }
    if (attempt && attempt.startedAt && (state === 'aim' || state === 'flight' || state === 'runup')) {
        $('#gTime').textContent = fmt((attempt.endedAt || performance.now()) - attempt.startedAt);
    }
    tctx.save();
    tctx.setTransform(1, 0, 0, 1, 0, 0);
    tctx.globalCompositeOperation = 'destination-out';
    tctx.fillStyle = 'rgba(0,0,0,.14)';
    tctx.fillRect(0, 0, trail.width, trail.height);
    tctx.restore();
    stadium.render();
}

$('#gLoading').hidden = true;
renderStart();
show('start');
loop();
