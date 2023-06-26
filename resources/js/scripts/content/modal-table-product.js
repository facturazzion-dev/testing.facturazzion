'use strict';

var dtProductTable = $('#product_id_data'),
    searchTableProducts = $('#search-product-id');

if (dtProductTable.length) {
    var dtProduct = dtProductTable.DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        },
        dom: "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        processing: true,
        serverSide: true,
        searching: false,
        info: false,
        lengthChange: false,
        order: [],
        columns: [
            { searchable: false, orderable: false, data: "id", className: "d-none" },
            { searchable: true, orderable: false, data: "sku" },
            { searchable: true, orderable: false, data: "clave_sat" },
            { searchable: true, orderable: false, data: "product_name" },
            { searchable: true, orderable: false, data: "description" },
            { searchable: false, orderable: false, data: "clave_unidad_sat" },
            { searchable: false, orderable: false, data: "unidad_sat" },
            { searchable: false, orderable: false, data: "sale_price" },
            { searchable: false, orderable: false, data: "quantity_available", className: "d-none" },
        ],
        ajax: {
            url: "/product/products_data",
            data: function (d) {
                d.searchable_product_name = $('input[name=searchable_product_name]').val();
            }
        }
    });
}
// Search Record
searchTableProducts.on('submit', function (e) {
    dtProduct.draw();
    e.preventDefault();
});
// Select Record
$('#product_id_data tbody').on('click', 'tr', function () {
    var tr = $(this),
        index = $('#product_id_modal input:first').text() - 1;

    $('input[name="products[' + index + '][product_id]"]').val(tr.find('td:nth-child(1)').text());
    $('input[name="products[' + index + '][sku]"]').val(tr.find('td:nth-child(2)').text());
    $('input[name="products[' + index + '][clave_sat]"]').val(tr.find('td:nth-child(3)').text());
    $('textarea[name="products[' + index + '][description]"]').val(tr.find('td:nth-child(5)').text());
    $('input[name="products[' + index + '][clave_unidad_sat]"]').val(tr.find('td:nth-child(6)').text());
    $('input[name="products[' + index + '][unidad_sat]"]').val(tr.find('td:nth-child(7)').text());
    $('input[name="products[' + index + '][quantity]"]').val(1);
    $('input[name="products[' + index + '][price]"]').val(tr.find('td:nth-child(8)').text());
    $('input[name="products[' + index + '][quantity_available]"]').val(tr.find('td:nth-child(9)').text());
    updateTotal(index);
    $('#product_id_modal').modal('hide');
});
$('#product_id_modal').on('show.bs.modal', function (event) {
    // Button that triggered the modal
    var button = $(event.relatedTarget);
    // Extract info from product-count
    var identifier = button.closest('.repeater-wrapper').find('.product-count').text();
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this);
    modal.find('input[name="identifier"]').text(identifier);
});

function updateTotal(index) {
    var priceInput = $('input[name="products[' + index + '][price]"]'),
        quantityInput = $('input[name="products[' + index + '][quantity]"]'),
        totalAmount = $('input[name="products[' + index + '][total_amount]"]'),
        price = parseFloat(priceInput.val()),
        quantity = parseFloat(quantityInput.val()),
        finalValue = quantity * price;

        totalAmount.val(finalValue.toFixed(6));    
}