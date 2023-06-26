<div class="nav-tabs-custom" id="setting_tabs">
    <ul class="nav nav-tabs settings" role="tablist">
        <li class="nav-item mt-2">
            <a class="nav-link active" href="#general_configuration" data-bs-toggle="tab">Datos Fiscales</a>
        </li>
        <li class="nav-item mt-2">
            <a class="nav-link" href="#contacts_configuration" data-bs-toggle="tab">Personas de Contacto</a>
        </li>
        <li class="nav-item mt-2">
            <a class="nav-link" href="#bank_configuration" data-bs-toggle="tab">Cuenta Bancaria</a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="general_configuration">
            @include('content._partials._forms.form-company')
        </div>
        <div class="tab-pane" id="contacts_configuration">
            <div id="contacts" class="card-body">
                <div data-repeater-list="contacts">
                    @each('content._partials._forms.form-contact-repeater', $company->contacts ?? [], 'contact', 'content._partials._forms.form-contact-repeater')
                </div>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-icon btn-primary" type="button" data-repeater-create>
                            <i data-feather="plus" class="me-25"></i>
                            <span>Añadir otro</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane" id="bank_configuration">
            <div id="banks" class="card-body">
                <div data-repeater-list="banks">
                    @each('content._partials._forms.form-bank-repeater', $company->banks ?? [], 'bank', 'content._partials._forms.form-bank-repeater')
                </div>
                <div class="row">
                    <div class="col-12">
                        <button class="btn btn-icon btn-primary" type="button" data-repeater-create>
                            <i data-feather="plus" class="me-25"></i>
                            <span>Añadir otro</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>