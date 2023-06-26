<div class="card">
    <div class="card-body">
        @if (isset($cfdi_profile))
            {!! Form::model($cfdi_profile, ['url' => $type . '/' . $cfdi_profile->id, 'method' => 'put', 'files'=> true, 'id'=>'form']) !!}
        @else
            {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true, 'id'=>'form']) !!}
        @endif
        <div class="row">
            <div class="col-md-3">
                <div class="form-group required {{ $errors->has('quotation_template') ? 'has-error' : '' }}">
                    {!! Form::label('name', trans('cfdi_profile.name'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('name', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('name', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group required {{ $errors->has('cfdi_name') ? 'has-error' : '' }}">
                    {!! Form::label('cfdi_name', trans('cfdi_profile.cfdi_name'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('cfdi_name', array('fact_normal'=>'Factura Normal','fact_ret'=>'Factura con Retenciones','carta_porte'=>'Carta Porte','rec_hon'=>'Recibo de Honorarios','nota_credit'=>'Nota de Crédito'),null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('tax', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group required {{ $errors->has('cfdi_type') ? 'has-error' : '' }}">
                    {!! Form::label('cfdi_type', trans('cfdi_profile.cfdi_type'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('cfdi_type', array('I'=>'Ingreso','E'=>'Egreso','T'=>'Traslado'),null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('cfdi_type', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="form-group required {{ $errors->has('taxes') ? 'has-error' : '' }}">
                    {!! Form::label('taxes', trans('cfdi_profile.taxes'),['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::select('taxes[]', $taxes, null, array('id'=>'taxes', 'multiple'=>true, 'class' => 'form-control select2')) !!}
                        <span class="help-block">{{ $errors->first('taxes', ':message') }}</span>
                    </div>
                </div>
            </div>            
            
        </div>
        
        <!-- Form Actions -->
        <div class="form-group">
            <div class="controls">
                <button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
                <a href="{{ route($type.'.index') }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
            </div>
        </div>
        <!-- ./ form actions -->

        {!! Form::close() !!}
    </div>
</div>

@section('scripts')
    <script>
        $(document).ready(function(){
            
            $("#cfdi_name").select2({
                theme:"bootstrap",
                placeholder:"Nombre del comprobante"
            });
            $("#cfdi_type").select2({
                theme:"bootstrap",
                placeholder:"Uso del comprobante"
            });
            $("#taxes").select2({
                theme:"bootstrap",
                placeholder:"Impuestos"
            });
            $('.icheckblue').iCheck({
                checkboxClass: 'icheckbox_minimal-blue',
                radioClass: 'iradio_minimal-blue'
            });
        });


    </script>
@endsection