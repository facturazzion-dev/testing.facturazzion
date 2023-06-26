'use strict';

var dtClaveUnidadSatTable = $('#clave_unidad_sat_data'),
    searchTableClaveUnidadSat = $('#search-unidad-sat');

if (dtClaveUnidadSatTable.length) {
    var dtClaveUnidaSat = dtClaveUnidadSatTable.DataTable({
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
            { searchable: true, orderable: false, data: "nombre" },
            { searchable: true, orderable: false, data: "id" },
        ],
        ajax: {
            url: "/product/clave_unidad_sat_data",
            data: function (d) {
                d.searchable_unidad_sat = $('input[name=searchable_unidad_sat]').val();
            }
        }
    });
}
// Search Record
searchTableClaveUnidadSat.on('submit', function (e) {
    dtClaveUnidaSat.draw();
    e.preventDefault();
});
