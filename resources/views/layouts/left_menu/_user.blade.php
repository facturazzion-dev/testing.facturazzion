<!-- <div class="nav_profile">
    <div class="media profile-left">
        <a class="pull-left profile-thumb" href="#">
            @if($user->user_avatar)
                <img src="{!! url('/').'/uploads/avatar/thumb_'.$user->user_avatar !!}" alt="img"
                     class="img-rounded"/>
            @else
                <img src="{{ url('uploads/avatar/user.png') }}" alt="img" class="img-rounded"/>
            @endif
        </a>
        <div class="content-profile">
            <h4 class="media-heading text-capitalize user_name_max">{{ $user->full_name }}</h4>
            <ul class="icon-list">
                <li>
                    <a href="{{ url('mailbox') }}#/m/inbox" title="Email">
                        <i class="fa fa-fw fa-envelope"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ url('sales_order') }}" title="Sales Order">
                        <i class="fa fa-fw fa-usd"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ url('invoice') }}" title="Invoices">
                        <i class="fa fa-fw fa-file-text"></i>
                    </a>
                </li>
                @if($orgRole=='admin')
                    <li>
                        <a href="{{ url('setting') }}" title="Settings">
                            <i class="fa fa-fw fa-cog"></i>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div> -->
<div id="menu" role="navigation">
    <ul class="navigation">
        <li {!! (Request::is('dashboard') ? 'class="active"' : '') !!}>
            <a href="{{url('dashboard')}}">
                <span class="nav-icon">
                    <i class="material-icons ">dashboard</i>
                </span>
                <span class="nav-text"> {{trans('left_menu.dashboard')}}</span>
            </a>
        </li>   
        @if(isset($user) && ($user->hasAccess(['invoices.read']) || isset($orgRole) && $orgRole=='admin'))
            <li {!! (Request::is('invoice/create') ? 'class="active"' : '') !!}>
                <a href="{{url('invoice/create')}}">
                    <i class="material-icons ">add_circle</i>
                    <span class="nav-text">Nueva Factura</span>
                </a>
            </li>            
        @endif
        @if(isset($user) && ($user->hasAccess(['invoices.read']) || isset($orgRole) && $orgRole=='admin'))
            <li {!! (Request::is('invoices_payment_log/create') ? 'class="active"' : '') !!}>
                <a href="{{url('invoices_payment_log/create')}}">
                    <i class="material-icons ">add_circle</i>
                    <span class="nav-text">Nuevo Pago REP</span>
                </a>
            </li>            
        @endif
        <div class="border-dashed"></div>
        @if(isset($user) && ($user->hasAccess(['sales_order.read']) || isset($orgRole) && $orgRole=='admin'))
            <li {!! (Request::is('sales_order/create') ? 'class="active"' : '') !!}>
                <a href="{{url('sales_order/create')}}">
                    <i class="material-icons ">add_circle</i>
                    <span class="nav-text">Nueva Nota de Venta</span>
                </a>
            </li>            
        @endif
        @if(isset($user) && ($user->hasAccess(['quotation.read']) || isset($orgRole) && $orgRole=='admin'))
            <li {!! (Request::is('quotation/create') ? 'class="active"' : '') !!}>
                <a href="{{url('quotation/create')}}">
                    <i class="material-icons ">add_circle</i>
                    <span class="nav-text">Nueva Cotización</span>
                </a>
            </li>            
        @endif
        @if(isset($user) && ($user->hasAccess(['company.read']) || isset($orgRole) && $orgRole=='admin'))
            <li {!! (Request::is('company/create') ? 'class="active"' : '') !!}>
                <a href="{{url('company/create')}}">
                    <i class="material-icons ">add_circle</i>
                    <span class="nav-text">Nuevo Cliente</span>
                </a>
            </li>            
        @endif
        @if(isset($user) && ($user->hasAccess(['product.read']) || isset($orgRole) && $orgRole=='admin'))
            <li {!! (Request::is('product/create') ? 'class="active"' : '') !!}>
                <a href="{{url('product/create')}}">
                    <i class="material-icons ">add_circle</i>
                    <span class="nav-text">Nuevo Producto</span>
                </a>
            </li>            
        @endif
        <div class="border-bottom"></div>
        @if(isset($user) && ($user->hasAccess(['customers.read']) || isset($orgRole) && $orgRole=='admin'))
            <li {!! (Request::is('customer/*') || Request::is('customer')
             || Request::is('company') ? 'class="active"' : '') !!}>
                <a href="{{url('company')}}">
                    <i class="material-icons ">person_pin</i>
                    <span class="nav-text">Clientes</span>
                </a>
            </li>
            
        @endif
        @if(isset($user) && ($user->hasAccess(['products.read']) || isset($orgRole) && $orgRole=='admin'))
            
            <li {!! (Request::is('product') ? 'class="active"' : '') !!}>
                <a href="{{url('product')}}" >
                    <i class="material-icons ">layers</i>
                    <span class="nav-text">{{trans('left_menu.products')}}</span>
                </a>
            </li>
                    
        @endif
        @if(isset($user) && ($user->hasAccess(['invoices.read']) || isset($orgRole) && $orgRole=='admin'))
            <li class="menu-dropdown {!! (Request::is('invoice')
                 || Request::is('invoice_delete_list*') || Request::is('invoice_delete_list')
                || Request::is('invoices_payment_log')
                 || Request::is('paid_invoice*') || Request::is('paid_invoice')
                ? 'active':'') !!}">
                <a>
                    <span class="nav-caret pull-right">
                        <i class="fa fa-angle-right"></i>
                    </span>
                    <span class="nav-icon">
                        <i class="material-icons ">web</i>
                    </span>
                    <span class="nav-text">FACTURAZZION</span>
                </a>
                <ul class="nav-sub sub_menu">
                    <li {!! ((Request::is('invoice') || Request::is('invoice_delete_list*') || Request::is('invoice_delete_list')
                 || Request::is('paid_invoice*') || Request::is('paid_invoice')) ? 'class="active"' : '') !!}>
                        <a href="{{url('invoice')}}" class="sub-li">
                            <i class="material-icons ">receipt</i>
                            <span class="nav-text">(CFDI) Facturas</span>
                        </a>
                    </li>
                    <li {!! (Request::is('invoices_payment_log/*') || Request::is('invoices_payment_log') ? 'class="active"' : '') !!}>
                        <a href="{{url('invoices_payment_log')}}" class="sub-li">
                            <i class="material-icons ">archive</i>
                            <span class="nav-text">(REP) Recibo de Pago</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif            

        @if(isset($user) && ($user->hasAccess(['quotations.read']) || isset($orgRole) && $orgRole=='admin'))
            <li class="menu-dropdown" {!! ((Request::is('quotation')
            || Request::is('quotation_delete_list/*') || Request::is('quotation_delete_list')
            || Request::is('quotation_converted_list') || Request::is('quotation_invoice_list')) ? 'class="active"' : '') !!}>
                <a>
                    <span class="nav-caret pull-right">
                        <i class="fa fa-angle-right"></i>
                    </span>
                    <span class="nav-icon">
                        <i class="material-icons ">receipt</i>
                    </span>
                    <span class="nav-text">Comprobantes</span>
                </a>
                <ul class="nav-sub sub_menu">
                    <li {!! ((Request::is('quotation') || Request::is('quotation_delete_list/*') || Request::is('quotation_delete_list') || Request::is('quotation_converted_list') || Request::is('quotation_invoice_list')) ? 'class="active"' : '') !!}>
                        <a href="{{url('quotation')}}" class="sub-li">
                            <i class="material-icons ">archive</i>
                            <span class="nav-text">{{trans('left_menu.quotations')}}</span>
                        </a>
                    </li>
                    <li {!! (Request::is('sales_order') ? 'class="active"' : '') !!}>
                        <a href="{{url('sales_order')}}" class="sub-li">
                            <i class="material-icons ">archive</i>
                            <span class="nav-text">Notas de Venta</span>
                        </a>
                    </li>
                    <li {!! (Request::is('payments') ? 'class="active"' : '') !!}>
                        <a href="" class="sub-li">
                            <i class="material-icons ">archive</i>
                            <span class="nav-text">Pagos</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        
        <li {!! (Request::is('report') ? 'class="active"' : '') !!}>
                <a href="{{url('report')}}" >
                    <i class="material-icons ">cloud_download</i>
                    <span class="nav-text">Reportes</span>
                </a>
            </li>

        <li class="menu-dropdown">
            <a>
                <span class="nav-caret pull-right">
                    <i class="fa fa-angle-right"></i>
                </span>
                <span class="nav-icon">
                    <i class="material-icons ">settings</i>
                </span>
                <span class="nav-text">Configuración</span>
            </a>
            <ul class="nav-sub sub_menu">
                @if(isset($user) && ($user->hasAccess(['taxes.read']) || isset($orgRole) && $orgRole=='admin'))
            
                    <li {!! (Request::is('tax') ? 'class="active"' : '') !!}>
                        <a href="{{url('tax')}}" class="sub-li">
                            <span class="nav-icon">
                                <i class="material-icons ">book</i>
                            </span>
                            <span class="nav-text">{{trans('left_menu.taxes')}}</span>
                        </a>
                    </li>
                            
                @endif
                @if(isset($user) && isset($orgRole) && $orgRole=='admin')
                    <li {!! (Request::is('setting/*') || Request::is('setting') ? 'class="active"' : '') !!}>
                        <a href="{{url('setting')}}" class="sub-li">
                            <span class="nav-icon">
                                <i class="material-icons ">settings</i>
                            </span>
                            <span class="nav-text">Ajustes</span>
                        </a>
                    </li>
                    @if($organization->subscription_type=='paypal')
                        <li {!! (Request::is('paypal_transactions/*') || Request::is('paypal_transactions') ? 'class="active"' : '') !!}>
                            <a href="{{url('paypal_transactions')}}" class="sub-li">
                                <span class="nav-icon">
                                    <i class="material-icons ">payment</i>
                                </span>
                                <span class="nav-text">{{trans('left_menu.paypal_transactions')}}</span>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>
        </li>
        @if(isset($user) && isset($orgRole) && $orgRole=='admin')
        <li {!! (Request::is('subscription*') || Request::is('subscription') ? 'class="active"' : '') !!}>
            <a href="{{url('subscription')}}">
                <span class="nav-icon">
                    <i class="material-icons ">web</i>
                </span>
                <span class="nav-text">Suscripción</span>
            </a>
        </li>
        <li class="menu-dropdown">
            <a class="text-danger">
                <span class="nav-caret pull-right text-danger">
                    <i class="fa fa-angle-right text-danger"></i>
                </span>
                <span class="nav-icon">
                    <i class="material-icons text-danger">help</i>
                </span>
                <span class="nav-text">Ayuda</span>
            </a>
            <ul class="nav-sub sub_menu">
                <li {!! (Request::is('support*') || Request::is('support') ? 'class="active"' : '') !!}>
                    <a href="{{url('support#/s/tickets')}}" class="sub-li">
                        <span class="nav-icon">
                            <i class="material-icons ">phone</i>
                        </span>
                        <span class="nav-text">Ticket de Soporte</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="sub-li">
                        <span class="nav-icon">
                            <i class="material-icons ">chat</i>
                        </span>
                        <span class="nav-text">Chat</span>
                    </a>
                </li>
            </ul>
        </li>
        @endif
</div>
