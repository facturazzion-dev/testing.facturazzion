@if ($configData['mainLayoutType'] === 'horizontal' && isset($configData['mainLayoutType']))
  <nav
    class="header-navbar navbar-expand-lg navbar navbar-fixed align-items-center navbar-shadow navbar-brand-center {{ $configData['navbarColor'] }}"
    data-nav="brand-center">
    <div class="navbar-header d-xl-block d-none">
      <ul class="nav navbar-nav">
        <li class="nav-item">
          <a class="navbar-brand" href="{{ url('/') }}">
            <span class="brand-logo">
              <svg viewbox="0 0 139 95" version="1.1" xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink" height="24">
                <defs>
                  <lineargradient id="linearGradient-1" x1="100%" y1="10.5120544%" x2="50%" y2="89.4879456%">
                    <stop stop-color="#000000" offset="0%"></stop>
                    <stop stop-color="#FFFFFF" offset="100%"></stop>
                  </lineargradient>
                  <lineargradient id="linearGradient-2" x1="64.0437835%" y1="46.3276743%" x2="37.373316%" y2="100%">
                    <stop stop-color="#EEEEEE" stop-opacity="0" offset="0%"></stop>
                    <stop stop-color="#FFFFFF" offset="100%"></stop>
                  </lineargradient>
                </defs>
                <g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                  <g id="Artboard" transform="translate(-400.000000, -178.000000)">
                    <g id="Group" transform="translate(400.000000, 178.000000)">
                      <path class="text-primary" id="Path"
                        d="M-5.68434189e-14,2.84217094e-14 L39.1816085,2.84217094e-14 L69.3453773,32.2519224 L101.428699,2.84217094e-14 L138.784583,2.84217094e-14 L138.784199,29.8015838 C137.958931,37.3510206 135.784352,42.5567762 132.260463,45.4188507 C128.736573,48.2809251 112.33867,64.5239941 83.0667527,94.1480575 L56.2750821,94.1480575 L6.71554594,44.4188507 C2.46876683,39.9813776 0.345377275,35.1089553 0.345377275,29.8015838 C0.345377275,24.4942122 0.230251516,14.560351 -5.68434189e-14,2.84217094e-14 Z"
                        style="fill:currentColor"></path>
                      <path id="Path1"
                        d="M69.3453773,32.2519224 L101.428699,1.42108547e-14 L138.784583,1.42108547e-14 L138.784199,29.8015838 C137.958931,37.3510206 135.784352,42.5567762 132.260463,45.4188507 C128.736573,48.2809251 112.33867,64.5239941 83.0667527,94.1480575 L56.2750821,94.1480575 L32.8435758,70.5039241 L69.3453773,32.2519224 Z"
                        fill="url(#linearGradient-1)" opacity="0.2"></path>
                      <polygon id="Path-2" fill="#000000" opacity="0.049999997"
                        points="69.3922914 32.4202615 32.8435758 70.5039241 54.0490008 16.1851325"></polygon>
                      <polygon id="Path-21" fill="#000000" opacity="0.099999994"
                        points="69.3922914 32.4202615 32.8435758 70.5039241 58.3683556 20.7402338"></polygon>
                      <polygon id="Path-3" fill="url(#linearGradient-2)" opacity="0.099999994"
                        points="101.428699 0 83.0667527 94.1480575 130.378721 47.0740288"></polygon>
                    </g>
                  </g>
                </g>
              </svg>
            </span>
            <h2 class="brand-text mb-0">Facturazzion</h2>
          </a>
        </li>
      </ul>
    </div>
  @else
    <nav
      class="header-navbar navbar navbar-expand-lg align-items-center {{ $configData['navbarClass'] }} navbar-light navbar-shadow {{ $configData['navbarColor'] }} {{ $configData['layoutWidth'] === 'boxed' && $configData['verticalMenuNavbarType'] === 'navbar-floating' ? 'container-xxl' : '' }}">
@endif
<div class="navbar-container d-flex content">
  <div class="bookmark-wrapper d-flex align-items-center">
    <ul class="nav navbar-nav d-xl-none">
      <li class="nav-item"><a class="nav-link menu-toggle" href="javascript:void(0);"><i class="ficon"
            data-feather="menu"></i></a></li>
    </ul>
    <ul class="nav navbar-nav">
      <!-- <li class="nav-item d-none d-lg-block">
        <a class="nav-link nav-link-style">
          <i class="ficon" data-feather="{{ $configData['theme'] === 'dark' ? 'sun' : 'moon' }}"></i>
        </a>
      </li> -->
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
          <i class="ficon" data-feather="zap"></i>
        </a>
        <div class="dropdown-menu">
          <a class="dropdown-item" href="{{ url('invoice/create') }}">
            <i class="ficon" data-feather="plus-circle"></i> Factura
          </a>
          <a class="dropdown-item" href="{{ url('invoices_payment_log/create') }}">
            <i class="ficon" data-feather="plus-circle"></i> Pago REP
          </a>
          <a class="dropdown-item" href="{{ url('sales_order/create') }}">
            <i class="ficon" data-feather="plus-circle"></i> Nota de Venta
          </a>
          <a class="dropdown-item" href="{{ url('quotation/create') }}">
            <i class="ficon" data-feather="plus-circle"></i> Cotización
          </a>
          <a class="dropdown-item" href="{{ url('company/create') }}">
            <i class="ficon" data-feather="plus-circle"></i> Cliente
          </a>
          <a class="dropdown-item" href="{{ url('product/create') }}">
            <i class="ficon" data-feather="plus-circle"></i> Producto
          </a>
        </div>
      </li>
    </ul>
  </div>
  <ul class="nav navbar-nav align-items-center ms-auto">
    <li class="nav-item">
      <a href="https://api.whatsapp.com/send?phone=5216641282251&text=Hola!" class="btn-flat nav-link w-100" tabindex="4">+52 664 128 2251
        <svg viewBox="0 0 32 32" style="height:auto; width: 25px;">
          <path d=" M19.11 17.205c-.372 0-1.088 1.39-1.518 1.39a.63.63 0 0 1-.315-.1c-.802-.402-1.504-.817-2.163-1.447-.545-.516-1.146-1.29-1.46-1.963a.426.426 0 0 1-.073-.215c0-.33.99-.945.99-1.49 0-.143-.73-2.09-.832-2.335-.143-.372-.214-.487-.6-.487-.187 0-.36-.043-.53-.043-.302 0-.53.115-.746.315-.688.645-1.032 1.318-1.06 2.264v.114c-.015.99.472 1.977 1.017 2.78 1.23 1.82 2.506 3.41 4.554 4.34.616.287 2.035.888 2.722.888.817 0 2.15-.515 2.478-1.318.13-.33.244-.73.244-1.088 0-.058 0-.144-.03-.215-.1-.172-2.434-1.39-2.678-1.39zm-2.908 7.593c-1.747 0-3.48-.53-4.942-1.49L7.793 24.41l1.132-3.337a8.955 8.955 0 0 1-1.72-5.272c0-4.955 4.04-8.995 8.997-8.995S25.2 10.845 25.2 15.8c0 4.958-4.04 8.998-8.998 8.998zm0-19.798c-5.96 0-10.8 4.842-10.8 10.8 0 1.964.53 3.898 1.546 5.574L5 27.176l5.974-1.92a10.807 10.807 0 0 0 16.03-9.455c0-5.958-4.842-10.8-10.802-10.8z" fill="#6e6b7b"></path></svg>
      </a>
    </li>
    <li class="nav-item dropdown dropdown-user">
      <a class="nav-link dropdown-toggle dropdown-user-link" id="dropdown-user" href="javascript:void(0);"
        data-bs-toggle="dropdown" aria-haspopup="true">
        <div class="user-nav d-sm-flex d-none">
          <span class="user-name fw-bolder">
            {{ config('settings.sat_name') }}
          </span>
          <span class="user-status">
            {{ config('settings.sat_rfc') }}
          </span>
        </div>
        <span class="avatar">
          @if (Sentinel::getUser()->user_avatar)
          <img class="round"
            src="{{ url('/').'/uploads/avatar/thumb_'.Sentinel::getUser()->user_avatar }}"
            alt="avatar" height="40" width="40">
          @else
          <div class="avatar-content">
            @php
            $result = preg_replace('/(?<=\w)./', '', Sentinel::getUser()->full_name);
            echo strtoupper($result[0][0].$result[1][0]);
            @endphp
          </div>
          @endif
        </span>
      </a>
      <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-user">
        <a class="dropdown-item" href="/setting">
          <i class="me-50" data-feather="settings"></i> Ajustes
        </a>
        <a class="dropdown-item" href="/logout">
          <i class="me-50" data-feather="power"></i> Cerrar sesión
        </a>
      </div>
    </li>
  </ul>
</div>
</nav>
<!-- END: Header-->
