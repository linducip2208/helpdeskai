import './bootstrap';
import './push';
import './echo';

import '@tabler/core/dist/js/tabler.min.js';

import ApexCharts from 'apexcharts';

window.ApexCharts = ApexCharts;

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

window.toggleTheme = function () {
    try {
        const current = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', current);
        localStorage.setItem('helpdeskai-theme', current);
    } catch (e) {}
};
