$(function () {
    'use strict';

    var bankRepeater = $('#banks');

    /* Initializes repeater */
    bankRepeater.repeater({
        show: function () {
            $(this).slideDown();
            // Feather Icons
            if (feather) {
                feather.replace({ width: 14, height: 14 });
            }
        },

        hide: function (deleteElement) {
            if (confirm('¿Estás seguro que deseas quitar este banco?')) {
                $(this).slideUp(deleteElement, function () {
                    $(this).remove();
                });
            }
        }
    });
});