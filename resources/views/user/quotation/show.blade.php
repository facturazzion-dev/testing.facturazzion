@extends('layouts/contentLayoutMaster')

@section('title', $title)

@section('content')
<section class="quotation-preview-wrapper">
  <div class="row quotation-preview">
    <!-- Quotation -->
    <div class="col-xl-9 col-md-8 col-12">
      <div class="card quotation-preview-card">
        <div class="card-body quotation-padding pb-0">
          <iframe class="w-100 vh-100 bg-secondary" frameborder="0" src="{{ url($type).'/'.$quotation->id.'/print_quot' }}"></iframe>  
        </div>
      </div>
    </div>
    <!-- /Quotation -->

    <!-- Quotation Actions -->
    <div class="col-xl-3 col-md-4 col-12 quotation-actions mt-md-0 mt-2">
      <div class="card">
        <div class="card-body">
          <button class="btn btn-primary w-100 mb-75" data-bs-toggle="modal" data-bs-target="#sendQuotationModal">
            Enviar Cotización
          </button>
          <a class="btn btn-outline-secondary w-100 btn-download-quotation mb-75" href="/quotation/{{$quotation->id}}/download_pdf" >Descargar PDF</a>
        </div>
      </div>
    </div>
    <!-- /Quotation Actions -->
  </div>
</section>
@include('content._partials._modals.modal-send-quotation')
@endsection