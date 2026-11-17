// shortcut: MindAR comes from jsDelivr, pinned. The npm package pulls in the native `canvas` build
// and its three.js wrapper breaks on three >= 0.162; vendor this file if a venue blocks CDNs.
const MINDAR_URL = 'https://cdn.jsdelivr.net/npm/mind-ar@1.2.5/dist/mindar-image.prod.js';

export const loadMindAR = () => import(/* @vite-ignore */ MINDAR_URL);
