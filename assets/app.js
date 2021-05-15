
import './styles/app.scss';
import 'bootstrap';

// start the Stimulus application
import $ from 'jquery';
import './bootstrap';
import 'select2';


$(document).ready(function() {
    $('.js-select2').select2({
        placeholder: '-- sélectionnez --',
        allowClear: true
    });

    const [startDelay, delayStep] = [window.delayStart, window.delayStep];
    let nbFlashes = window.nbFlashes;
    let delay = startDelay;

    while (nbFlashes > 0) {
        const toast = $(`#js-toast-${delay}`);
        const toastButton = $(`#js-close-toast-${delay}`);

        toastButton.click((event) => {
            // $(`#${event.currentTarget.dataset.bsDismiss}`).addClass('d-none');
            $(`#${event.currentTarget.dataset.bsDismiss}`).slideUp();
        });

        setTimeout(() => {
            // toast.addClass('d-none');
            toast.slideUp();
        }, delay);

        delay = delay + delayStep;
        nbFlashes--;
    }

});
