// Leaderboard rows (masked mobiles), shared by the game's board screen and the public page.

export const fmtTime = (ms) => {
    const s = ms / 1000, m = Math.floor(s / 60), r = s - m * 60;
    return m + ':' + (r < 10 ? '0' : '') + r.toFixed(2);
};

function row(r, shots, youLabel, isMe) {
    const d = document.createElement('div');
    d.className = 'g-row' + (r.rank === 1 ? ' first' : '') + (isMe ? ' me' : '');
    d.innerHTML = '<span class="rk"></span><span class="pl"></span><span class="gl g-num"></span><span class="tm g-num"></span>';
    d.querySelector('.rk').textContent = r.rank;
    d.querySelector('.pl').textContent = r.player;
    d.querySelector('.pl').dataset.you = youLabel;
    d.querySelector('.gl').textContent = r.goals + '/' + shots;
    d.querySelector('.tm').textContent = fmtTime(r.duration_ms);
    return d;
}

/**
 * @param {HTMLElement} list
 * @param {{rows: Array, me?: object|null}} board
 * @param {{shots: number, you: string, empty: string}} labels
 */
export function renderBoard(list, board, labels) {
    list.innerHTML = '';
    const me = board.me || null;

    if (!board.rows.length) {
        const p = document.createElement('p');
        p.className = 'g-empty';
        p.textContent = labels.empty;
        list.appendChild(p);
        return;
    }

    board.rows.forEach((r) => list.appendChild(row(r, labels.shots, labels.you, !!me && me.rank === r.rank && me.player === r.player)));

    // the player's own row under the top list when they are further down
    if (me && !board.rows.some((r) => r.rank === me.rank)) {
        list.appendChild(row(me, labels.shots, labels.you, true));
    }
}
