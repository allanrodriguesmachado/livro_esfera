import $ from 'jquery';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import '@fortawesome/fontawesome-free/css/all.min.css';

import 'datatables.net-bs5';
import 'datatables.net-bs5/css/dataTables.bootstrap5.min.css';

import {fetch} from "../js/app/livros";
import {registro} from "../js/app/registro";

import lottie from 'lottie-web';

lottie.loadAnimation({
    container: document.getElementById('lottie-loader'),
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: '/animations/animation_book.json',
    rendererSettings: {
        preserveAspectRatio: 'xMidYMid slice',
        progressiveLoad: true,
        viewBoxOnly: true,
    },
});

window.$ = $;
window.jQuery = $;

$('#toggleMode').on('click', () => {
    $('body').toggleClass('dark-mode');

    const icon = $('#toggleMode i');
    icon.toggleClass('fa-moon fa-sun');
});
$(document).ready(() => {
    fetch('/application/fetch');
    registro('/application/create')
});