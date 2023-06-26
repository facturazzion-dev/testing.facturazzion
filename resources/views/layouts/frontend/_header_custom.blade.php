<section id="nav_custom">
    <div class="container">
        <div class="row ">
            <div class="col-12  header_mtop" id="navicon_">
                <div class="navbar navbar-expand-md navbar-light">
                    <a href="{{ url('/') }}" class="logo navbar-brand text-center" style="margin-left: auto; margin-right:auto;">
                        @if(isset($settings['site_logo']))
                        <img src="/img/logo.png" alt="{{ $settings['site_name'] }}" class="img-responsive site_logo m_auto">
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>