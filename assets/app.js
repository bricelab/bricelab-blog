
import './styles/app.scss';
import 'bootstrap';

// start the Stimulus application
import './bootstrap';
import $ from 'jquery';
import 'select2';


$(document).ready(function() {
    $('.js-select2').select2({
        placeholder: '-- sélectionnez --',
        allowClear: true
    });
});
