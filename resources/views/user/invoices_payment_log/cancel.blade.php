@extends('layouts/contentLayoutMaster')

{{-- Web site Title --}}
@section('title')
{{ $title }}
@stop

{{-- Content --}}
@section('content')
  {!! Form::open(['url' => $type . '/' . $payment->id . '/confirm-cancel', 'method' => 'post', 'class' => 'row mb-2']) !!}
    <div class="col-12 col-md-4">
      <div class="form-group required">
        {!! Form::label('motivo', 'Motivo de cancelación', ['class' => 'control-label required']) !!}
        <div class="controls">
          {!! Form::select('motivo', array(
          '01'=>'Comprobantes emitidos con errores con relación. - 01',
          '02'=>'Comprobantes emitidos con errores sin relación. - 02',
          '03'=>'No se llevó a cabo la operación. - 03',
          '04'=>'Operación nominativa relacionada en una factura global. - 04'
          ),'02', ['class' => 'form-control', 'placeholder' => 'Seleccione un motivo']) !!}
          <span class="help-block">{{ $errors->first('motivo', ':message') }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4" id="folioSustitucionPanel">
      <div class="form-group required">
        {!! Form::label('folioSustitucion', 'Folio de Sustitución', ['class' => 'control-label']) !!}
        <div class="controls">
          {!! Form::text('folioSustitucion', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el folio de la factura que sustituye a la cancelada']) !!}
          <span class="help-block">{{ $errors->first('folioSustitucion', ':message') }}</span>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4 mt-2">
      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancelar REP</button>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cancelar REP</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            ¿Estás seguro que quieres cancelar este Recibo de Pago?
          </div>
          <div class="modal-footer">
            <div class="form-group">
              <div class="controls">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                <button type="submit" class="btn btn-primary">Si, estoy seguro</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  {!! Form::close() !!}
@include('user/'.$type.'/_details')
@stop

@section('page-script')
<script>
  $(document).ready(function() {
    $('#folioSustitucionPanel').hide();
    $('#motivo').change(function() {
      if ($(this).val() == '01') // get selected value
      {
        $('#folioSustitucionPanel').show();
      } else {
        $('#folioSustitucionPanel').hide();
      }
    });
  });
</script>
@endsection