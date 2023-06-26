<div class="card">
	<div class="card-body">
		@if (isset($organization)) {!! Form::model($organization, ['url' => $type . '/' . $organization->id, 'method' => 'put','files'=> true]) !!}
		@else {!! Form::open(['url' => $type, 'method' => 'post', 'files'=> true]) !!} @endif
		
		<h2>{{trans('organizations.organization_owner_detals')}}</h2>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('owner_first_name') ? 'has-error' : '' }}">
					{!! Form::label('owner_first_name', trans('organizations.owner_first_name'), ['class' => 'control-label required']) !!}
					<div class="controls">
						{!! Form::text('owner_first_name', null, ['class' => 'form-control']) !!}
						<span class="help-block">{{ $errors->first('owner_first_name', ':message') }}</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('owner_last_name') ? 'has-error' : '' }}">
					{!! Form::label('owner_last_name', trans('organizations.owner_last_name'), ['class' => 'control-label required']) !!}
					<div class="controls">
						{!! Form::text('owner_last_name', null, ['class' => 'form-control']) !!}
						<span class="help-block">{{ $errors->first('owner_last_name', ':message') }}</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('owner_phone_number') ? 'has-error' : '' }}">
					{!! Form::label('owner_phone_number', trans('organizations.owner_phone_number'), ['class' => 'control-label required'])
                    !!}
					<div class="controls">
						{!! Form::text('owner_phone_number', null, ['class' => 'form-control','data-fv-integer' => 'true']) !!}
						<span class="help-block">{{ $errors->first('owner_phone_number', ':message') }}</span>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('owner_email') ? 'has-error' : '' }}">
					{!! Form::label('owner_email', trans('organizations.owner_email'), ['class' => 'control-label required']) !!}
					<div class="controls">
						{!! Form::email('owner_email', null, ['class' => 'form-control']) !!}
						<span class="help-block">{{ $errors->first('owner_email', ':message') }}</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('owner_password') ? 'has-error' : '' }}">
					{!! Form::label('owner_password', trans('organizations.owner_password'), ['class' => 'control-label required']) !!}
					<div class="controls">
						{!! Form::password('owner_password', ['class' => 'form-control']) !!}
						<span class="help-block">{{ $errors->first('owner_password', ':message') }}</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('owner_password_confirmation') ? 'has-error' : '' }}">
					{!! Form::label('owner_password_confirmation', trans('organizations.owner_password_confirmation'), ['class' => 'control-label
                    required']) !!}
					<div class="controls">
						{!! Form::password('owner_password_confirmation', ['class' => 'form-control']) !!}
						<span class="help-block">{{ $errors->first('owner_password_confirmation', ':message') }}</span>
					</div>
				</div>
			</div>
		</div>
		
		
		<h2>{{trans('organizations.organization_details')}}</h2>
		<div class="row">
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('name') ? 'has-error' : '' }}">
					{!! Form::label('name', trans('organizations.name'), ['class' => 'control-label required']) !!}
					<div class="controls">
						{!! Form::text('name', null, ['class' => 'form-control']) !!}
						<span class="help-block">{{ $errors->first('name', ':message') }}</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('email') ? 'has-error' : '' }}">
					{!! Form::label('email', trans('organizations.email'), ['class' => 'control-label required']) !!}
					<div class="controls">
						{!! Form::email('email', null, ['class' => 'form-control']) !!}
						<span class="help-block">{{ $errors->first('email', ':message') }}</span>
					</div>
				</div>
			</div>
			<div class="col-md-4">
				<div class="form-group required {{ $errors->has('sat_rfc') ? 'has-error' : '' }}">
                    {!! Form::label('sat_rfc', trans('company.sat_rfc'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('sat_rfc', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('sat_rfc', ':message') }}</span>
                    </div>
                </div>				
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
            	<div class="form-group required {{ $errors->has('sat_name') ? 'has-error' : '' }}">
                    {!! Form::label('sat_name', trans('company.sat_name'), ['class' => 'control-label required']) !!}
                    <div class="controls">
                        {!! Form::text('sat_name', null, ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('sat_name', ':message') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
				<div class="form-group required {{ $errors->has('fiscal_regimen') ? 'has-error' : '' }}">
                    {!! Form::label('fiscal_regimen', 'Régimen Fiscal', ['class' => 'control-label  required']) !!}
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
							), isset($fiscal_regimen) ? $fiscal_regimen : '601', ['class' => 'form-control']) !!}
                        <span class="help-block">{{ $errors->first('fiscal_regimen', ':message') }}</span>
                    </div>
                </div>
			</div>
			<div class="col-md-4">
                <div class="form-group  {{ $errors->has('zip_code') ? 'has-error' : '' }}">
                    {!! Form::label('zip_code', trans('company.zip_code'), ['class' => 'control-label']) !!}
                    <div class="controls">
                        {!! Form::text('zip_code', null, ['class' => 'form-control','data-fv-integer' => "true"]) !!}
                        <span class="help-block">{{ $errors->first('zip_code', ':message') }}</span>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
        	<div class="col-md-12">
				<a class="btn  btn-default" role="button" data-toggle="collapse" href="#more_info" aria-expanded="false" aria-controls="more_info"><i class="fa fa-toggle-on"></i>Agregar más campos</a>
			</div>
		</div>
		<br>

		<!-- Collapsable section -->
		<div id="more_info" class="collapse">
			<div class="row">
				<div class="col-md-4"> 
	                <div class="form-group  {{ $errors->has('street') ? 'has-error' : '' }}">
	                    {!! Form::label('street', trans('company.street'), ['class' => 'control-label ']) !!}
	                    <div class="controls">
	                        {!! Form::text('street', null, ['class' => 'form-control']) !!}
	                        <span class="help-block">{{ $errors->first('street', ':message') }}</span>
	                    </div>
	                </div>
	            </div>
	            <div class="col-md-2">
	                <div class="form-group  {{ $errors->has('exterior_no') ? 'has-error' : '' }}">
	                    {!! Form::label('exterior_no', trans('company.exterior_no'), ['class' => 'control-label ']) !!}
	                    <div class="controls">
	                        {!! Form::text('exterior_no', null, ['class' => 'form-control','data-fv-integer' => "true"]) !!}
	                        <span class="help-block">{{ $errors->first('exterior_no', ':message') }}</span>
	                    </div>
	                </div>
	            </div>
	            <div class="col-md-2">
	                <div class="form-group  {{ $errors->has('interior_no') ? 'has-error' : '' }}">
	                    {!! Form::label('interior_no', trans('company.interior_no'), ['class' => 'control-label']) !!}
	                    <div class="controls">
	                        {!! Form::text('interior_no', null, ['class' => 'form-control','data-fv-integer' => "true"]) !!}
	                        <span class="help-block">{{ $errors->first('interior_no', ':message') }}</span>
	                    </div>
	                </div>
	            </div>
	            <div class="col-md-4">
	                <div class="form-group  {{ $errors->has('suburb') ? 'has-error' : '' }}">
	                    {!! Form::label('suburb', trans('company.suburb'), ['class' => 'control-label ']) !!}
	                    <div class="controls">
	                        {!! Form::text('suburb', null, ['class' => 'form-control']) !!}
	                        <span class="help-block">{{ $errors->first('suburb', ':message') }}</span>
	                    </div>
	                </div>
	            </div>
	        </div>
	        <div class="row">
	            <div class="col-3">
                    <div class="form-group  {{ $errors->has('country_id') ? 'has-error' : '' }}">
                        {!! Form::label('country_id', trans('company.country'), ['class' => 'control-label ']) !!}
                        <div class="controls">
                            {!! Form::select('country_id', $countries, isset($country_id)?$country_id:null, ['id'=>'country_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('country_id', ':message') }}</span>
                        </div>
                    </div>
                </div>                         
                <div class="col-lg-3">
                    <div class="form-group  {{ $errors->has('state_id') ? 'has-error' : '' }}">
                        {!! Form::label('state_id', trans('company.state'), ['class' => 'control-label ']) !!}
                        <div class="controls">
                            {!! Form::select('state_id', $states, isset($state_id)?$state_id:null, ['id'=>'state_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('state_id', ':message') }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="form-group  {{ $errors->has('city_id') ? 'has-error' : '' }}">
                        {!! Form::label('city_id', trans('company.city'), ['class' => 'control-label ']) !!}
                        <div class="controls">
                            {!! Form::select('city_id', $cities, isset($city_id)?$city_id:null, ['id'=>'city_id', 'class' => 'form-control select2']) !!}
                            <span class="help-block">{{ $errors->first('city_id', ':message') }}</span>
                        </div>
                    </div>
                </div>	            
	            <div class="col-md-3">
	                <div class="form-group {{ $errors->has('user_avatar_file') ? 'has-error' : '' }}">
						{!! Form::label('user_avatar_file', trans('organizations.organization_avatar'), ['class' => 'control-label']) !!}
						<div class="row">
							@if(isset($organization->logo))
							<span class="alert-success">{{$organization->logo}}<i class="fa fa-check-square-o"></i></span> 
								{!! Form::file('organization_avatar_file') !!}
							@else
								{!! Form::file('organization_avatar_file') !!}
							@endif
						</div>
						<span class="help-block">{{ $errors->first('organization_avatar_file', ':message') }}</span>
					</div>
				</div>
			</div>
		</div>	
		
		<h2>Archivos CSD de la organización</h2>
		<div class="row">
			<div class="col-md-4">
                <div class="form-group  {{ $errors->has('cer_file') ? 'has-error' : '' }}">
					{!! Form::label('cer_file', 'Subir archivo .cer', ['class' => 'control-label']) !!}
						@if(isset($organization->cer_file))
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
						@if(isset($organization->key_file))
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
                        {!! Form::text('fiel_pwd', null, ['class' => 'form-control','data-fv-integer' => "true"]) !!}
                        <span class="help-block">{{ $errors->first('fiel_pwd', ':message') }}</span>
                    </div>
                </div>
            </div>
		</div>

		@if(!isset($organization))
			<div class="row">
				<div class="col-12">
					<h2>{{trans('payplan.payplans')}}</h2>
					<div class="form-group required {{ $errors->has('plan_id') ? 'has-error' : '' }}">
						<span class="help-block">{{ $errors->first('plan_id', ':message') }}</span>
					</div>
				</div>
				@foreach($payment_plans_list as $item)
					<div class="col-md-4 col-sm-6 col-12">
						<div class="pay_plan">
							<div class="card">
								@if(collect($payment_plans_list)->max('organizations') == $item->organizations && $item->organizations > 0)
									<div class="badges badge_left">
										<div class="badge_content badge_purple bg-purple">Trending</div>
									</div>
								@endif
								<div class="card-header bg-primary text-center text-white">
									<input type="hidden" class="plan_id" value="{{$item->id}}">
									<h4>{{ $item->name }}</h4>
								</div>
								<div class="card-body text-center">
									<div class="m-t-10">
                                    <span class="font_28">
                                        @if($item->currency==="MXN")
											<sup>MXN</sup>
										@else
											<sup>&euro;</sup>
										@endif
										{{ ($item->amount)}}
                                    </span>
										<span class="font_18"> / </span>
										<span class="text_light">
											{{ ($item->interval_count==1?$item->interval_count.' '.$item->interval:$item->interval_count.' '.$item->interval.'s') }}
										</span>
									</div>
									<div class="m-t-20 text-primary">
										<h4>{{ trans('payplan.user_access') }}</h4>
									</div>
									<div class="m-t-10 text_light">
										{{ ($item->no_people!==0?$item->no_people : trans('payplan.unlimited'))}} {{ trans('payplan.members') }}
									</div>
									<div class="m-t-20 text-primary">
										<h4>{{ trans('payplan.trials') }}</h4>
									</div>
									<div class="m-t-10 text_light">
										{{ isset($item->trial_period_days)?$item->trial_period_days .' '.trans('payplan.days_free_trial'): trans('payplan.none') }}
									</div>
									<div class="m-t-20 text-primary">
										<h4>{{ trans('payplan.description') }}</h4>
									</div>
									<div class="m-t-10 text_light">
										{{ isset($item->statement_descriptor) ? $item->statement_descriptor : trans('payplan.none') }}
									</div>
								</div>
							</div>
						</div>
					</div>
				@endforeach
			</div>
			<div class="row">
				<input type="hidden" value="" name="plan_id" class="payplan_id">
				<div class="col-md-6 plan_duration">
					<div class="form-group required {{ $errors->has('duration') ? 'has-error' : '' }}">
						{!! Form::label('duration', trans('organizations.duration'), ['class' => 'control-label required'])
                        !!}
						<div class="controls">
							{!! Form::number('duration', null, ['class' => 'form-control','data-fv-integer' => 'true', 'min'=>1]) !!}
							<span class="help-block">{{ $errors->first('duration', ':message') }}</span>
						</div>
					</div>
				</div>
			</div>
			@endif
		<div class="row">
			<div class="col-md-12">
				<!-- Form Actions -->
				<div class="form-group">
					<div class="controls">
						<button type="submit" class="btn btn-success"><i class="fa fa-check-square-o"></i> {{trans('table.ok')}}</button>
						<a href="{{ route($type.'.index') }}" class="btn btn-warning"><i class="fa fa-arrow-left"></i> {{trans('table.back')}}</a>
					</div>
				</div>
				<!-- ./ form actions -->
			</div>
		</div>
		{!! Form::close() !!}
	</div>
</div>
@section('scripts')
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{config('services.gmaps_key')}}&libraries=places"></script>
<script>
	$(document).ready(function(){

		$("#country_id").select2({
            theme:"bootstrap",
            placeholder:"{{ trans('company.select_country') }}"
        });
        $("#state_id").select2({
            theme:"bootstrap",
            placeholder:"{{ trans('company.select_state') }}"
        });
        $("#city_id").select2({
            theme:"bootstrap",
            placeholder:"{{ trans('company.select_city') }}"
        });

		$("#fiscal_regimen").select2({
            theme:"bootstrap",
            placeholder:"Seleccione el Régimen Fiscal"
        });

		$(".plan_duration").hide();
	    $(".pay_plan").on("click",function(){
	        $(".pay_plan").removeClass("active");
	       $(this).addClass('active');
            $(".plan_duration").show();
	       $(".payplan_id").val($(this).find(".plan_id").val())
		});
	});

	var _country = null;
    var _state = null;
    var _city = null;

    

    // get contries/states/cities

    $("#state_id").find("option:contains({{trans('company.select_state')}})").attr({
        selected: true,
        value: ""
    });
    $("#city_id").find("option:contains('{{trans('company.select_city')}}')").attr({
        selected: true,
        value: ""
    });
    $('#country_id').change(function () {
        getstates($(this).val());
        
    });

	if(_country){
        getstates(_country);
    }
    
    function getstates(country) {
        $.ajax({
            type: "GET",
            url: '{{ url('organizations/ajax_state_list')}}',
            data: {'id': country, _token: '{{ csrf_token() }}'},
            success: function (data) {

            	$('#state_id').empty();
                $('#city_id').empty();
                
                if(_state){
                    getcities(_state);

                }                    
                $('#city_id').select2({
                    theme: "bootstrap",
                    placeholder: "{{trans('company.select_city')}}"
                }).trigger('change');

                $.each(data, function (val, text) {
                    $('#state_id').append($('<option></option>').val(val).html(text).attr('selected', val == _state ? true : false));
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
            url: '{{ url('organizations/ajax_city_list')}}',
            data: {'id': state, _token: '{{ csrf_token() }}'},
            success: function (data) {
                $('#city_id').empty();
                $('#city_id').select2({
                    theme: "bootstrap",
                    placeholder: "{{trans('company.select_city')}}"
                }).trigger('change');
                $.each(data, function (val, text) {
                    $('#city_id').append($('<option></option>').val(val).html(text).attr('selected', val == _city ? true : false));
                });
            }
        });
    }

    $('#city_id').change(function () {
        var geocoder = new google.maps.Geocoder();
        if (typeof $('#city_id').select2('data')[0] !== "undefined" && typeof $('#state_id').select2('data')[0] !== "undefined") {
            geocoder.geocode({'address': '"' + $('#city_id').select2('data')[0].text + '",' + $('#state_id').select2('data')[0].text + '"'}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    $('#latitude').val(results[0].geometry.location.lat());
                    $('#longitude').val(results[0].geometry.location.lng());
                }
            });
        }
    });

</script>
	@endsection