@extends('layouts/contentLayoutMaster')

@section('title', $title)

@section('content')
<section class="saleorder-preview-wrapper">
  <div class="row saleorder-preview">
    <!-- Saleorder -->
    <div class="col-xl-9 col-md-8 col-12">
      <div class="card saleorder-preview-card">
        <div class="card-body saleorder-padding pb-0">
          <iframe class="w-100 vh-100 bg-secondary" frameborder="0" src="{{ url($type).'/'.$saleorder->id.'/print_quot' }}"></iframe>  
        </div>
      </div>
    </div>
    <!-- /Saleorder -->

    <!-- Saleorder Actions -->
    <div class="col-xl-3 col-md-4 col-12 saleorder-actions mt-md-0 mt-2">
      <div class="card">
        <div class="card-body">
          <button class="btn btn-primary w-100 mb-75" data-bs-toggle="modal" data-bs-target="#sendSaleorderModal">
            Enviar Nota de Venta
          </button>
          <a class="btn btn-outline-secondary w-100 btn-download-saleorder mb-75" href="{{ url($type).'/'.$saleorder->id.'/download_pdf' }}" >Descargar PDF</a>
        </div>
      </div>
    </div>
    <!-- /Saleorder Actions -->
  </div>
</section>
@include('content._partials._modals.modal-send-saleorder')
@endsection