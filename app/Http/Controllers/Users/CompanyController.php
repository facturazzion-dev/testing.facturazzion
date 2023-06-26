<?php

namespace App\Http\Controllers\Users;

use App\Helpers\ExcelfileValidator;
use App\Http\Controllers\Controller;
use App\Http\Requests\CityRequest;
use App\Http\Requests\CompanyImportRequest;
use App\Http\Requests\CompanyRequest;
use App\Models\Company;
use App\Repositories\CallRepository;
use App\Repositories\CityRepository;
use App\Repositories\CompanyBanksRepository;
use App\Repositories\CompanyContactsRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CountryRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EmailRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\MeetingRepository;
use App\Repositories\OptionRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\StateRepository;
use App\Repositories\UserRepository;
use DataTables;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;

class CompanyController extends Controller
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var SalesOrderRepository
     */
    private $salesOrderRepository;
    /**
     * @var OrganizationSettingsRepository
     */
    private $organizationSettingsRepository;

    private $countryRepository;

    private $stateRepository;

    private $cityRepository;

    private $meetingRepository;

    private $callRepository;

    private $emailRepository;

    private $customerRepository;

    private $organizationRepository;

    private $optionRepository;

    private $companyContactsRepository;

    private $companyBanksRepository;

    protected $user;

    /**
     *
     * @param OrganizationSettingsRepository $OrganizationSettingsRepository
     */
    public function __construct(
        CompanyRepository $companyRepository,
        UserRepository $userRepository,
        InvoiceRepository $invoiceRepository,
        QuotationRepository $quotationRepository,
        SalesOrderRepository $salesOrderRepository,
        CountryRepository $countryRepository,
        StateRepository $stateRepository,
        CityRepository $cityRepository,
        MeetingRepository $meetingRepository,
        CallRepository $callRepository,
        EmailRepository $emailRepository,
        CustomerRepository $customerRepository,
        OrganizationRepository $organizationRepository,
        OptionRepository $optionRepository,
        CompanyContactsRepository $companyContactsRepository,
        CompanyBanksRepository $companyBanksRepository,
        OrganizationSettingsRepository $organizationSettingsRepository
    ) {
        parent::__construct();

        $this->companyRepository = $companyRepository;
        $this->userRepository = $userRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->cityRepository = $cityRepository;
        $this->meetingRepository = $meetingRepository;
        $this->callRepository = $callRepository;
        $this->emailRepository = $emailRepository;
        $this->customerRepository = $customerRepository;
        $this->organizationRepository = $organizationRepository;
        $this->optionRepository = $optionRepository;
        $this->companyContactsRepository = $companyContactsRepository;
        $this->companyBanksRepository = $companyBanksRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        view()->share('type', 'company');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = trans('company.companies');

        return view('user.company.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['customers.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = trans('company.new');
        $orgSettings = $this->organizationSettingsRepository->getAll();
        $states = $this->stateRepository->orderBy('name', 'asc')->findByField('country_id', '142')->pluck('name', 'id');
        $cities = $this->cityRepository->getByState('2428');

        $payment_method = $this->optionRepository->getAll()
            ->where('category', 'payment_methods')
            ->map(function ($title) {
                return [
                    'text' => $title->value . ' - ' . $title->title,
                    'id' => $title->value,
                ];
            })->pluck('text', 'id')->prepend(trans('company.select_payment_method'), '');

        $payment_type = $this->optionRepository->getAll()
            ->where('category', 'payment_type')
            ->map(function ($title) {
                return [
                    'text' => $title->value . ' - ' . $title->title,
                    'id' => $title->value,
                ];
            })->pluck('text', 'id')->prepend(trans('company.select_payment_type'), '');

        $cfdi_use = $this->optionRepository->getAll()
            ->where('category', 'cfdi_use')
            ->map(function ($title) {
                return [
                    'text' => $title->value . ' - ' . $title->title,
                    'id' => $title->value,
                ];
            })->pluck('text', 'id')->prepend(trans('company.select_cfdi_use'), '');

        return view('user.company.create', compact('title', 'states', 'cities', 'payment_method', 'payment_type', 'cfdi_use', 'orgSettings'));
    }

    public function store(CompanyRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $organization = $this->userRepository->getOrganization();
        $request->merge(['user_id' => $this->user->id, 'organization_id' => $organization->id]);

        if ($request->hasFile('company_avatar_file')) {
            $file = $request->file('company_avatar_file');
            $file = $this->companyRepository->uploadAvatar($file);

            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->companyRepository->generateThumbnail($file);
        }

        $company = $this->companyRepository->create($request->except('company_avatar_file', 'contacts', 'banks'));

        $contacts = $request->contacts;

        if ($contacts != null) {

            foreach ($contacts as $contact) {

                if (!empty($contact['name'])) {

                    $contact['organization_id'] = $organization->id;
                    $contact['company_id'] = $company->id;

                    $this->companyContactsRepository->create($contact);
                }
            }
        }

        $banks = $request->banks;

        if ($banks != null) {

            foreach ($banks as $bank) {

                if (!empty($bank['name'])) {

                    $bank['organization_id'] = $organization->id;
                    $bank['company_id'] = $company->id;

                    $this->companyBanksRepository->create($bank);
                }
            }
        }

        return redirect('company');
    }

    public function edit(Company $company)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['customers.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = trans('company.edit');
        $states = $this->stateRepository->orderBy('name', 'asc')->findByField('country_id', $company->country_id)->pluck('name', 'id');
        $cities = $this->cityRepository->getByState($company->state_id);

        $payment_method = $this->optionRepository->getAll()
            ->where('category', 'payment_methods')
            ->map(function ($title) {
                return [
                    'text' => $title->value . ' - ' . $title->title,
                    'id' => $title->value,
                ];
            })->pluck('text', 'id')->prepend(trans('company.select_payment_method'), '');

        $payment_type = $this->optionRepository->getAll()
            ->where('category', 'payment_type')
            ->map(function ($title) {
                return [
                    'text' => $title->value . ' - ' . $title->title,
                    'id' => $title->value,
                ];
            })->pluck('text', 'id')->prepend(trans('company.select_payment_type'), '');

        $cfdi_use = $this->optionRepository->getAll()
            ->where('category', 'cfdi_use')
            ->map(function ($title) {
                return [
                    'text' => $title->value . ' - ' . $title->title,
                    'id' => $title->value,
                ];
            })->pluck('text', 'id')->prepend(trans('company.select_cfdi_use'), '');

        $company->load('contacts','banks');

        return view('user.company.create', compact('title', 'company', 'cities', 'states', 'payment_method', 'payment_type', 'cfdi_use'));
    }

    public function update(CompanyRequest $request, Company $company)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['customers.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (empty($request->main_contact_person)) {
            $request->merge(['main_contact_person' => 0]);
        }
        $organization = $this->userRepository->getOrganization();

        if ($request->hasFile('company_avatar_file')) {
            $file = $request->file('company_avatar_file');
            $file = $this->companyRepository->uploadAvatar($file);

            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->companyRepository->generateThumbnail($file);
        }

        $company->update($request->except('company_avatar_file', 'contacts', 'banks'));

        $contacts = $request->contacts;

        if ($contacts != null) {

            foreach ($contacts as $contact) {

                if (!empty($contact['id'])) {

                    $contact_data = $this->companyContactsRepository->find($contact['id']);
                    $contact_data->update($contact);
                } elseif ($contact['name'] != "") {

                    $contact['organization_id'] = $organization->id;
                    $contact['company_id'] = $company->id;

                    $this->companyContactsRepository->create($contact);
                }
            }
        }

        $banks = $request->banks;

        if ($banks != null) {

            foreach ($banks as $bank) {

                if (!empty($bank['id'])) {

                    $bank_data = $this->companyBanksRepository->find($bank['id']);
                    $bank_data->update($bank);
                } elseif ($bank['name'] != "") {

                    $bank['organization_id'] = $organization->id;
                    $bank['company_id'] = $company->id;

                    $this->companyBanksRepository->create($bank);
                }
            }
        }

        return redirect('company');
    }

    public function show(Company $company)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = trans('company.details');
        $action = trans('action.show');

        $total_sales = round($this->invoiceRepository->getAllForCustomer($company->id)->sum('grand_total'), 3);

        return view('user.company.show', compact('title', 'company', 'action', 'total_sales'));
    }

    public function delete(Company $company)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = trans('company.delete');
        $action = trans('action.delete');

        $total_sales = round($this->invoiceRepository->getAllForCustomer($company->id)->sum('grand_total'), 3);

        return view('user.company.delete', compact('title', 'company', 'action', 'total_sales'));
    }

    public function destroy(Company $company)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $company->delete();

        return redirect('company');
    }

    public function toggleFavorite(Company $company)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.favorite'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        $favoriteCompanies = $this->companyRepository->findWhere(['favorite' => 1]);

        foreach ($favoriteCompanies as $favCompany) {
            $favCompany->update(['favorite' => 0]);
        }

        $company->update(['favorite' => !$company->favorite]);

        return response()->json([
            'state' => 'updated',
            'company' => $company->only(['id', 'name']),
        ], 200);
    }

    public function data()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $companies = $this->companyRepository->getAll()
            ->sortByDesc('id')
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'sat_rfc' => $company->sat_rfc,
                    'sat_name' => $company->sat_name,
                    'name' => $company->name,
                    'phone' => $company->phone,
                    'email' => $company->email,
                    'favorite' => $company->favorite,
                ];
            });
        return DataTables::of($companies)->make();
    }

    private function generateParams()
    {
        $this->user = $this->getUser();
        $countries = $this->countryRepository->orderBy('name', 'asc')->pluck('name', 'id')->prepend(trans('company.select_country'), '');

        view()->share('countries', $countries);
    }

    public function downloadExcelTemplate()
    {
        ob_end_clean();
        $path = base_path('resources/excel-templates/FZZClientes.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return 'File not found!';
    }

    public function getImport()
    {
        $title = trans('company.newupload');
        return view('user.company.import', compact('title'));
    }

    public function postImport(Request $request)
    {
        if (!ExcelfileValidator::validate($request)) {
            return response('invalid File or File format', 500);
        }
        $reader = Excel::import($request->file('file'));
        $data = $reader->all()->map(function ($row) {

            return [

                'name' => $row->nombrecomercial_req,
                'email' => $row->correoelectronico_req,
                'phone' => $row->telefono,
                'sat_name' => $row->razonsocial_req,
                'sat_rfc' => $row->rfc_req,
                'street' => $row->calle,
                'exterior_no' => $row->noexterior,
                'interior_no' => $row->nointerior,
                'suburb' => $row->colonia,
                'zip_code' => $row->codigopostal,
                'country_id' => $row->pais_req,
                // 'state_id' => $row['Estado'],
                // 'city_id' => $row['Ciudad'],

            ];
        });
        $countries = $this->countryRepository->orderBy("name", "asc")->all()
            ->map(function ($country) {
                return [
                    'text' => $country->name,
                    'id' => $country->id,
                ];
            });

        return response()->json(compact('data', 'countries'), 200);
    }
    public function postAjaxStore(CompanyImportRequest $request)
    {
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $request->merge(['user_id' => $user->id, 'organization_id' => $organization->id, 'payment_method' => 'PUE', 'payment_type' => '01', 'cfdi_use' => 'G03']);
        $this->companyRepository->create($request->except('created', 'errors', 'selected', 'tags'));

        return response()->json([], 200);
    }

    public function postAjaxStoreCity(CityRequest $request)
    {
        $user = $this->userRepository->getUser();

        $request = [
            'name' => $request->h_name,
            'user_id' => $user->id,
            'state_id' => $request->h_state_id,
        ];

        $city = $this->cityRepository->create($request);

        return response()->json([
            'id' => $city->id,
            'name' => $city->name
        ], 200);
    }

    public function ajaxCompanyData(Request $request)
    {
        $columns = [
            'id',
            'name',
            'phone',
            'country_id',
            'state_id',
            'city_id',
            'sat_name',
            'sat_rfc',
            'email',
            'street',
            'exterior_no',
            'interior_no',
            'suburb',
            'payment_method',
            'payment_type',
            'cfdi_use',
            'fiscal_regimen',
            'zip_code',
        ];
        switch ($request->id) {
            case 'general':
                $organization = $this->userRepository->getOrganization();
                $company = new Company([
                    'name' => 'CLIENTE NACIONAL',
                    'phone' => '',
                    'country_id' => $organization->getSetting('country_id'),
                    'state_id' => $organization->getSetting('state_id'),
                    'city_id' => $organization->getSetting('city_id'),
                    'sat_name' => 'CLIENTE NACIONAL',
                    'sat_rfc' => 'XAXX010101000',
                    'email' => '',
                    'street' => '',
                    'exterior_no' => '',
                    'interior_no' => '',
                    'suburb' => '',
                    'payment_method' => 'PUE',
                    'payment_type' => '99',
                    'cfdi_use' => 'S01',
                    'fiscal_regimen' => '616',
                    'zip_code' => $organization->getSetting('zip_code'),
                ]);
                break;
            case 'general_extranjero':
                $organization = $this->userRepository->getOrganization();
                $company = new Company([
                    'name' => 'CLIENTE EXTRANJERO',
                    'phone' => '',
                    'country_id' => $organization->getSetting('country_id'),
                    'state_id' => $organization->getSetting('state_id'),
                    'city_id' => $organization->getSetting('city_id'),
                    'sat_name' => 'CLIENTE EXTRANJERO',
                    'sat_rfc' => 'XEXX010101000',
                    'email' => '',
                    'street' => '',
                    'exterior_no' => '',
                    'interior_no' => '',
                    'suburb' => '',
                    'payment_method' => 'PUE',
                    'payment_type' => '99',
                    'cfdi_use' => 'S01',
                    'fiscal_regimen' => '616',
                    'zip_code' => $organization->getSetting('zip_code'),
                ]);
                break;
            
            default:
                $company = $this->companyRepository->find($request->id, $columns);
                break;
        }

        return response()->json([
            'company' => $company
        ], 200);
    }

    public function ajaxStore(CompanyRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['customers.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $organization = $this->userRepository->getOrganization();
        $request->merge(['user_id' => $this->user->id, 'organization_id' => $organization->id]);
        $contacts = $request->contacts;
        $banks = $request->banks;
        $company = $this->companyRepository->updateOrCreate([
            'id' => $request->id,
        ], $request->except('company_avatar_file', 'contacts', 'banks'));

        if ($request->hasFile('company_avatar_file')) {
            $file = $request->file('company_avatar_file');
            $file = $this->companyRepository->uploadAvatar($file);

            $request->merge([
                'company_avatar' => $file->getFileInfo()->getFilename(),
            ]);
            $this->companyRepository->generateThumbnail($file);
        }
        if ($contacts != null) {
            foreach ($contacts as $contact) {
                if ($contact['name'] != "") {
                    $contact['organization_id'] = $organization->id;
                    $contact['company_id'] = $company->id;
                    $this->companyContactsRepository->create($contact);
                }
            }
        }
        if ($banks != null) {
            foreach ($banks as $bank) {
                if ($bank['name'] != "") {
                    $bank['organization_id'] = $organization->id;
                    $bank['company_id'] = $company->id;
                    $this->companyBanksRepository->create($bank);
                }
            }
        }
        return response()->json([
            'company' => $company
        ], 200);
    }
}
