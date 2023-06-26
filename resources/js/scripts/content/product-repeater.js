'use strict';

// Select Record
$('#clave_unidad_sat_data tbody').on('click', 'tr', function () {
    var tr = $(this),
        index = $('#clave_unidad_sat_modal input:first').text() - 1;

    $('input[name="products[' + index + '][clave_unidad_sat]"]').val(tr.find('td:last').text());
    $('input[name="products[' + index + '][unidad_sat]"]').val(tr.find('td:first').text());
    $('#clave_unidad_sat_modal').modal('hide');
});
$('#clave_unidad_sat_modal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var identifier = button.closest('.repeater-wrapper').find('.product-count').text(); // Extract info from product-count
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this);
    modal.find('input[name="identifier"]').text(identifier);

});

// Select Record
$('#clave_sat_data tbody').on('click', 'tr', function () {
    var tr = $(this),
        index = $('#clave_sat_modal input:first').text() - 1;
    $('input[name="products[' + index + '][clave_sat]"]').val(tr.find('td:last').text());
    $('textarea[name="products[' + index + '][description]"]').val(tr.find('td:first').text());
    $('#clave_sat_modal').modal('hide');
});
$('#clave_sat_modal').on('show.bs.modal', function (event) {
    // Button that triggered the modal         
    var button = $(event.relatedTarget);
    // Extract info from product-count
    var identifier = button.closest('.repeater-wrapper').find('.product-count').text();
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this);
    modal.find('input[name="identifier"]').text(identifier);
});