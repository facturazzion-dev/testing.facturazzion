$(function () {
    'use strict';

    var _state = 2428,
        _city = 47578,
        select = $('.company-select2'),
        citySelect = $("#city_id"),
        stateSelect = $("#state_id"),
        countrySelect = $("#country_id"),
        paymentMethodSelect = $('#payment_method');
        

    select.each(function () {
        var $this = $(this)
        $this.wrap('<div class="position-relative"></div>')
        $this.select2({
            // the following code is used to disable x-scrollbar when click in select input and
            // take 100% width in responsive also
            dropdownAutoWidth: true,
            width: '100%',
            dropdownParent: $this.parent()
        })
    });

    citySelect.select2({
        allowClear: true,
        escapeMarkup: function (markup) { return markup; },
        language: {
            noResults: function () {
                return '<a data-bs-toggle="modal" href="#addCityModal">Agregar</a>';
            }
        }
    });
    countrySelect.change(function () {
        getstates($(this).val());
    });
    stateSelect.change(function () {
        $('#h_state_id').val($(this).val());
        getcities($(this).val());
    });
    paymentMethodSelect.change(function () {
        var value = $(this).val();
        if (value == 'PPD') {
            $('#payment_type').val('99').change();
        }
    });
    function getstates(country) {
        $.ajax({
            type: "GET",
            url: '/lead/ajax_state_list',
            data: { 'id': country },
            success: function (data) {
                $('#state_id').empty();
                $('#city_id').find('option').remove();
                if (_state) {
                    getcities(_state);

                }
                $.each(data, function (val, text) {
                    $('#state_id').append($('<option></option>').val(val).html(text).attr('selected', val == _state ? true : false));
                });
            }
        });
    }
    function getcities(state) {
        $.ajax({
            type: "GET",
            url: '/lead/ajax_city_list',
            data: { 'id': state },
            success: function (data) {
                $('#city_id').find('option').remove();
                $.each(data, function (val, text) {
                    $('#city_id').append($('<option></option>').val(val).html(text).attr('selected', val == _city ? true : false));
                });
            }
        });
    }
});