import Alpine from 'alpinejs';
import Collapse from '@alpinejs/collapse';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

Alpine.plugin(Collapse);
Alpine.start();
