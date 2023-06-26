@extends('layouts/contentLayoutMaster')

@section('title', $title)

@section('content')
<section class="invoice-preview-wrapper">
  <div class="row invoice-preview">
    <!-- Invoice -->
    <div class="col-xl-9 col-md-8 col-12">
      <div class="card invoice-preview-card">
        <div class="card-body invoice-padding pb-0">
          <iframe class="w-100 vh-100 bg-secondary" frameborder="0" src="{{ url($type).'/'.$invoice->id.'/print_quot' }}"></iframe>  
        </div>
      </div>
    </div>
    <!-- /Invoice -->

    <!-- Invoice Actions -->
    <div class="col-xl-3 col-md-4 col-12 invoice-actions mt-md-0 mt-2">
      <div class="card">
        <div class="card-body">
          <button class="btn btn-primary w-100 mb-75" data-bs-toggle="modal" data-bs-target="#sendInvoiceModal">
            Enviar Factura
          </button>
          <a class="btn btn-outline-secondary w-100 btn-download-invoice mb-75" href="/invoice/{{$invoice->id}}/download_pdf" >Descargar PDF</a>
          <a class="btn btn-outline-secondary w-100 btn-download-invoice mb-75" href="/invoice/{{$invoice->id}}/download_xml" >Descargar XML</a>
          @if ($invoice->payment_method == 'PPD' && $invoice->is_delete_list != 1 && $invoice->status != 'Borrador')
            <a class="btn btn-success w-100" href="/invoices_payment_log/pay_invoice/{{$invoice->id}}" >Agregar Pago</a>
          @endif
        </div>
      </div>
    </div>
    <!-- /Invoice Actions -->
  </div>
</section>
@include('content._partials._modals.modal-send-invoice')
@endsection