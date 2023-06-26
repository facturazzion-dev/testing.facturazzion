<div class="card">
    <div class="card-header bg-white">
        <h4 class="float-left">Impuestos Locales</h4>
        @if($user->hasAccess(['taxes.write']) || $orgRole=='admin')
            <div class="pull-right">
                <a href="{{ 'local_tax/create' }}" class="btn btn-primary ">
                    <i class="fa fa-plus-circle"></i> {{ trans('tax.create') }}</a>
                
            </div>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="data2" class="table table-striped table-bordered local-tax-list-table">
                <thead>
                <tr>
                    <th class="cell-fit"></th>
                    <th>ID</th>
                    <th>{{ trans('tax.name') }}</th>
                    <th>{{ trans('tax.tax_type') }}</th>
                    <th>{{ trans('tax.percentage') }}</th>
                    <th>{{ trans('tax.factor_type') }}</th>
                </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>
