@php
    $periodicities = [
        '01' => 'Diario',
        '02' => 'Semanal',
        '03' => 'Quincenal',
        '04' => 'Mensual',
        '05' => 'Bimestral'
    ];
    $months = [
        '01' => 'Enero',
        '02' => 'Febrero',
        '03' => 'Marzo',
        '04' => 'Abril',
        '05' => 'Mayo',
        '06' => 'Junio',
        '07' => 'Julio',
        '08' => 'Agosto',
        '09' => 'Septiembre',
        '10' => 'Octubre',
        '11' => 'Noviembre',
        '12' => 'Diciembre',
        '13' => 'Enero-Febrero',
        '14' => 'Marzo-Abril',
        '15' => 'Mayo-Junio',
        '16' => 'Julio-Agosto',
        '17' => 'Septiembre-Octubre',
        '18' => 'Noviembre-Diciembre'
    ];
@endphp
<div class="row">
    <div class="col-lg-4">
        <div class="form-group required {{ $errors->has('periodicity') ? 'has-error' : '' }}">
            {!! Form::label('periodicity', 'Periodicidad', ['class' => 'form-label required']) !!}
            {!! Form::select('periodicity', $periodicities, null, ['class' => 'form-select select2 global-info-select2']) !!}
            <span class="help-block">{{ $errors->first('periodicity', ':message') }}</span>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="form-group required {{ $errors->has('month') ? 'has-error' : '' }}">
            {!! Form::label('month', 'Mes', ['class' => 'form-label required']) !!}
            {!! Form::select('month', $months, null, ['class' => 'form-select select2 global-info-select2']) !!}
            <span class="help-block">{{ $errors->first('month', ':message') }}</span>
        </div>
    </div>
    <div class="col-md-6 col-lg-4">
        <div class="form-group required {{ $errors->has('year') ? 'has-error' : '' }}">
            {!! Form::label('year', 'Año', ['class' => 'form-label required']) !!}
            {!! Form::text('year', '2022', ['class' => 'form-control']) !!}
            <span class="help-block">{{ $errors->first('year', ':message') }}</span>
        </div>
    </div>
</div>

@section('page-script')
    @parent
    <script>
        $(function () {
            'use strict';

            var select = $('.global-info-select2');
            
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
            
        });
    </script>
@endsection