@php
$configData = Helper::applClasses();
@endphp
<div
  class="main-menu menu-fixed {{ $configData['theme'] === 'dark' || $configData['theme'] === 'semi-dark' ? 'menu-dark' : 'menu-light' }} menu-accordion menu-shadow"
  data-scroll-to-active="true">
  <div class="navbar-header">
    <ul class="nav navbar-nav flex-row">
      <li class="nav-item me-auto">
        <a class="navbar-brand" href="{{ url('/') }}">
          <svg width="180" height="15" viewBox="0 0 188 15" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M9.44135 2.7957L8.66606 0H0V15H4.58284V8.94265H8.26979V6.34409H4.58284V2.7957H9.44135Z" fill="#7367F0"/>
            <path d="M17.4009 0L13.3694 15H17.8489L18.2451 13.1362H21.2946L21.6909 15H26.1703L22.1388 0H17.4009ZM18.7448 10.7527L19.7957 5.43011L20.8122 10.7527H18.7448Z" fill="#7367F0"/>
            <path d="M36.7834 2.7957H41.0562L40.2809 0H34.4059C33.665 0 33.0448 0.16129 32.5452 0.483871C32.0455 0.806452 31.7871 1.25448 31.7871 1.84588V12.6882C31.7871 13.5125 32.0455 14.1039 32.5624 14.4624C33.0793 14.8208 33.9752 15 35.2673 15H40.2464L41.0389 12.2043H36.7834C36.525 12.2043 36.3872 12.1147 36.3872 11.9176V3.08244C36.3872 2.8853 36.525 2.7957 36.7834 2.7957Z" fill="#7367F0"/>
            <path d="M47.758 0L46.5864 2.7957H49.567V15H54.1154V2.7957H57.096L55.9933 0H47.758Z" fill="#7367F0"/>
            <path d="M69.9142 12.043C69.9142 12.1505 69.8797 12.2401 69.8452 12.2939C69.7936 12.3477 69.673 12.3835 69.4834 12.3835H67.9156C67.7261 12.3835 67.6055 12.3477 67.5538 12.2939C67.5021 12.2401 67.4849 12.1505 67.4849 12.043V0H62.9365V12.6882C62.9365 13.5125 63.195 14.1039 63.7118 14.4624C64.2287 14.8208 65.1246 15 66.4167 15H70.9996C72.2917 15 73.1876 14.8208 73.7045 14.4624C74.2213 14.1039 74.4798 13.5125 74.4798 12.6882V0H69.9314V12.043H69.9142Z" fill="#7367F0"/>
            <path d="M91.8811 9.80287C92.2257 9.53405 92.3807 9.22939 92.3807 8.88889V1.93548C92.3807 1.34409 92.1395 0.878137 91.6571 0.519714C91.1747 0.161291 90.4166 0 89.3657 0H80.9064V15H85.3686V10.2151H85.9544L88.108 15H92.7253L90.3822 10.2151C91.0369 10.2151 91.5365 10.0717 91.8811 9.80287ZM87.9874 7.49104C87.9874 7.70609 87.8496 7.7957 87.5567 7.7957H85.3686V2.61649H87.5567C87.8496 2.61649 87.9874 2.72401 87.9874 2.92115V7.49104Z" fill="#7367F0"/>
            <path d="M102.097 0L98.0659 15H102.545L102.942 13.1362H105.991L106.387 15H110.867L106.835 0H102.097ZM103.441 10.7527L104.492 5.43011L105.509 10.7527H103.441Z" fill="#7367F0"/>
            <path d="M116.173 0L115.398 2.7957H120.05L118.844 5.69893L116.415 8.22581H117.81L115.036 15H124.443L125.201 12.2043H121.032L122.238 9.12186L124.771 6.46953H123.272L125.822 0H116.173Z" fill="#7367F0"/>
            <path d="M132.058 0L131.283 2.7957H135.935L134.729 5.73477L132.317 8.22581H133.695L130.921 15H140.328L141.086 12.2043H136.917L138.123 9.13979L140.673 6.46953H139.157L141.706 0H132.058Z" fill="#7367F0"/>
            <path d="M152.009 0H147.547V15H152.009V0Z" fill="#7367F0"/>
            <path d="M169.29 0.519714C168.807 0.161291 168.049 0 166.998 0H161.468C160.417 0 159.659 0.179212 159.176 0.519714C158.694 0.878137 158.453 1.34409 158.453 1.93548V12.8495C158.453 13.1362 158.504 13.405 158.608 13.6738C158.711 13.9247 158.883 14.1577 159.125 14.3548C159.366 14.552 159.693 14.6953 160.107 14.8208C160.52 14.9462 161.037 15 161.64 15H166.809C167.429 15 167.929 14.9462 168.342 14.8208C168.756 14.6953 169.083 14.552 169.324 14.3548C169.565 14.1577 169.738 13.9427 169.841 13.6738C169.944 13.4229 169.996 13.1541 169.996 12.8495V1.93548C170.013 1.34409 169.772 0.878137 169.29 0.519714ZM165.499 12.0609C165.499 12.1685 165.465 12.2581 165.43 12.2939C165.379 12.3477 165.258 12.3656 165.069 12.3656H163.38C163.191 12.3656 163.07 12.3477 163.018 12.2939C162.967 12.2401 162.949 12.1685 162.949 12.0609V2.92115C162.949 2.72401 163.087 2.63441 163.346 2.63441H165.103C165.361 2.63441 165.499 2.72401 165.499 2.92115V12.0609Z" fill="#7367F0"/>
            <path d="M182.986 0V6.02151L180.712 0H176.457V15H180.574V8.7276L182.986 15H187.552V0H182.986Z" fill="#7367F0"/>
          </svg>
        </a>
      </li>
      <li class="nav-item nav-toggle">
        <a class="nav-link modern-nav-toggle pe-0" data-toggle="collapse">
          <i class="d-block d-xl-none text-primary toggle-icon font-medium-4" data-feather="x"></i>
          <i class="d-none d-xl-block collapse-toggle-icon font-medium-4 text-primary" data-feather="disc"
            data-ticon="disc"></i>
        </a>
      </li>
    </ul>
  </div>
  <div class="shadow-bottom"></div>
  <div class="main-menu-content">
    <ul class="navigation navigation-main" id="main-menu-navigation" data-menu="menu-navigation">
      {{-- Foreach menu item starts --}}
      @if (isset($menuData[0]))
        @foreach ($menuData[0]->menu as $menu)
          @if (isset($menu->navheader))
            <li class="navigation-header">
              <span>{{ __('locale.' . $menu->navheader) }}</span>
              <i data-feather="more-horizontal"></i>
            </li>
          @else
            {{-- Add Custom Class with nav-item --}}
            @php
              $custom_classes = '';
              if (isset($menu->classlist)) {
                  $custom_classes = $menu->classlist;
              }
            @endphp
            <li
              class="nav-item {{ $custom_classes }} {{ Route::currentRouteName() === $menu->slug ? 'active' : '' }}">
              <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0)' }}" class="d-flex align-items-center"
                target="{{ isset($menu->newTab) ? '_blank' : '_self' }}">
                <i data-feather="{{ $menu->icon }}"></i>
                <span class="menu-title text-truncate">{{ __('locale.' . $menu->name) }}</span>
                @if (isset($menu->badge))
                  <?php $badgeClasses = 'badge rounded-pill badge-light-primary ms-auto me-1'; ?>
                  <span
                    class="{{ isset($menu->badgeClass) ? $menu->badgeClass : $badgeClasses }}">{{ $menu->badge }}</span>
                @endif
              </a>
              @if (isset($menu->submenu))
                @include('panels/submenu', ['menu' => $menu->submenu])
              @endif
            </li>
          @endif
        @endforeach
      @endif
      {{-- Foreach menu item ends --}}
    </ul>
  </div>
</div>
<!-- END: Main Menu-->
