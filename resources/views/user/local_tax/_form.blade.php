<div class="card">
    <div class="card-body">
        @if (isset($tax))
            {!! Form::model($tax, ['url' => $type . '/' . $tax->id, 'method' => 'put', 'files'=> true]) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true]) !!}
        @endif
        
        <div class="row">
            <div class="col-3">
                <div class="form-group required {{ $errors->has('name') ? 'has-error' : '' }}">
                    {!! Form::label('name', trans('tax.name'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                    </div>
                </div>
            </div>
            
        
            <div class="col-lg-3">
                <div class="form-group required {{ $errors->has('tax_type') ? 'has-error' : '' }}">
                    {!! Form::label('tax_type', 'Tipo', ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('tax_type', array('Traslado'=>'Traslado','Retención'=>'Retención'),null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('tax_type', ':message') }}</span>
                    </div>
                </div>
            </div>            
            <div class="col-lg-3">
                <div class="form-group required {{ $errors->has('percentage') ? 'has-error' : '' }}">
                    {!! Form::label('percentage', 'Porcentaje', ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('percentage', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('percentage', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group required {{ $errors->has('factor_type') ? 'has-error' : '' }}">
                    {!! Form::label('factor_type', 'Tipo de Factor', ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::select('factor_type', array('Tasa'=>'Tasa','Cuota'=>'Cuota','Exento'=>'Exento'),null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('factor_type', ':message') }}</span>
                    </div>
                </div>
            </div>                      
        </div>
            

        <div class="mt-2">
                <a href="/tax" class="btn btn-outline-secondary"><i class="fa fa-arrow-left"></i> {{trans('table.cancel')}}</a>
                <button type="submit" class="btn btn-primary"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>


@section('page-script')
    <script>
        $(document).ready(function () {
            $("#tax").select2({
                theme: 'bootstrap',
                placeholder:"{{trans('tax.tax_type')}}"
            });
            $("#tax_type").select2({
                theme: 'bootstrap',
                placeholder:"{{trans('tax.tax_type')}}"
            });
            $("#factor_type").select2({
                theme: 'bootstrap',
                placeholder:"{{trans('tax.tax_type')}}"
            });
        });
    </script>
@endsection
