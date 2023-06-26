@extends('layouts/contentLayoutMaster')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="row">
                @foreach($payment_plans_list as $item)
                    @if($item->is_visible==1)
                    <!-- basic plan -->
                    <div class="col-12 col-md-4">
                    <div class="card basic-pricing text-center">
                        <div class="card-body">
                        <h3>{{ $item->name }}</h3>
                        <p class="card-text">Renueva tu suscripción</p>
                        <div class="annual-plan">
                            <div class="plan-price mt-2">
                            <sup class="font-medium-1 fw-bold text-primary">$</sup>
                            <span class="pricing-basic-value fw-bolder text-primary" style="font-size: 3.5rem; line-height: .8;">{{ ($item->amount)}}</span>
                            <sup class="font-medium-1 fw-bold text-primary">MXN</sup>
                            <sub class="pricing-duration text-body font-medium-1 fw-bold">/anual</sub>
                            </div>
                            <small class="annual-pricing d-none text-muted"></small>
                        </div>
                        <ul class="list-group list-group-circle text-start">
                            <li class="list-group-item">Comunícate al <strong>6641282251</strong> o al correo <strong>soporte@facturazzion.com</strong> y consígue un descuento</li>
                        </ul>
                        <a href="https://api.whatsapp.com/send?phone=5216641282251&text=Hola!" class="btn w-100 btn-outline-success mt-2" tabindex="4">Contactar ahora                            
                        </a>
                        </div>
                    </div>
                    </div>
                    <!--/ basic plan -->    
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection