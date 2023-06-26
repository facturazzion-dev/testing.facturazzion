<div class="modal fade" id="sendRepModal" tabindex="-1" aria-labelledby="sendRep" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-refer-earn">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-0">
        <div class="px-sm-5 mx-50">
          <h4 class="fw-bolder">Enviar Recibo REP</h4>
          <form class="row g-1">
            @csrf
            <div class="col-lg-10">
              <label class="form-label" for="modalSendRepEmailToMe">
                Emisor
              </label>
              <input type="hidden" name="id" class="hidden-document-id" value="{{ isset($invoiceReceivePayment) ? $invoiceReceivePayment->id : '' }}" />
              <input type="text" name="email" id="modalSendRepEmailToMe" value="{{ config('settings.site_email') }}" class="form-control" placeholder="juan@gmail.com" aria-label="juan@gmail.com" />
            </div>
            <div class="col-lg-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Enviar</button>
            </div>
          </form>
          <form class="row g-1">
            @csrf
            <div class="col-lg-10">
              <label class="form-label" for="modalSendRepEmailTo">
                Receptor
              </label>
              <input type="hidden" name="id" class="hidden-document-id" value="{{ isset($invoiceReceivePayment) ? $invoiceReceivePayment->id : '' }}" />
              <input type="text" name="email" id="modalSendRepEmailTo" value="{{ isset($invoiceReceivePayment) ? $invoiceReceivePayment->companies->email : '' }}" class="form-control" placeholder="juan@gmail.com" aria-label="juan@gmail.com" />
            </div>
            <div class="col-lg-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Enviar</button>
            </div>
          </form>
          <div class="col-lg-12 text-center mb-1">
            <a class="btn btn-info me-1 mt-1" id="modalSendRepDownloadPdf" href="{{ isset($invoiceReceivePayment) ? '/invoices_payment_log/'.$invoiceReceivePayment->id.'/download_pdf' : '' }}">Descargar PDF</a>
            <a class="btn btn-info me-1 mt-1" id="modalSendRepDownloadXml" href="{{ isset($invoiceReceivePayment) ? '/invoices_payment_log/'.$invoiceReceivePayment->id.'/download_xml' : '' }}">Descargar XML</a>
          </div>
          @if(!isset($invoiceReceivePayment))
          <iframe id="modalSendRepIframePdf" src="" style="width:100%; height:400px;" frameborder="0"></iframe>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@section('page-script')
@parent
<script>
  'use strict';

var sendRepForm = $('#sendRepModal').find('form');

if ($('body').attr('data-framework') === 'laravel') {
    var assetPath = $('body').attr('data-asset-path'),
        basePath = assetPath + 'invoices_payment_log/';
}

sendRepForm.on('submit', function (e) {
    e.preventDefault();
    var $this = $(this);

    $this.block({
        message:
            '<div class="d-flex justify-content-center align-items-center"><p class="me-50 mb-0" text-primary>Enviando Recibo REP...</p> <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div> </div>',
        css: {
            backgroundColor: 'transparent',
            border: '0'
        },
        overlayCSS: {
            backgroundColor: '#fff',
            opacity: 0.8
        }
    });
    $.ajax({
        type: "POST",
        url: basePath + 'send_cfdi',
        data: $this.serialize(),
        success: function (msg) {
          $this.block({
                message: '<div class="p-1 bg-primary">' + msg + '</div>',
                timeout: 2000,
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: '0'
                },
                overlayCSS: {
                    opacity: 0.25
                }
            });
        },
        error: function (e) {
          $this.block({
                message: '<div class="p-1 bg-danger">' + e + '</div>',
                timeout: 2000,
                css: {
                    backgroundColor: 'transparent',
                    color: '#fff',
                    border: '0'
                },
                overlayCSS: {
                    opacity: 0.25
                }
            });
        }
    });
});
</script>
@endsection