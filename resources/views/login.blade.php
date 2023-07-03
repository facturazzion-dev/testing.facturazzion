@php
$configData = Helper::applClasses();
@endphp
@extends('layouts/fullLayoutMaster')

@section('title', 'Login')

@section('page-style')
  {{-- Page Css files --}}
  <link rel="stylesheet" href="{{ asset(mix('css/base/plugins/forms/form-validation.css')) }}">
  <link rel="stylesheet" href="{{ asset(mix('css/base/pages/authentication.css')) }}">
@endsection

@section('content')
<div class="auth-wrapper auth-cover bg-warning">
  <div class="auth-inner row m-0">
    <!-- Brand logo-->
    <a class="brand-logo" href="#">
    <svg width="352" height="27" viewBox="0 0 352 27" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M17.6774 5.03226L16.2258 0H0V27H8.58064V16.0968H15.4839V11.4194H8.58064V5.03226H17.6774Z" fill="#7367F0"/>
      <path d="M32.5804 0L25.032 27H33.4191L34.161 23.6452H39.8707L40.6126 27H48.9997L41.4513 0H32.5804ZM35.0965 19.3548L37.0642 9.77419L38.9675 19.3548H35.0965Z" fill="#7367F0"/>
      <path d="M68.8712 5.03226H76.8712L75.4196 0H64.4196C63.0325 0 61.8712 0.290323 60.9357 0.870968C60.0002 1.45161 59.5164 2.25806 59.5164 3.32258V22.8387C59.5164 24.3226 60.0002 25.3871 60.968 26.0323C61.9357 26.6774 63.6131 27 66.0325 27H75.3551L76.8389 21.9677H68.8712C68.3873 21.9677 68.1293 21.8065 68.1293 21.4516V5.54839C68.1293 5.19355 68.3873 5.03226 68.8712 5.03226Z" fill="#7367F0"/>
      <path d="M89.4191 0L87.2256 5.03226H92.8062V27H101.322V5.03226H106.903L104.838 0H89.4191Z" fill="#7367F0"/>
      <path d="M130.903 21.6774C130.903 21.871 130.839 22.0323 130.774 22.129C130.677 22.2258 130.452 22.2903 130.097 22.2903H127.161C126.806 22.2903 126.581 22.2258 126.484 22.129C126.387 22.0323 126.355 21.871 126.355 21.6774V0H117.839V22.8387C117.839 24.3226 118.322 25.3871 119.29 26.0323C120.258 26.6774 121.935 27 124.355 27H132.935C135.355 27 137.032 26.6774 138 26.0323C138.968 25.3871 139.452 24.3226 139.452 22.8387V0H130.935V21.6774H130.903Z" fill="#7367F0"/>
      <path d="M172.033 17.6452C172.678 17.1613 172.968 16.6129 172.968 16V3.48387C172.968 2.41935 172.516 1.58065 171.613 0.935484C170.71 0.290323 169.291 0 167.323 0H151.484V27H159.839V18.3871H160.936L164.968 27H173.613L169.226 18.3871C170.452 18.3871 171.387 18.129 172.033 17.6452ZM164.742 13.4839C164.742 13.871 164.484 14.0323 163.936 14.0323H159.839V4.70968H163.936C164.484 4.70968 164.742 4.90323 164.742 5.25806V13.4839Z" fill="#7367F0"/>
      <path d="M191.161 0L183.613 27H192L192.742 23.6452H198.452L199.193 27H207.581L200.032 0H191.161ZM193.677 19.3548L195.645 9.77419L197.548 19.3548H193.677Z" fill="#7367F0"/>
      <path d="M217.516 0L216.065 5.03226H224.774L222.516 10.2581L217.968 14.8065H220.581L215.387 27H233L234.419 21.9677H226.613L228.871 16.4194L233.613 11.6452H230.807L235.581 0H217.516Z" fill="#7367F0"/>
      <path d="M247.258 0L245.807 5.03226H254.516L252.258 10.3226L247.742 14.8065H250.323L245.129 27H262.742L264.161 21.9677H256.355L258.613 16.4516L263.387 11.6452H260.549L265.323 0H247.258Z" fill="#7367F0"/>
      <path d="M284.613 0H276.258V27H284.613V0Z" fill="#7367F0"/>
      <path d="M316.968 0.935484C316.065 0.290323 314.645 0 312.678 0H302.323C300.355 0 298.936 0.322581 298.032 0.935484C297.129 1.58065 296.677 2.41935 296.677 3.48387V23.129C296.677 23.6452 296.774 24.129 296.968 24.6129C297.161 25.0645 297.484 25.4839 297.936 25.8387C298.387 26.1935 299 26.4516 299.774 26.6774C300.548 26.9032 301.516 27 302.645 27H312.323C313.484 27 314.419 26.9032 315.194 26.6774C315.968 26.4516 316.581 26.1935 317.032 25.8387C317.484 25.4839 317.807 25.0968 318 24.6129C318.194 24.1613 318.29 23.6774 318.29 23.129V3.48387C318.323 2.41935 317.871 1.58065 316.968 0.935484ZM309.871 21.7097C309.871 21.9032 309.807 22.0645 309.742 22.129C309.645 22.2258 309.419 22.2581 309.065 22.2581H305.903C305.548 22.2581 305.323 22.2258 305.226 22.129C305.129 22.0323 305.097 21.9032 305.097 21.7097V5.25806C305.097 4.90323 305.355 4.74194 305.839 4.74194H309.129C309.613 4.74194 309.871 4.90323 309.871 5.25806V21.7097Z" fill="#7367F0"/>
      <path d="M342.613 0V10.8387L338.355 0H330.387V27H338.097V15.7097L342.613 27H351.161V0H342.613Z" fill="#7367F0"/>
    </svg>
    </a>
    <!-- /Brand logo-->

    <!-- Left Text-->
    <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
      <div class="w-100 d-lg-flex align-items-center justify-content-center px-5">
          <img class="img-fluid" src="{{asset('images/pages/login/devices.png')}}" alt="Login V2" />
          <h1 class="text-white"><strong style="font-size: 65px" >AMBIENTE </strong> DE ENSAYO (cesar)  <strong>. . .</strong> </h1>
      </div>
    </div>
    <!-- /Left Text-->

    <!-- Login-->
    <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
      <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
        <h2 class="card-title fw-bold mb-1">¡Bienvenido! 👋🏻</h2>
        <p class="card-text mb-2">Por favor introduce tu Correo Electrónico y Contraseña</p>
        <form class="auth-login-form mt-2" action="/signin" method="POST">
          @csrf
          <div class="mb-1">
            <label class="form-label" for="email">Correo Electrónico</label>
            <input class="form-control" id="email" type="text" name="email" placeholder="juan@gmail.com" aria-describedby="email" autofocus="" tabindex="1" />
          </div>
          <div class="mb-1">
            <div class="d-flex justify-content-between">
              <label class="form-label" for="password">Contraseña</label>
              <a href="{{url("auth/forgot-password-cover")}}">
                <small>¿Olvidaste tu Contraseña?</small>
              </a>
            </div>
            <div class="input-group input-group-merge form-password-toggle">
              <input class="form-control form-control-merge" id="password" type="password" name="password" placeholder="············" aria-describedby="password" tabindex="2" />
              <span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
            </div>
          </div>
          <div class="mb-1">
            <div class="form-check">
              <input class="form-check-input" id="remember" type="checkbox" tabindex="3" />
              <label class="form-check-label" for="remember"> Recordar datos</label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary w-100" tabindex="4">Ingresar</button>
        </form>
        <p class="text-center mt-2">
          <span>¿Quieres conocer nuestro sistema?</span>
          <a href="{{url('auth/register-cover')}}"><span>&nbsp;Crear una Cuenta</span></a>
        </p>
        <div class="divider my-2">
          <div class="divider-text">+</div>
        </div>
        <p class="text-center mt-2">
          <span>¿Necesitas Ayuda? Mandanos un WhatsApp,</span>
          <span>Llámanos ó mandanos un correo a z@facturazzion.com</span>
        </p>
        <a href="https://api.whatsapp.com/send?phone=5216641282251&text=Hola!" class="btn btn-success w-100" tabindex="4">Whatsapp: 664 128 2251</a>
        
      </div>
    </div>
    <!-- /Login-->
  </div>
</div>
@endsection

@section('vendor-script')
<script src="{{asset(mix('vendors/js/forms/validation/jquery.validate.min.js'))}}"></script>
@endsection

@section('page-script')
<script src="{{asset(mix('js/scripts/pages/auth-login.js'))}}"></script>
@endsection
