<div class="row">
    <div class="col-lg-4">
        <div class="form-group  {{ $errors->has('name') ? 'has-error' : '' }}">
            {!! Form::label('name', trans('company.company_name'), ['class' => 'form-label']) !!}
            {!! Form::text('name', null, ['class' => 'form-control input-disable ', 'id' => 'company_name']) !!}
            <span class="help-block">{{ $errors->first('name', ':message') }}</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="form-group required {{ $errors->has('email') ? 'has-error' : '' }}">
            {!! Form::label('email', trans('company.email'), ['class' => 'form-label required']) !!}
            {!! Form::email('email', isset($orgSettings)?$orgSettings['site_email']:null, ['class' => 'form-control input-disable']) !!}
            <span class="help-block">{{ $errors->first('email', ':message') }}</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4">
        <div class="form-group  {{ $errors->has('phone') ? 'has-error' : '' }}">
            {!! Form::label('phone', trans('company.phone'), ['class' => 'form-label']) !!}
            {!! Form::text('phone', null, ['class' => 'form-control input-disable','data-fv-integer' => "true"]) !!}
            <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group required {{ $errors->has('sat_name') ? 'has-error' : '' }}">
            {!! Form::label('sat_name', trans('company.sat_name'), ['class' => 'form-label required']) !!}
            {!! Form::text('sat_name', null, ['class' => 'form-control input-disable']) !!}
            <span class="help-block">{{ $errors->first('sat_name', ':message') }}</span>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group required {{ $errors->has('sat_rfc') ? 'has-error' : '' }}">
            {!! Form::label('sat_rfc', trans('company.sat_rfc'), ['class' => 'form-label required']) !!}
            {!! Form::text('sat_rfc', null, ['class' => 'form-control input-disable text-uppercase']) !!}
            <span class="help-block">{{ $errors->first('sat_rfc', ':message') }}</span>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group required {{ $errors->has('fiscal_regimen') ? 'has-error' : '' }}">
            {!! Form::label('fiscal_regimen', 'Régimen Fiscal', ['class' => 'form-label required ']) !!}
            {!! Form::select('fiscal_regimen', array(
            '601'=>'General de Ley Personas Morales',
            '603'=>'Personas Morales con Fines no Lucrativos',
            '605'=>'Sueldos y Salarios e Ingresos Asimilados a Salarios',
            '606'=>'Arrendamiento',
            '607'=>'Régimen de Enajenación o Adquisición de Bienes',
            '608'=>'Demás ingresos',
            '610'=>'Residentes en el Extranjero sin Establecimiento Permanente en México',
            '611'=>'Ingresos por Dividendos (socios y accionistas)',
            '612'=>'Personas Físicas con Actividades Empresariales y Profesionales',
            '614'=>'Ingresos por intereses',
            '615'=>'Régimen de los ingresos por obtención de premios',
            '616'=>'Sin obligaciones fiscales',
            '620'=>'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
            '621'=>'Incorporación Fiscal',
            '622'=>'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
            '623'=>'Opcional para Grupos de Sociedades',
            '624'=>'Coordinados',
            '625'=>'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
            '626'=>'Régimen Simplificado de Confianza'
            ), isset($company->fiscal_regimen)?$company->fiscal_regimen:'601', ['class' => 'form-select input-disable select2 company-select2', 'placeholder' => 'Seleccionar Régimen Fiscal']) !!}
            <span class="help-block">{{ $errors->first('fiscal_regimen', ':message') }}</span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group  {{ $errors->has('street') ? 'has-error' : '' }}">
            {!! Form::label('street', trans('company.street'), ['class' => 'form-label ']) !!}
            {!! Form::text('street', null, ['class' => 'form-control input-disable']) !!}
            <span class="help-block">{{ $errors->first('street', ':message') }}</span>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="form-group  {{ $errors->has('exterior_no') ? 'has-error' : '' }}">
            {!! Form::label('exterior_no', trans('company.exterior_no'), ['class' => 'form-label ']) !!}
            {!! Form::text('exterior_no', null, ['class' => 'form-control input-disable','data-fv-integer' => "true"]) !!}
            <span class="help-block">{{ $errors->first('exterior_no', ':message') }}</span>
        </div>
    </div>
    <div class="col-6 col-lg-2">
        <div class="form-group  {{ $errors->has('interior_no') ? 'has-error' : '' }}">
            {!! Form::label('interior_no', trans('company.interior_no'), ['class' => 'form-label']) !!}
            {!! Form::text('interior_no', null, ['class' => 'form-control input-disable','data-fv-integer' => "true"]) !!}
            <span class="help-block">{{ $errors->first('interior_no', ':message') }}</span>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="form-group  {{ $errors->has('suburb') ? 'has-error' : '' }}">
            {!! Form::label('suburb', trans('company.suburb'), ['class' => 'form-label ']) !!}
            {!! Form::text('suburb', null, ['class' => 'form-control input-disable']) !!}
            <span class="help-block">{{ $errors->first('suburb', ':message') }}</span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-4 col-md-4 col-lg-3">
        <div class="form-group required {{ $errors->has('zip_code') ? 'has-error' : '' }}">
            {!! Form::label('zip_code', trans('company.zip_code'), ['class' => 'form-label required']) !!}
            {!! Form::text('zip_code', null, ['class' => 'form-control input-disable']) !!}
            <span class="help-block">{{ $errors->first('zip_code', ':message') }}</span>
        </div>
    </div>
    <div class="col-8 col-md-8 col-lg-3">
        <div class="form-group required {{ $errors->has('country_id') ? 'has-error' : '' }}">
            {!! Form::label('country_id', trans('company.country'), ['class' => 'form-label required']) !!}
            {!! Form::select('country_id', $countries, '142', ['id'=>'country_id', 'class' => 'form-select input-disable select2 company-select2', 'data-id' => $company->country_id ?? '']) !!}
            <span class="help-block">{{ $errors->first('country_id', ':message') }}</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="form-group {{ $errors->has('state_id') ? 'has-error' : '' }}">
            {!! Form::label('state_id', trans('company.state'), ['class' => 'form-label']) !!}
            {!! Form::checkbox('print_company_state', 'true', isset($orgSettings['print_company_state']) ? ($orgSettings['print_company_state'] == 'false') ? false : true : true, ['id' => 'print_company_state', 'class' => 'styled']) !!}
            {!! Form::select('state_id', isset($states)?$states:[0=>trans('company.select_state')], $company->state_id ?? '2428', ['id'=>'state_id', 'class' => 'form-select select2 company-select2 input-disable', 'data-id' => $company->state_id ?? '2428']) !!}
            <span class="help-block">{{ $errors->first('state_id', ':message') }}</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="form-group  {{ $errors->has('city_id') ? 'has-error' : '' }}">
            {!! Form::label('city_id', trans('company.city'), ['class' => 'form-label ']) !!}
            {!! Form::checkbox('print_company_city', 'true', isset($orgSettings['print_company_city']) ? ($orgSettings['print_company_city'] == 'false') ? false : true : true, ['id' => 'print_company_city', 'class' => 'print_city icheck']) !!}
            {!! Form::select('city_id', isset($cities)?$cities:[0=>trans('company.select_city')], $company->city_id ?? '47578', ['id'=>'city_id', 'class' => 'form-select select2 company-select2 input-disable', 'data-id' => $company->city_id ?? '47578']) !!}
            <span class="help-block">{{ $errors->first('city_id', ':message') }}</span>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-4">
        <div class="form-group required {{ $errors->has('cfdi_use') ? 'has-error' : '' }}">
            {!! Form::label('cfdi_use', trans('company.cfdi_use'), ['class' => 'form-label required']) !!}
            {!! Form::select('cfdi_use', $cfdi_use, old('cfdi_use', (isset($company['cfdi_use'])?$company['cfdi_use']:"G03")), ['class' => 'form-select select2 company-select2']) !!}
            <span class="help-block">{{ $errors->first('cfdi_use', ':message') }}</span>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="form-group required {{ $errors->has('payment_method') ? 'has-error' : '' }}">
            {!! Form::label('payment_method', trans('company.payment_method'), ['class' => 'form-label required']) !!}
            {!! Form::select('payment_method', $payment_method, old('payment_method', (isset($company['payment_method'])?$company['payment_method']:"PUE")), ['class' => 'form-select select2 company-select2']) !!}
            <span class="help-block">{{ $errors->first('payment_method', ':message') }}</span>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="form-group required {{ $errors->has('payment_type') ? 'has-error' : '' }}">
            {!! Form::label('payment_type', trans('company.payment_type'), ['class' => 'form-label required']) !!}
            {!! Form::select('payment_type', array(
            '01'=>'Efectivo - 01',
            '02'=>'Cheque nominativo - 02',
            '03'=>'Transferencia electrónica de fondos - 03',
            '04'=>'Tarjeta de crédito - 04',
            '05'=>'Monedero electrónico - 05',
            '06'=>'Dinero electrónico - 06',
            '08'=>'Vales de despensa - 08',
            '12'=>'Dación en pago - 12',
            '13'=>'Pago por subrogación - 13',
            '14'=>'Pago por consignación - 14',
            '15'=>'Condonación - 15',
            '17'=>'Compensación - 17',
            '23'=>'Novación - 23',
            '24'=>'Confusión - 24',
            '25'=>'Remisión de deuda - 25',
            '26'=>'Prescripción o caducidad - 26',
            '27'=>'A satisfacción del acreedor - 27',
            '28'=>'Tarjeta de débito - 28',
            '29'=>'Tarjeta de servicios - 29',
            '30'=>'Aplicación de anticipos - 30',
            '31'=>'Intermediario pagos - 31',
            '99'=>'Por definir - 99'), old('payment_type', (isset($company['payment_type'])?$company['payment_type']:"01")), ['class' => 'form-select select2 company-select2']) !!}
            <span class="help-block">{{ $errors->first('payment_type', ':message') }}</span>
        </div>
    </div>
</div>

@include('content._partials._modals.modal-add-city')

@section('page-script')
    @parent
    <script>
        $(function () {
            'use strict';

            var select = $('.company-select2'),
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
                        var _state = stateSelect.attr('data-id');
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
                        var _city = citySelect.attr('data-id');
                        $('#city_id').find('option').remove();
                        $.each(data, function (val, text) {
                            $('#city_id').append($('<option></option>').val(val).html(text).attr('selected', val == _city ? true : false));
                        });
                    }
                });
            }
        });
    </script>
@endsection