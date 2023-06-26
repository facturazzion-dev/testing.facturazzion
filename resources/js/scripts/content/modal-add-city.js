'use strict';

var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content'),
    addCityModal = $('#addCityModal'),
    addCityFormSection = $("#addCityForm"),
    addCityButton = $('#addCityButton');

addCityModal.on('show.bs.modal', function (event) {
    var modal = $(this),
        stateId = $('#state_id').val(),
        cityName = $('#city_id').data('select2').dropdown.$search.val();

    modal.find('input[name="h_state_id"]').val(stateId);
    modal.find('input[name="h_name"]').val(cityName);

});
addCityModal.on('hidden.bs.modal', function (event) {
    $('.has-error').removeClass("has-error");
    $('.help-block').text("");
});
addCityButton.click(function (e) {
    addCityFormSection.block({
        message:
            '<div class="d-flex justify-content-center align-items-center"><p class="me-50 mb-0" text-primary>Guardando...</p> <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div> </div>',
        css: {
            backgroundColor: 'transparent',
            border: '0'
        },
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: 0.8
        }
    });
    $.ajax({
        type: "POST",
        url: '/company/ajax-store-city',
        data: {
            '_token': CSRF_TOKEN,
            'h_state_id': addCityModal.find('input[name="h_state_id"]').val(),
            'h_name': addCityModal.find('input[name="h_name"]').val(),
        },
        success: function (data) {
            addCityFormSection.block({
                message: '<div class="p-1 bg-primary">¡Listo!</div>',
                timeout: 2000,
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: '0'
                },
                overlayCSS: {
                    opacity: 0.25
                }
            });
            var city_id = data.id;
            var city_name = data.name;

            $('#city_id').append($('<option></option>').val(city_id).html(city_name));
            $('#city_id').val(city_id).change();                
        },
        error: function (e) {
            addCityFormSection.block({
                message: '<div class="p-1 bg-danger">' + e + '</div>',
                timeout: 2000,
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: '0'
                },
                overlayCSS: {
                    opacity: 0.25
                }
            });
        }
    });
    addCityModal.modal('hide');
});