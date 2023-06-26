<div class="card">
    <div class="card-body">
        <div class="row">
            <iframe class="w-100 vh-100 bg-secondary" frameborder="0" src="{{ url($type).'/'.$payment->id.'/print_quot' }}"></iframe>  
        </div>
        <div class="row">
    <div class="col-md-12">
        <br>
        <label class="control-label">Facturas Relacionadas</label>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr class="detailes-tr">
                        <th>Serie</th>
                        <th>Folio</th>
                        <th>Fecha</th>
                        <th>Moneda</th>
                        <th>UUID</th>
                        <th>Descargar</th>
                    </tr>
                </thead>
                <tbody id="InputsWrapper">
                    @if(isset($invoiceReceivePayment)&& $invoiceReceivePayment->paidInvoices->count()>0)
                        @foreach($invoiceReceivePayment->paidInvoices as $index => $variants)
                            <tr class="remove_tr">
                                <td> {{$variants->invoice_serie}} </td>
                                <td> {{$variants->invoice_number}} </td>
                                <td> {{$variants->invoice_date}} </td>
                                <td> {{$variants->currency}} </td>
                                <td> {{$variants->uuid_sat}} </td>
                                @if($variants->status != 'otro')
                                    <td>
                                        <a href= "https://facturazzion.com/invoice/{{$variants->id}}/download_pdf"> <i class="fa fa-fw fa-file-pdf-o"></i></a>
                                        <a href= "https://facturazzion.com/invoice/{{$variants->id}}/download_xml"> <i class="fa fa-fw fa-file-code-o"></i></a>
                                    </td>
                                @else
                                    <td>
                                        No disponible
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
        
        <div class="form-group">
            <div class="controls">
                @if (@$action == trans('action.show'))
                    <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                @else
                    <a href="{{ url($type) }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
                    <button type="submit" class="btn btn-danger"><i class="fa fa-ban"></i> {{trans('table.cancel')}}</button>
                @endif
            </div>
        </div>
    </div>
</div>