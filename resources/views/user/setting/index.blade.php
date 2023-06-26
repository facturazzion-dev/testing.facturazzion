@extends('layouts/contentLayoutMaster')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('vendors/css/forms/select/select2.min.css')}}">
@endsection

@section('content')
    @include('flash::message')
    @if($errors->any())
        <div class="alert alert-danger">
            {{  trans('settings.mandatory_fields_valid') }}
        </div>
    @endif
    <div class="card" xmlns:v-bind="http://symfony.com/schema/routing">
        <div class="card-body">
            {!! Form::open(['url' => url('setting'), 'method' => 'post', 'files'=> true]) !!}
            <div class="nav-tabs-custom" id="setting_tabs">
                <ul class="nav nav-tabs settings" role="tablist">
                    <li class="nav-item">
                        <a href="#general_configuration"
                           data-bs-toggle="tab" class="nav-link active" title="">Datos Fiscales</a>
                    </li>
                    <li class="nav-item">
                        <a href="#csd_configuration"
                           data-bs-toggle="tab" class="nav-link" title="">Archivos CSD</a>
                    </li>
                    <li class="nav-item">
                        <a href="#payment_configuration"
                           data-bs-toggle="tab" class="nav-link" title="">{{ trans('settings.payment_configuration') }}</a>
                    </li>
                    <li class="nav-item">
                        <a href="#website_settings"
                           data-bs-toggle="tab" class="nav-link" title="">Ajustes del sitio</a>
                    </li>
                    <li class="nav-item">
                        <a href="#start_number_prefix_configuration"
                           data-bs-toggle="tab" class="nav-link" title="">Plantillas y folios</a>
                    </li>
                    <!-- <li class="nav-item">
                        <a href="#paypal_settings"
                           data-bs-toggle="tab" class="nav-link" title="{{ trans('settings.paypal_settings') }}"><i
                                    class="material-icons md-24">payment</i></a>
                    </li> -->
                    
                    <!-- <li class="nav-item">
                        <a href="#europian_tax"
                           data-bs-toggle="tab" class="nav-link" title="{{ trans('settings.europian_tax') }}"><i
                                    class="fa fa-money md-24"></i></a>
                    </li> -->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="general_configuration">
                        
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group required {{ $errors->has('site_name') ? 'has-error' : '' }}">
                                    {!! Form::label('site_name', trans('organizations.name'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('site_name', old('site_name', (isset($orgSettings['site_name'])?$orgSettings['site_name']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('site_name', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                    
                            <div class="col-lg-4">
                                <div class="form-group required {{ $errors->has('site_email') ? 'has-error' : '' }}">
                                    {!! Form::label('site_email', trans('settings.organization_email'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('site_email', old('site_email', (isset($orgSettings['site_email'])?$orgSettings['site_email']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('site_email', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group required {{ $errors->has('phone') ? 'has-error' : '' }}">
                                    {!! Form::label('phone', trans('settings.phone'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::text('phone', old('phone', (isset($orgSettings['phone'])?$orgSettings['phone']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('phone', ':message') }}</span>
                                    </div>
                                </div>
                            </div>    
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="form-group required {{ $errors->has('sat_name') ? 'has-error' : '' }}">
                                    {!! Form::label('sat_name', trans('company.sat_name'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('sat_name', old('sat_name', (isset($orgSettings['sat_name'])?$orgSettings['sat_name']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('sat_name', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="form-group required {{ $errors->has('sat_rfc') ? 'has-error' : '' }}">
                                    {!! Form::label('sat_rfc', trans('company.sat_rfc'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('sat_rfc', old('sat_rfc', (isset($orgSettings['sat_rfc'])?$orgSettings['sat_rfc']:"")), ['class' => 'form-control text-uppercase']) !!}
                                        <span class="help-block">{{ $errors->first('sat_rfc', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                          
                            <div class="col-lg-4">
                                <div class="form-group required {{ $errors->has('fiscal_regimen') ? 'has-error' : '' }}">
                                    {!! Form::label('fiscal_regimen', 'Régimen Fiscal', ['class' => 'control-label required ']) !!}
                                    <div class="controls">
                                        {!! Form::select('fiscal_regimen', array(
                                            '601'=>'General de Ley Personas Morales',
                                            '603'=>'Personas Morales con Fines no Lucrativos',
                                            '605'=>'Sueldos y Salarios e Ingresos Asimilados a Salarios',
                                            '606'=>'Arrendamiento',
                                            '607'=>'Régimen de Enajenación o Adquisición de Bienes',
                                            '608'=>'Demás ingresos',
                                            '610'=>'Residentes en el Extranjero sin Establecimiento Permanente en México',
                                            '611'=>'Ingresos por Dividendos (socios y accionistas)',
                                            '612'=>'Personas Físicas con Actividades Empresariales y Profesionales',
                                            '614'=>'Ingresos por intereses',
                                            '615'=>'Régimen de los ingresos por obtención de premios',
                                            '616'=>'Sin obligaciones fiscales',
                                            '620'=>'Sociedades Cooperativas de Producción que optan por diferir sus ingresos',
                                            '621'=>'Incorporación Fiscal',
                                            '622'=>'Actividades Agrícolas, Ganaderas, Silvícolas y Pesqueras',
                                            '623'=>'Opcional para Grupos de Sociedades',
                                            '624'=>'Coordinados',
                                            '625'=>'Régimen de las Actividades Empresariales con ingresos a través de Plataformas Tecnológicas',
                                            '626'=>'Régimen Simplificado de Confianza'
                                            ), isset($orgSettings['fiscal_regimen'])?$orgSettings['fiscal_regimen']:'601', ['class' => 'form-select']) !!}
                                        <span class="help-block">{{ $errors->first('fiscal_regimen', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                                                                    
                        </div>
                        <div class="row">                        
                            <div class="col-4">
                                <div class="form-group  {{ $errors->has('street') ? 'has-error' : '' }}">
                                    {!! Form::label('street', trans('company.street'), ['class' => 'control-label ']) !!}
                                    <div class="controls">
                                        {!! Form::text('street', old('street', (isset($orgSettings['street'])?$orgSettings['street']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('street', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                        
                            <div class="col-lg-2">
                                <div class="form-group  {{ $errors->has('exterior_no') ? 'has-error' : '' }}">
                                    {!! Form::label('exterior_no', trans('company.exterior_no'), ['class' => 'control-label ']) !!}
                                    <div class="controls">
                                        {!! Form::text('exterior_no', old('exterior_no', (isset($orgSettings['exterior_no'])?$orgSettings['exterior_no']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('exterior_no', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group  {{ $errors->has('interior_no') ? 'has-error' : '' }}">
                                    {!! Form::label('interior_no', trans('company.interior_no'), ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::text('interior_no', old('interior_no', (isset($orgSettings['interior_no'])?$orgSettings['interior_no']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('interior_no', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-4">
                                <div class="form-group  {{ $errors->has('suburb') ? 'has-error' : '' }}">
                                    {!! Form::label('suburb', trans('company.suburb'), ['class' => 'control-label ']) !!}
                                    <div class="controls">
                                        {!! Form::text('suburb', old('suburb', (isset($orgSettings['suburb'])?$orgSettings['suburb']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('suburb', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                        
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="form-group  required{{ $errors->has('zip_code') ? 'has-error' : '' }}">
                                    {!! Form::label('zip_code', trans('company.zip_code'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('zip_code', old('zip_code', (isset($orgSettings['zip_code'])?$orgSettings['zip_code']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('zip_code', ':message') }}</span>
                                    </div>
                                </div>
                            </div> 
                            <div class="col-3">
                                <div class="form-group  {{ $errors->has('country_id') ? 'has-error' : '' }}">
                                    {!! Form::label('country_id', trans('company.country'), ['class' => 'control-label ']) !!}
                                    <div class="controls">
                                        {!! Form::select('country_id', $countries, isset($orgSettings['country_id'])?$orgSettings['country_id']:null, ['id'=>'country_id', 'class' => 'form-select select2']) !!}
                                        <span class="help-block">{{ $errors->first('country_id', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                         
                            <div class="col-lg-3">
                                <div class="form-group  {{ $errors->has('state_id') ? 'has-error' : '' }}">
                                    {!! Form::label('state_id', trans('company.state'), ['class' => 'control-label ']) !!}
                                    <div class="controls">
                                        {!! Form::select('state_id', $states, isset($orgSettings['state_id'])?$orgSettings['state_id']:null, ['id'=>'state_id', 'class' => 'form-select select2']) !!}
                                        <span class="help-block">{{ $errors->first('state_id', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group  {{ $errors->has('city_id') ? 'has-error' : '' }}">
                                    {!! Form::label('city_id', trans('company.city'), ['class' => 'control-label ']) !!}
                                    <div class="controls">
                                        {!! Form::select('city_id', $cities, isset($orgSettings['city_id'])?$orgSettings['city_id']:null,  ['id'=>'city_id', 'class' => 'form-select select2']) !!}
                                        <span class="help-block">{{ $errors->first('city_id', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                                                                     
                        </div>

                        <div class="form-group  {{ $errors->has('site_logo_file') ? 'has-error' : '' }} ">
                            {!! Form::label('site_logo_file', trans('settings.organization_logo'), ['class' => 'control-label ']) !!}
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="row">
                                        {!! Form::file('site_logo_file', null, ['id' => 'site_logo_file']) !!}
                                        <img id="logo_file" src="{{ url((isset($settings['site_logo'])?$settings['site_logo']:'uploads/site/logo.png')) }}" alt="logo">
                                    </div>
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('site_logo_file', ':message') }}</span>

                        </div>                        
                    </div>
                    <div class="tab-pane" id="csd_configuration">
                        <br>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group  {{ $errors->has('cer_file') ? 'has-error' : '' }}">
                                    {!! Form::label('cer_file', 'Subir archivo .cer', ['class' => 'control-label']) !!}
                                        @if(isset($orgSettings['cer_file']))
                                            <span class="alert-success">Configurado<i class="fa fa-check-square-o"></i></span> 
                                            {!! Form::file('cer_file', null, ['class' => 'form-control']) !!}
                                        @else
                                            {!! Form::file('cer_file', null, ['class' => 'form-control']) !!}
                                        @endif
                                    
                                    <span class="help-block">{{ $errors->first('cer_file', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group  {{ $errors->has('key_file') ? 'has-error' : '' }}">
                                    {!! Form::label('key_file', 'Subir archivo .key', ['class' => 'control-label']) !!}
                                        @if(isset($orgSettings['key_file']))
                                            <span class="alert-success">Configurado<i class="fa fa-check-square-o"></i></span> 
                                            {!! Form::file('key_file', null, ['class' => 'form-control']) !!}
                                        @else
                                            {!! Form::file('key_file', null, ['class' => 'form-control']) !!}
                                        @endif
                                    
                                    <span class="help-block">{{ $errors->first('key_file', ':message') }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group  {{ $errors->has('fiel_pwd') ? 'has-error' : '' }}">
                                    {!! Form::label('fiel_pwd', '(CSD) Contraseña', ['class' => 'control-label']) !!}
                                    <div class="controls">
                                        {!! Form::input( 'text', 'fiel_pwd', old('fiel_pwd', (isset($orgSettings['fiel_pwd'])?$orgSettings['fiel_pwd']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('fiel_pwd', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                    </div>
                    <div class="tab-pane" id="payment_configuration">
                        <div class="form-group required {{ $errors->has('sales_tax') ? 'has-error' : '' }}">
                            {!! Form::label('sales_tax', trans('settings.sales_tax').'%', ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::input('number','sales_tax', old('sales_tax', (isset($orgSettings['sales_tax'])?$orgSettings['sales_tax']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('sales_tax', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->has('payment_term1') ? 'has-error' : '' }}">
                            {!! Form::label('payment_term1', trans('settings.payment_term1'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::input('number','payment_term1', old('payment_term1', (isset($orgSettings['payment_term1'])?$orgSettings['payment_term1']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('payment_term1', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->has('payment_term2') ? 'has-error' : '' }}">
                            {!! Form::label('payment_term2', trans('settings.payment_term2'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::input('number','payment_term2', old('payment_term2', (isset($orgSettings['payment_term2'])?$orgSettings['payment_term2']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('payment_term2', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->has('payment_term3') ? 'has-error' : '' }}">
                            {!! Form::label('payment_term3', trans('settings.payment_term3'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::input('number','payment_term3', old('payment_term3', (isset($orgSettings['payment_term3'])?$orgSettings['payment_term3']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('payment_term3', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->has('opportunities_reminder_days') ? 'has-error' : '' }}">
                            {!! Form::label('opportunities_reminder_days', trans('settings.opportunities_reminder_days'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::input('number','opportunities_reminder_days', old('opportunities_reminder_days', (isset($orgSettings['opportunities_reminder_days'])?$orgSettings['opportunities_reminder_days']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('opportunities_reminder_days', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->has('invoice_reminder_days') ? 'has-error' : '' }}">
                            {!! Form::label('invoice_reminder_days', trans('settings.invoice_reminder_days'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::input('number','invoice_reminder_days', old('invoice_reminder_days', (isset($orgSettings['invoice_reminder_days'])?$orgSettings['invoice_reminder_days']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('invoice_reminder_days', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="website_settings">
                        <div class="form-group required {{ $errors->has('date_format') ? 'has-error' : '' }}">
                            {!! Form::label('date_format', trans('settings.date_format'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                <div class="radio">
                                    {!! Form::radio('date_format', 'F j,Y',((isset($orgSettings['date_format'])?$orgSettings['date_format']:"")=='F j,Y')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('date_format', date('F j,Y'))  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('date_format', 'Y-d-m',((isset($orgSettings['date_format'])?$orgSettings['date_format']:"")=='Y-d-m')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('date_format', date('Y-d-m'))  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('date_format', 'd.m.Y.',((isset($orgSettings['date_format'])?$orgSettings['date_format']:"")=='d.m.Y.')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('date_format', date('d.m.Y.'))  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('date_format', 'd.m.Y',((isset($orgSettings['date_format'])?$orgSettings['date_format']:"")=='d.m.Y')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('date_format', date('d.m.Y'))  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('date_format', 'd/m/Y',((isset($orgSettings['date_format'])?$orgSettings['date_format']:"")=='d/m/Y')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('date_format', date('d/m/Y'))  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('date_format', 'm/d/Y',((isset($orgSettings['date_format'])?$orgSettings['date_format']:"")=='m/d/Y')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('date_format', date('m/d/Y'))  !!}
                                </div>
                                <div class="form-inline">
                                    {!! Form::label('custom_format', trans('settings.custom_format'))  !!}
                                    {!! Form::input('text','date_format_custom', config('settings.date_format'), ['class' => 'form-control']) !!}
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('date_format', ':message') }}</span>
                        </div>
                        <a href="{{url('http://php.net/manual/en/function.date.php')}}">{!! trans('settings.date_time_format') !!}</a>
                        <div class="form-group required {{ $errors->has('time_format') ? 'has-error' : '' }}">
                            {!! Form::label('time_format', trans('settings.time_format'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                <div class="radio">
                                    {!! Form::radio('time_format', 'g:i a',((isset($orgSettings['time_format'])?$orgSettings['time_format']:"")=='g:i a')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('time_format', date('g:i a'))  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('time_format', 'g:i A',((isset($orgSettings['time_format'])?$orgSettings['time_format']:"")=='g:i A')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('time_format', date('g:i A'))  !!}
                                </div>
                                <div class="radio">
                                    {!! Form::radio('time_format', 'H:i',((isset($orgSettings['time_format'])?$orgSettings['time_format']:"")=='H:i')?true:false,['class' => 'icheck'])  !!}
                                    {!! Form::label('time_format', date('H:i'))  !!}
                                </div>
                                <div class="form-inline">
                                    {!! Form::label('custom_format', trans('settings.custom_format'))  !!}
                                    {!! Form::input('text','time_format_custom', config('settings.time_format'), ['class' => 'form-control']) !!}
                                </div>
                            </div>
                            <span class="help-block">{{ $errors->first('date_format', ':message') }}</span>
                        </div>
                        <div class="form-group required {{ $errors->has('currency') ? 'has-error' : '' }}">
                            {!! Form::label('currency', trans('settings.currency'), ['class' => 'control-label required']) !!}
                            <div class="controls">
                                {!! Form::select('currency', $currency, old('currency', (isset($orgSettings['currency'])?$orgSettings['currency']:"")), ['id'=>'currency','class' => 'form-control select2']) !!}
                                <span class="help-block">{{ $errors->first('currency', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->has('language') ? 'has-error' : '' }}">
                            {!! Form::label('language', trans('Language'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::select('language', $languages, old('language',isset($orgSettings['language'])?$orgSettings['language']:''), ['class' => 'form-control select2']) !!}
                                <span class="help-block">{{ $errors->first('language', ':message') }}</span>
                            </div>
                        </div>
                        <!-- <div class="form-group required {{ $errors->has('stripe_publishable') ? 'has-error' : '' }}">
                            {!! Form::label('stripe_publishable', trans('settings.stripe_publishable'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::input('text','stripe_publishable', old('stripe_publishable', (isset($orgSettings['stripe_publishable'])?$orgSettings['stripe_publishable']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('stripe_publishable', ':message') }}</span>
                            </div>
                        </div>
                        <div class="form-group required {{ $errors->has('stripe_secret') ? 'has-error' : '' }}">
                            {!! Form::label('stripe_secret', trans('settings.stripe_secret'), ['class' => 'control-label']) !!}
                            <div class="controls">
                                {!! Form::input('text','stripe_secret', old('stripe_secret', (isset($orgSettings['stripe_secret'])?$orgSettings['stripe_secret']:"")), ['class' => 'form-control']) !!}
                                <span class="help-block">{{ $errors->first('stripe_secret', ':message') }}</span>
                            </div>
                        </div> -->
                    </div>
                    <div class="tab-pane" id="start_number_prefix_configuration">
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('quotation_prefix') ? 'has-error' : '' }}">
                                    {!! Form::label('quotation_prefix', trans('settings.quotation_prefix'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('quotation_prefix', old('quotation_prefix', (isset($orgSettings['quotation_prefix'])?$orgSettings['quotation_prefix']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('quotation_prefix', ':message') }}</span>
                                    </div>
                                </div>    
                            </div>
                            <div class="col-4">
                                 <div class="form-group required {{ $errors->has('quotation_start_number') ? 'has-error' : '' }}">
                                    {!! Form::label('quotation_start_number', trans('settings.quotation_start_number'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::input('number','quotation_start_number', old('quotation_start_number', (isset($orgSettings['quotation_start_number'])?$orgSettings['quotation_start_number']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('quotation_start_number', ':message') }}</span>
                                    </div>
                                </div>                                   
                            </div>
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('quotation_template') ? 'has-error' : '' }}">
                                    {!! Form::label('quotation_template', trans('settings.quotation_template'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::select('quotation_template', $quotation_template, old('quotation_template', (isset($orgSettings['quotation_template'])?$orgSettings['quotation_template']:"")), ['id'=>'quotation_template','class' => 'form-control select2']) !!}
                                        <span class="help-block">{{ $errors->first('quotation_template', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('sales_prefix') ? 'has-error' : '' }}">
                                    {!! Form::label('sales_prefix', trans('settings.sales_prefix'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('sales_prefix', old('sales_prefix', (isset($orgSettings['sales_prefix'])?$orgSettings['sales_prefix']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('sales_prefix', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('sales_start_number') ? 'has-error' : '' }}">
                                    {!! Form::label('sales_start_number', trans('settings.sales_start_number'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::input('number','sales_start_number', old('sales_start_number', (isset($orgSettings['sales_start_number'])?$orgSettings['sales_start_number']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('sales_start_number', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('saleorder_template') ? 'has-error' : '' }}">
                                    {!! Form::label('saleorder_template', trans('settings.saleorder_template'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::select('saleorder_template', $saleorder_template, old('saleorder_template', (isset($orgSettings['saleorder_template'])?$orgSettings['saleorder_template']:"")), ['id'=>'saleorder_template','class' => 'form-control select2']) !!}
                                        <span class="help-block">{{ $errors->first('saleorder_template', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                               <div class="form-group required {{ $errors->has('invoice_prefix') ? 'has-error' : '' }}">
                                    {!! Form::label('invoice_prefix', trans('settings.invoice_prefix'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('invoice_prefix', old('invoice_prefix', (isset($orgSettings['invoice_prefix'])?$orgSettings['invoice_prefix']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('invoice_prefix', ':message') }}</span>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-4">
                               <div class="form-group required {{ $errors->has('invoice_start_number') ? 'has-error' : '' }}">
                                    {!! Form::label('invoice_start_number', trans('settings.invoice_start_number'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::input('number','invoice_start_number', old('invoice_start_number', (isset($orgSettings['invoice_start_number'])?$orgSettings['invoice_start_number']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('invoice_start_number', ':message') }}</span>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('invoice_template') ? 'has-error' : '' }}">
                                    {!! Form::label('invoice_template', trans('settings.invoice_template'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::select('invoice_template', $invoice_template, old('invoice_template', (isset($orgSettings['invoice_template'])?$orgSettings['invoice_template']:"")), ['id'=>'invoice_template','class' => 'form-control select2']) !!}
                                        <span class="help-block">{{ $errors->first('invoice_template', ':message') }}</span>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('invoice_payment_prefix') ? 'has-error' : '' }}">
                                    {!! Form::label('invoice_payment_prefix', trans('settings.invoice_payment_prefix'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::text('invoice_payment_prefix', old('invoice_payment_prefix', (isset($orgSettings['invoice_payment_prefix'])?$orgSettings['invoice_payment_prefix']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('invoice_payment_prefix', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('invoice_payment_start_number') ? 'has-error' : '' }}">
                                    {!! Form::label('invoice_payment_start_number', trans('settings.invoice_payment_start_number'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::input('number','invoice_payment_start_number', old('invoice_payment_start_number', (isset($orgSettings['invoice_payment_start_number'])?$orgSettings['invoice_payment_start_number']:"")), ['class' => 'form-control']) !!}
                                        <span class="help-block">{{ $errors->first('invoice_payment_start_number', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group required {{ $errors->has('invoice_payment_template') ? 'has-error' : '' }}">
                                    {!! Form::label('invoice_payment_template', trans('settings.invoice_payment_template'), ['class' => 'control-label required']) !!}
                                    <div class="controls">
                                        {!! Form::select('invoice_payment_template', $invoice_payment_template, old('invoice_payment_template', (isset($orgSettings['invoice_payment_template'])?$orgSettings['invoice_payment_template']:"")), ['id'=>'invoice_payment_template','class' => 'form-control select2']) !!}
                                        <span class="help-block">{{ $errors->first('invoice_payment_template', ':message') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4"></div>
                            <div class="col-4"></div>
                            <div class="col-4">
                                {!! trans('settings.print_address') !!}
                                <div class="form-inline">
                                    <div class="radio">
                                        <div class="form-inline">
                                            {!! Form::radio('print_address', 'true',(isset($orgSettings['print_address'])&&$orgSettings['print_address']=='true')?true:false,['id'=>'print_address_true','class' => 'print_address icheck'])  !!}
                                            {!! Form::label('true', 'SI',['class'=>'ml-1 mr-2'])  !!}
                                        </div>
                                    </div>
                                    <div class="radio">
                                        <div class="form-inline">
                                            {!! Form::radio('print_address', 'false', (isset($orgSettings['print_address'])&&$orgSettings['print_address']=='false')?true:false,['id'=>'print_address_false','class' => 'print_address icheck'])  !!}
                                            {!! Form::label('false', 'NO',['class'=>'ml-1']) !!}
                                        </div>
                                    </div>
                                </div>
                                <span class="help-block">{{ $errors->first('print_address', ':message') }}</span>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
            </br>
            <!-- Form Actions -->
            <div class="d-flex align-items-center justify-content-md-end">
                <button type="submit" class="btn btn-primary">{{trans('table.ok')}}</button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
@stop

@section('vendor-script')
<script src="{{ asset('vendors/js/forms/select/select2.full.min.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function(){

            $("#country_id").select2({
                placeholder:"{{ trans('company.select_country') }}"
            });
            $("#state_id").select2({
                placeholder:"{{ trans('company.select_state') }}"
            });
            $("#city_id").select2({
                placeholder:"{{ trans('company.select_city') }}"
            });
            $("#fiscal_regimen").select2({
                placeholder:"Seleccione el Régimen Fiscal"
            });
            
        });

        

        // get contries/states/cities

        
        $('#country_id').change(function () {
            getstates($(this).val());
            
        });

        $('#site_logo_file').change(function () {
            const [file] = $(this).prop('files');
            if (file) {
                $('#logo_file').attr('src', URL.createObjectURL(file));
            }
        });
        
        function getstates(country) {
            

            $.ajax({
                type: "GET",
                url: "{{ url('lead/ajax_state_list')}}",
                data: {'id': country, _token: '{{ csrf_token() }}'},
                success: function (data) {

                    $('#state_id').empty();
                    $('#city_id').empty();
                    
                    
                    $('#city_id').select2({
                        placeholder: "{{trans('company.select_city')}}"
                    }).trigger('change');

                    $.each(data, function (val, text) {
                        $('#state_id').append($('<option></option>').val(val).html(text).attr('selected',false));
                    });
                    
                }
            });
        }

        $('#state_id').change(function () {
            // _state = $(this).val();
            getcities($(this).val());
        });

        function getcities(state) {
            $.ajax({
                type: "GET",
                url: "{{ url('lead/ajax_city_list')}}",
                data: {'id': state, _token: '{{ csrf_token() }}'},
                success: function (data) {
                    $('#city_id').empty();
                    $('#city_id').select2({
                        placeholder: "{{trans('company.select_city')}}"
                    }).trigger('change');
                    $.each(data, function (val, text) {
                        $('#city_id').append($('<option></option>').val(val).html(text).attr('selected',  false));
                    });
                }
            });
        }
    </script>
@stop
