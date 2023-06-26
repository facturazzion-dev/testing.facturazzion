@extends('layouts/contentLayoutMaster')

{{-- Content --}}
@section('content')
  {!! Form::open(['url' => $type . '/' . $quotation->id, 'method' => 'delete', 'class' => 'row mb-2']) !!}
    <div class="col-12 col-md-4 mt-2">
      <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">Cancelar cotización</button>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Cancelar cotización</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            ¿Estás seguro que quieres cancelar esta cotización?
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