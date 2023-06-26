'use strict';

var dtClaveSatTable = $('#clave_sat_data'),
    searchTableClaveSat = $('#search-form');

if (dtClaveSatTable.length) {
    var dtClaveSat = dtClaveSatTable.DataTable({
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
            {
                searchable: true,
                orderable: false,
                data: "descripcion"
            },
            {
                searchable: true,
                orderable: false,
                data: "id"
            },
        ],
        ajax: {
            url: "/product/clave_sat_data",
            data: function (d) {
                d.searchable_name = $('input[name=searchable_name]').val();
            }
        }
    });

}
// Search Record
searchTableClaveSat.on('submit', function (e) {
    dtClaveSat.draw();
    e.preventDefault();
});