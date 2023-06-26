<div class="modal fade" id="product_id_modal" tabindex="-1" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <input type="hidden" name="identifier">
            <div class="modal-header">
                <h4 class="modal-title">Buscar Producto o Servicio</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true , 'id' => 'search-product-id']) !!}
                <div class="input-group mb-3">
                    <input type="text" name="searchable_product_name" class="form-control" placeholder="Buscar por nombre de producto" aria-label="Buscar por nombre de producto" aria-describedby="basic-addon2">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit">Buscar</button>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="table-responsive">
                    <table id="product_id_data" class="table table-striped table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>{{ trans('product.sku') }}</th>
                                <th>{{ trans('product.sat_id') }}</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Clave Unidad</th>
                                <th>Unidad Medida</th>
                                <th>Precio</th>
                                <th>Inventario</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@section('page-script')
    @parent
    <script>
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
    $('.price').keyup();
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


    </script>
@stop