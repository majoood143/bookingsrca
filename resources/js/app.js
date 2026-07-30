import './bootstrap';

import { gsap } from 'gsap';

// Exposed globally so Livewire/Alpine component scripts (kiosk wizard, etc.)
// can animate without each needing its own bundler import.
window.gsap = gsap;
