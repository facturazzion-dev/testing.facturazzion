@extends('layouts/contentLayoutMaster')

@section('title', $title)

@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/dataTables.bootstrap5.min.css')}}">
<link rel="stylesheet" href="{{asset('vendors/css/tables/datatable/responsive.bootstrap5.min.css')}}">
@endsection

@section('page-style')
<link rel="stylesheet" href="{{asset('css/base/pages/app-invoice-list.css')}}">
@endsection

@section('content')
<section class="invoice-list-wrapper">
  <div class="card">
    <div class="card-datatable table-responsive">
      <table class="quotation-list-table table">
        <thead>
          <tr>
            <th></th>
            <th>#</th>
            <th>Cliente</th>
            <th>Total</th>
            <th class="text-truncate">Fecha de Emisión</th>
            <th class="cell-fit">Opciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>
</section>
@endsection

@section('vendor-script')
<script src="{{asset('vendors/js/extensions/moment.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/datatables.buttons.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/dataTables.responsive.min.js')}}"></script>
<script src="{{asset('vendors/js/tables/datatable/responsive.bootstrap5.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('js/scripts/pages/app-quotation-draft-list.js')}}"></script>
@endsection