<div class="modal fade" id="sendReportModal" tabindex="-1" aria-labelledby="sendReport" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-refer-earn">
    <div class="modal-content">
      <div class="modal-header bg-transparent">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body px-sm-0">
        <div class="px-sm-5 mx-50">
          <h4 class="fw-bolder">Enviar Reporte</h4>
          <form id="sendReportForm" class="row g-1">
            @csrf
            <div class="col-lg-10">
              <label class="form-label" for="modalSendReportEmailTo">
                Ingresa el correo electrónico a quien enviaremos el Reporte
              </label>
              <input type="hidden" name="year_month" id="modalSendReportYearMonth" value="" />
              <input type="text" name="email" id="modalSendReportEmailTo" value="" class="form-control" placeholder="juan@gmail.com" aria-label="juan@gmail.com" />
            </div>
            <div class="col-lg-2 d-flex align-items-end">
              <button type="submit" class="btn btn-primary w-100">Enviar</button>
            </div>
          </form>
          <div class="col-lg-12 text-center">
            <a class="btn btn-info me-1 mt-1" id="modalSendReportDownloadPdf" href="">Descargar PDF</a>
            <a class="btn btn-info me-1 mt-1" id="modalSendReportDownloadXls" href="">Descargar Excel</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@section('page-script')
@parent
<script>
  'use strict';

  var sendReportForm = $('#sendReportForm');

  if ($('body').attr('data-framework') === 'laravel') {
      var assetPath = $('body').attr('data-asset-path'),
          basePath = assetPath + 'report/';
  }

  sendReportForm.on('submit', function (e) {
      e.preventDefault();

      sendReportForm.block({
          message:
              '<div class="d-flex justify-content-center align-items-center"><p class="me-50 mb-0" text-primary>Enviando Reporte...</p> <div class="spinner-grow spinner-grow-sm text-primary" role="status"></div> </div>',
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
          url: basePath + 'send',
          data: sendReportForm.serialize(),
          success: function (msg) {
              sendReportForm.block({
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
              sendReportForm.block({
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