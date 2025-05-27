import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

import $ from 'jquery';

window.$ = $;
window.jQuery = $;

$('#toggleMode').on('click', () => {
    $('body').toggleClass('dark-mode');

    const icon = $('#toggleMode i');
    icon.toggleClass('fa-moon fa-sun');
});

