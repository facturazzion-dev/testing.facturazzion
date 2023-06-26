$(function () {
    'use strict';

    // Select Record
    $('#clave_unidad_sat_data tbody').on('click', 'tr', function () {
        var tr = $(this),
            index = $('#clave_unidad_sat_modal input:first').text() - 1;

        $('input[name="clave_unidad_sat"]').val(tr.find('td:last').text());
        $('input[name="unidad_sat"]').val(tr.find('td:first').text());
        $('#clave_unidad_sat_modal').modal('hide');
    });

    // Select Record
    $('#clave_sat_data tbody').on('click', 'tr', function () {
        var tr = $(this),
            index = $('#clave_sat_modal input:first').text() - 1;
        $('input[name="clave_sat"]').val(tr.find('td:last').text());
        $('textarea[name="description"]').val(tr.find('td:first').text());
        $('#clave_sat_modal').modal('hide');
    });

    $('#quantity_available').bind('keyup mouseup', function() {
        var qty_available = $('#quantity_available').val();
        $('#quantity_on_hand').val(qty_available);
    });
    
});