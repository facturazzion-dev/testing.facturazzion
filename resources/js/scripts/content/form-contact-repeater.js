$(function () {
    'use strict';

    var contactRepeater = $('#contacts');

    /* Initializes repeater */
    contactRepeater.repeater({
        show: function () {
            $(this).slideDown();
            // Feather Icons
            if (feather) {
                feather.replace({ width: 14, height: 14 });
            }
        },

        hide: function (deleteElement) {
            if (confirm('¿Estás seguro que deseas quitar este contacto?')) {
                $(this).slideUp(deleteElement, function () {
                    $(this).remove();
                });
            }
        }
    });
});