import './bootstrap';
// import { Livewire } from '../../vendor/livewire/livewire/dist/livewire.esm';
import Alpine from 'alpinejs';
import html2canvas from 'html2canvas';

window.Alpine = Alpine;
window.html2canvas = html2canvas;

Alpine.start();
// Livewire.start();
