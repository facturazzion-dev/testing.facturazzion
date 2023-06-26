<?php

namespace App\Http\Controllers\Admin;

use App\Events\Organization\OrganizationCreated;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\PayPlanRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\CountryRepository;
use App\Repositories\StateRepository;
use App\Repositories\CityRepository;
use DataTables;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationRequest;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationRolesRepository;
use App\Repositories\UserRepository;
use App\Repositories\TaxRepository;
use Srmklive\PayPal\Facades\PayPal;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Event;
use Stripe\Stripe;
use PhpCfdi\Credentials\Credential;

// @TODO:: Add appropriate flash messages
class OrganizationController extends Controller
{
    private $organizationRepository;

    private $userRepository;

    private $organizationRolesRepository;

    private $payPlanRepository;

    private $settingsRepository;

    private $countryRepository;

    private $stateRepository;

    private $cityRepository;

    private $organizationSettingsRepository;

    private $taxRepository;

    public function __construct(
        OrganizationRepository $organizationRepository,
        UserRepository $userRepository,
        OrganizationRolesRepository $organizationRolesRepository,
        PayPlanRepository $payPlanRepository,
        CountryRepository $countryRepository,
        StateRepository $stateRepository,
        CityRepository $cityRepository,
        SettingsRepository $settingsRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        TaxRepository $taxRepository
    ) {
        $this->organizationRepository = $organizationRepository;
        $this->userRepository = $userRepository;
        $this->organizationRolesRepository = $organizationRolesRepository;
        $this->payPlanRepository = $payPlanRepository;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->cityRepository = $cityRepository;
        $this->settingsRepository = $settingsRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->taxRepository = $taxRepository;

        view()->share('type', 'organizations');
    }

    public function index()
    {
        $title = trans('organizations.organizations');

        return view('admin.organizations.index', compact('title'));
    }

    // public function data(DataTables $datatable)
    // {        
    //     $orgs = $this->organizationRepository->all()
    //         ->map(function ($orgs) {

    //             $orgSettings = $this->organizationSettingsRepository->get()->where('organization_id',$orgs->id)->pluck('value', 'key');

    //             // Log::info($orgSettings);

    //             return [
    //                 'id' => $orgs->id,
    //                 'sat_rfc' => isset($orgSettings['sat_rfc'])?$orgSettings['sat_rfc']:"",
    //                 'sat_name' => isset($orgSettings['sat_name'])?$orgSettings['sat_name']:"",
    //                 'name' => isset($orgSettings['site_name'])?$orgSettings['site_name']:"",
    //                 'subscription_type' => $orgs->subscription_type??'--',
    //                 'phone_number' => $orgs->phone_number,
    //                 'email' => $orgs->email,
    //                 'is_deleted' => $orgs->is_deleted,
    //             ];
    //         });

    //     return DataTables::collection($orgs)
    //         ->addColumn('actions', '<a href="{{ url(\'organizations/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
    //                                         <i class="fa fa-fw fa-pencil text-warning"></i> </a>
    //                                  <a href="{{ url(\'organizations/\' . $id ) }}" title="{{ trans(\'table.details\') }}" >
    //                                         <i class="fa fa-fw fa-eye text-primary"></i> </a>
    //                                         @if($is_deleted==0)
    //                                             <a href="{{ url(\'organizations/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.disable\') }}">
    //                                             <i class="fa fa-fw fa-ban text-danger"></i> </a>
    //                                         @else
    //                                             <a href="{{ url(\'organizations/\' . $id . \'/activate\' ) }}" title="{{ trans(\'table.restore\') }}">
    //                                             <i class="fa fa-fw fa-undo text-success"></i> </a>
    //                                         @endif')
    //         ->rawColumns(['actions'])
    //         ->removeColumn('id')
    //         ->removeColumn('is_deleted')
    //         ->make();
    // }

    public function data(DataTables $datatable)
    {
        $orgs = $this->organizationRepository->getAllSummary()->toArray();
        // $orgs = $this->organizationRepository->all()->sortByDesc('created_at')
        //     ->map(function ($orgs) {

        //         // $orgSettings = $this->organizationSettingsRepository->getKey('vat_number')
        //         $orgSettings = $this->organizationSettingsRepository->findByField('organization_id',$orgs->id)->pluck('value','key');                
        //         $last_login = UserLogin::where('user_id', $orgs->user_id)->latest()->first();

        //         return [
        //             'id' => $orgs->id,
        //             'sat_rfc' => isset($orgSettings['sat_rfc'])?$orgSettings['sat_rfc']:"",
        //             'sat_name' => isset($orgSettings['sat_name'])?$orgSettings['sat_name']:"",
        //             'email' => $orgs->email,
        //             'phone_number' => $orgs->phone_number,
        //             'invoices' => $orgs->invoices()->count(),
        //             'last_login' => $orgs->users()->first()->last_login,
        //             'is_deleted' => $orgs->is_deleted,
        //         ];
        //     });

        return DataTables::of($orgs)
            ->addColumn('actions', '<a href="{{ url(\'organizations/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning"></i> </a>
                                     <a href="{{ url(\'organizations/\' . $id ) }}" title="{{ trans(\'table.details\') }}" >
                                            <i class="fa fa-fw fa-eye text-primary"></i> </a>
                                            @if($is_deleted==0)
                                                <a href="{{ url(\'organizations/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.disable\') }}">
                                                <i class="fa fa-fw fa-ban text-danger"></i> </a>
                                            @else
                                                <a href="{{ url(\'organizations/\' . $id . \'/activate\' ) }}" title="{{ trans(\'table.restore\') }}">
                                                <i class="fa fa-fw fa-undo text-success"></i> </a>
                                            @endif')
            ->removeColumn('id')
            ->removeColumn('is_deleted')
            ->rawColumns(['actions'])
            ->make();
    }

    public function create()
    {
        $title = trans('organizations.new_organization');
        $this->generateParams();

        $states = $this->stateRepository->orderBy('name', 'asc')->findByField('country_id', '142')->pluck('name', 'id')->prepend(trans('lead.select_state'), '');

        $cities = $this->cityRepository->getByState(null);

        return view('admin.organizations.create', compact('title', 'states', 'cities'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(OrganizationRequest $request)
    {


        $this->user = $this->userRepository->getUser();
        $request->merge(['user_id' => $this->user->id]);


        $user = $this->userRepository->create([
            'first_name' => $request->owner_first_name,
            'last_name' => $request->owner_last_name,
            'email' => $request->owner_email,
            'phone_number' => $request->owner_phone_number,
            'password' => $request->owner_password,
            'user_id' => $request->user_id,
        ], true);


        $this->userRepository->assignRole($user, 'user');
        // create organization for the user
        $organization = '';

        $paybook_live_key = '';
        $paybook_sandbox_key = '';

        if ($user) {
            // generate thumbnail
            if ($request->hasFile('organization_avatar_file')) {
                $file = $request->file('organization_avatar_file');
                $file = $this->organizationRepository->uploadLogo($file);

                $request->merge([
                    'logo' => $file->getFileInfo()->getFilename(),
                ]);
                $this->organizationRepository->generateThumbnail($file);
            }

            $organization = $this->organizationRepository->create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'email' => $request->email,
                'user_id' => $user->id,
                'logo' => $request->logo,
                'trial_ends_at' => now()->addDays($request->duration),
                'generic_trial_plan' => $request->plan_id,
                'created_by_admin' => 1,
                'subscription_type' => 'Otro',

            ]);

            $orgSettings = [
                'site_name' => $request->name,
                'site_email' => $request->email,
                'sat_name' => $request->sat_name,
                'sat_rfc' => $request->sat_rfc,
                'street' => $request->street,
                'exterior_no' => $request->exterior_no,
                'interior_no' => $request->interior_no,
                'suburb' => $request->suburb,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city_id' => $request->city_id,
                'phone' => $request->phone_number,
                'zip_code' => $request->zip_code,
                'fiscal_regimen' => $request->fiscal_regimen,
                'fiel_pwd' => $request->fiel_pwd,
                'date_format' => 'F j,Y',
                'time_format' => 'H:i',
                'date_time_format' => 'F j,Y H:i',
                'currency' => 'MXN',
                'language' => 'es',
                'sales_tax' => '16',
                'payment_term1' => '7',
                'payment_term2' => '15',
                'payment_term3' => '30',
                'opportunities_reminder_days' => '1',
                'invoice_reminder_days' => '1',
                'quotation_start_number' => '1',
                'quotation_prefix' => 'Cot',
                'quotation_template' => 'quotation_blue',
                'sales_start_number' => '1',
                'sales_prefix' => 'NV',
                'saleorder_template' => 'saleorder_blue',
                'invoice_start_number' => '1',
                'invoice_prefix' => 'Fac',
                'invoice_template' => 'invoice_blue',
                'invoice_payment_prefix' => 'RP',
                'invoice_payment_start_number' => '1',
                'invoice_payment_template' => 'invoice_payment_blue',
            ];
            foreach ($orgSettings as $key => $value) {
                $this->organizationSettingsRepository->setKey($key, $value, $organization->id);
            }

            event(new OrganizationCreated($organization));
            $role = $this->organizationRolesRepository->findByField('slug', 'admin')->first();
            $this->organizationRolesRepository->attachRole($organization, $user, $role);

            if ($request->hasFile('cer_file') && $request->hasFile('key_file')) {

                //convert .cer & .key files to base64 as needed for paybook
                $cer_file = base64_encode(file_get_contents($request->file('cer_file')->path()));
                $key_file = base64_encode(file_get_contents($request->file('key_file')->path()));
                $this->organizationSettingsRepository->setKey('cer_file', $cer_file);
                $this->organizationSettingsRepository->setKey('key_file', $key_file);

                $csd = Credential::openFiles($request->file('cer_file')->path(), $request->file('key_file')->path(), $request->fiel_pwd);

                //convert .cer & .key files to pem as needed for gofac
                $this->organizationSettingsRepository->setKey('num_certificado', $csd->certificate()->serialNumber()->bytes());
                $this->organizationSettingsRepository->setKey('cer_pem_file', $csd->certificate()->pem());
                $this->organizationSettingsRepository->setKey('key_pem_file', $csd->privateKey()->pem());
            }


            // Save Default Taxes to Organization
            $taxes = array(
                ['name' => 'IVA', 'tax' => '002', 'tax_type' => 'Traslado', 'percentage' => '.16', 'factor_type' => 'Tasa'],
                ['name' => 'IVA', 'tax' => '002', 'tax_type' => 'Traslado', 'percentage' => '.08', 'factor_type' => 'Tasa'],
                ['name' => 'IVA', 'tax' => '002', 'tax_type' => 'Retención', 'percentage' => '.106667', 'factor_type' => 'Tasa'],
                ['name' => 'ISR', 'tax' => '001', 'tax_type' => 'Retención', 'percentage' => '.1', 'factor_type' => 'Tasa']
            );


            foreach ($taxes as $tax) {
                $tax['user_id'] = $organization->user_id;
                $tax['organization_id'] = $organization->id;

                $this->taxRepository->create($tax);
            }
        }

        return redirect('organizations');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show($org)
    {

        $organization = $this->organizationRepository->find($org);

        $title = trans('organizations.show_organization');
        $action = trans('action.show');
        $this->generateParams();

        if ($organization->subscription_type == 'paypal') {
            $provider = PayPal::setProvider('express_checkout');
            $paypal_mode = $this->settingsRepository->getKey('paypal_mode');
            if (!isset($paypal_mode)) {
                flash(trans('subscription.paypal_keys_are_required'))->error();
                return redirect()->back();
            }
            $transactions = $organization->paypalTransactions;
            $paypalTransactions = [];
            if (isset($transactions)) {
                foreach ($transactions as $transaction) {
                    $paypalTransactions[] = $provider->getTransactionDetails($transaction->txn_id);
                }
                view()->share('paypalTransactions', $paypalTransactions);
            }
        }

        $stripe_secret = $this->settingsRepository->getKey('stripe_secret');

        if (isset($stripe_secret) && $stripe_secret && isset($organization->stripe_id)) {
            Stripe::setApiKey($stripe_secret);
            $payments = Charge::all([
                'customer' => $organization->stripe_id,
                'limit' => 100,
            ]);
            $subscription_customerid = $organization->stripe_id;
            $subscription_customer = Customer::retrieve($subscription_customerid);
            $events = Event::all([
                'limit' => 100,
            ]);
            view()->share('events', $events);
            view()->share('subscription_customerid', $subscription_customerid);
            view()->share('subscription_customer', $subscription_customer);
            view()->share('payments', $payments);
        }

        return view('admin.organizations.show', compact('title', 'organization', 'action'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($org)
    {

        $organization = $this->organizationRepository->find($org);

        $user = $this->userRepository->find($organization->user_id);
        $this->generateParams();
        $organization->owner_first_name = $user->first_name;
        $organization->owner_last_name = $user->last_name;
        $organization->owner_phone_number = $user->phone_number;
        $organization->owner_email = $user->email;

        $orgSettings = $this->organizationSettingsRepository->get()->where('organization_id', $org)->pluck('value', 'key');

        $organization->name = isset($orgSettings['site_name']) ? $orgSettings['site_name'] : "";
        $organization->email = isset($orgSettings['site_email']) ? $orgSettings['site_email'] : "";
        $organization->sat_name = isset($orgSettings['sat_name']) ? $orgSettings['sat_name'] : "";
        $organization->sat_rfc = isset($orgSettings['sat_rfc']) ? $orgSettings['sat_rfc'] : "";
        $organization->street = isset($orgSettings['street']) ? $orgSettings['street'] : "";
        $organization->exterior_no = isset($orgSettings['exterior_no']) ? $orgSettings['exterior_no'] : "";
        $organization->interior_no = isset($orgSettings['interior_no']) ? $orgSettings['interior_no'] : "";
        $organization->suburb = isset($orgSettings['suburb']) ? $orgSettings['suburb'] : "";
        $organization->country_id = isset($orgSettings['country_id']) ? $orgSettings['country_id'] : "";
        $organization->state_id = isset($orgSettings['state_id']) ? $orgSettings['state_id'] : "";
        $organization->city_id = isset($orgSettings['city_id']) ? $orgSettings['city_id'] : "";
        $organization->phone_number = isset($orgSettings['phone']) ? $orgSettings['phone'] : "";
        $organization->zip_code = isset($orgSettings['zip_code']) ? $orgSettings['zip_code'] : "";
        $organization->fiscal_regimen = isset($orgSettings['fiscal_regimen']) ? $orgSettings['fiscal_regimen'] : "";
        $organization->cer_file = isset($orgSettings['cer_file']) ? $orgSettings['cer_file'] : "";
        $organization->key_file = isset($orgSettings['key_file']) ? $orgSettings['key_file'] : "";
        $organization->fiel_pwd = isset($orgSettings['fiel_pwd']) ? $orgSettings['fiel_pwd'] : "";

        $title = trans('organizations.edit_organization');

        $states = $this->stateRepository->orderBy('name', 'asc')->findByField('country_id', $organization->country_id)->pluck('name', 'id')->prepend(trans('lead.select_state'), '');

        $cities = $this->cityRepository->getByState($organization->state_id);

        return view('admin.organizations.edit', compact('title', 'organization', 'states', 'cities'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function update(OrganizationRequest $request, $org)
    {
        if ($request->hasFile('organization_avatar_file')) {
            $file = $request->file('organization_avatar_file');
            $file = $this->organizationRepository->uploadLogo($file);

            $request->merge([
                'logo' => $file->getFileInfo()->getFilename(),
            ]);
            $this->organizationRepository->generateThumbnail($file);
        }

        if ($request->hasFile('cer_file') && $request->hasFile('key_file')) {

            //convert .cer & .key files to base64 as needed for paybook
            $cer_file = base64_encode(file_get_contents($request->file('cer_file')->path()));
            $key_file = base64_encode(file_get_contents($request->file('key_file')->path()));
            $this->organizationSettingsRepository->setKey('cer_file', $cer_file, $org);
            $this->organizationSettingsRepository->setKey('key_file', $key_file, $org);

            $csd = Credential::openFiles($request->file('cer_file')->path(), $request->file('key_file')->path(), $request->fiel_pwd);

            //convert .cer & .key files to pem as needed for gofac
            $this->organizationSettingsRepository->setKey('num_certificado', $csd->certificate()->serialNumber()->bytes(), $org);
            $this->organizationSettingsRepository->setKey('cer_pem_file', $csd->certificate()->pem(), $org);
            $this->organizationSettingsRepository->setKey('key_pem_file', $csd->privateKey()->pem(), $org);
        }

        // Update Organization
        $organization = [
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
        ];
        if ($request->logo) {
            $organization['logo'] = $request->logo;
        }
        $organization = $this->organizationRepository->update($organization, $org);

        $request->merge([
            'site_name' => $request->name,
            'site_email' => $request->email,
            'phone' => $request->phone_number,
        ]);

        $orgSettings = [
            'site_name' => $request->name,
            'site_email' => $request->email,
            'sat_name' => $request->sat_name,
            'sat_rfc' => $request->sat_rfc,
            'street' => $request->street,
            'exterior_no' => $request->exterior_no,
            'interior_no' => $request->interior_no,
            'suburb' => $request->suburb,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'phone' => $request->phone_number,
            'zip_code' => $request->zip_code,
            'fiscal_regimen' => $request->fiscal_regimen,
            'fiel_pwd' => $request->fiel_pwd,
        ];
        foreach ($orgSettings as $key => $value) {
            $this->organizationSettingsRepository->setKey($key, $value, $org);
        }

        // Update User

        $user = $this->userRepository->find($organization->user_id);

        $userData = [
            'first_name' => $request->owner_first_name,
            'last_name' => $request->owner_last_name,
            'email' => $request->owner_email,
            'phone_number' => $request->owner_phone_number,
        ];

        if ($request->owner_password) {
            $userData['password'] = bcrypt($request->owner_password);
        }

        $user = $this->userRepository->update($userData, $user->id);

        return redirect('organizations');
    }

    public function delete($org)
    {
        $organization = $this->organizationRepository->find($org);
        view()->share('title', trans('organizations.delete_organization'));

        return view('admin.organizations.delete', compact('organization'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($org)
    {
        $org = $this->organizationRepository->find($org);
        $org->update(['is_deleted' => 1]);

        return redirect('organizations');
    }

    public function activate($org)
    {
        $org = $this->organizationRepository->find($org);
        $org->update(['is_deleted' => 0]);

        return redirect('organizations');
    }

    private function generateParams()
    {
        $payplans = $this->payPlanRepository->all()->sortByDesc('organizations');
        $countries = $this->countryRepository->orderBy('name', 'asc')->pluck('name', 'id')->prepend(trans('company.select_country'), '');

        $payment_plans_list = $payplans->values()->all();
        $org_payplan = $this->payPlanRepository->all();
        view()->share('payment_plans_list', $payment_plans_list);
        view()->share('org_payplan', $org_payplan);
        view()->share('countries', $countries);
    }



    public function paymentActivity($id)
    {
        $title = trans('paypal_transaction.title');
        $provider = PayPal::setProvider('express_checkout');
        $paypal_mode = $this->settingsRepository->getKey('paypal_mode');
        if (!isset($paypal_mode)) {
            flash(trans('subscription.paypal_keys_are_required'))->error();
            return redirect()->back();
        }
        $paypalTransactions = $provider->getTransactionDetails($id);
        return view('admin.organizations.transaction_details', compact('title', 'paypalTransactions', 'active_subscription'));
    }

    public function ajaxStateList(Request $request)
    {
        return $this->stateRepository->orderBy('name', 'asc')->findByField('country_id', $request->id)->pluck('name', 'id')->prepend(trans('lead.select_state'), '');
    }

    public function ajaxCityList(Request $request)
    {
        return $this->cityRepository->orderBy('name', 'asc')->findByField('state_id', $request->id)->pluck('name', 'id')->prepend(trans('lead.select_city'), '');
    }
}
