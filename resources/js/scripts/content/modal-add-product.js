'use strict';

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content'),
    addProductModal = $('#product_add_modal'),
    addProductForm = $('#add-new-product');

addProductModal.on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var identifier = button.data('identifier'); // Extract info from data-* attributes
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this);
    modal.find('input[name="identifier"]').text(identifier);

});
addProductModal.on('hidden.bs.modal', function (event) {
    $('.has-error').removeClass("has-error");
    $('.help-block').text("");
});
addProductForm.on('submit', function (e) {
    e.preventDefault();
    var form = $(this);
    var data = new FormData(form[0]);
    var url = form.attr("action");
    $.ajax({
        type: form.attr('method'),
        url: url,
        data: data,
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {

            // $('#search-product-id').submit();
            addProductModal.modal('hide');

        },
        error: function (reject) {

            if (reject.status === 422) {
                var errors = reject.responseJSON.errors;
                $.each(errors, function (key, val) {
                    $("#f_" + key).addClass("has-error");
                    $("#" + key + "_error").text(val[0]);
                });
            }
        }
    });
    return false;
});