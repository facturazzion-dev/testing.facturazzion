<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrganizationSettingRequest;
use App\Models\PrintTemplate;
use App\Repositories\OptionRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\CountryRepository;
use App\Repositories\StateRepository;
use App\Repositories\CityRepository;
use App\Repositories\SettingsRepository;
use Illuminate\Http\Request;
use PhpCfdi\Credentials\Credential;

class SettingsController extends Controller
{
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    /**
     * @var OrganizationSettingsRepository
     */
    private $organizationSettingsRepository;
    private $organizationRepository;
    private $settingsRepository;

    /**
     * SettingsController constructor.
     *
     * @param OptionRepository               $optionRepository
     * @param OrganizationSettingsRepository $OrganizationSettingsRepository
     */
    public function __construct(
        OptionRepository $optionRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        OrganizationRepository $organizationRepository,
        SettingsRepository $settingsRepository,
        CountryRepository $countryRepository,
        StateRepository $stateRepository,
        CityRepository $cityRepository

    ) {
        parent::__construct();

        view()->share('type', 'setting');

        $this->optionRepository = $optionRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->organizationRepository = $organizationRepository;
        $this->settingsRepository = $settingsRepository;
        $this->countryRepository = $countryRepository;
        $this->stateRepository = $stateRepository;
        $this->cityRepository = $cityRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orgRole = $this->getUser()->orgRole;
        if ('admin' != $orgRole) {
            return redirect('dashboard');
        }
        $title = trans('settings.settings');
        $max_upload_file_size = [
            '1000' => '1MB',
            '2000' => '2MB',
            '3000' => '3MB',
            '4000' => '4MB',
            '5000' => '5MB',
            '6000' => '6MB',
            '7000' => '7MB',
            '8000' => '8MB',
            '9000' => '9MB',
            '10000' => '10MB',
        ];
        $currency = $this->optionRepository->getAll()
            ->where('category', 'currency')
            ->map(
                function ($title) {
                    return [
                        'text' => $title->title,
                        'id' => $title->value,
                    ];
                }
            )->pluck('text', 'id')->toArray();

        $backup_type = $this->optionRepository->getAll()
            ->where('category', 'backup_type')
            ->map(
                function ($title) {
                    return [
                        'text' => $title->value,
                        'id' => $title->title,
                    ];
                }
            );
        $orgSettings = $this->organizationSettingsRepository->getAll();

        $invoice_template = PrintTemplate::where('type', 'invoice')->pluck('name', 'slug')->toArray();
        $saleorder_template = PrintTemplate::where('type', 'saleorder')->pluck('name', 'slug')->toArray();
        $quotation_template = PrintTemplate::where('type', 'quotation')->pluck('name', 'slug')->toArray();
        $invoice_payment_template = PrintTemplate::where('type', 'invoice_payment')->pluck('name', 'slug')->toArray();
        $languages = $this->optionRepository->getAll()->where('category', 'language')->pluck('title', 'value');

        $countries = $this->countryRepository->orderBy('name', 'asc')->pluck('name', 'id')->prepend(trans('company.select_country'), '');

        $country_id = isset($orgSettings['country_id']) ? $orgSettings['country_id'] : "";
        $state_id = isset($orgSettings['state_id']) ? $orgSettings['state_id'] : "";

        $states = $this->stateRepository->orderBy('name', 'asc')->findByField('country_id', $country_id)->pluck('name', 'id')->prepend(trans('lead.select_state'), '');

        $cities = $this->cityRepository->getByState($state_id);

        return view('user.setting.index', compact(
            'title',
            'max_upload_file_size',
            'currency',
            'backup_type',
            'orgSettings',
            'invoice_template',
            'saleorder_template',
            'quotation_template',
            'invoice_payment_template',
            'languages',
            'countries',
            'states',
            'cities'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OrganizationSettingRequest|Request $request
     *
     * @return \Illuminate\Http\Response
     *
     * @internal param int $id
     */
    public function update(OrganizationSettingRequest $request)
    {
        $orgRole = $this->getUser()->orgRole;
        if ('admin' != $orgRole) {
            return redirect('dashboard');
        }
        $this->user = $this->getUser();

        if ('' != $request->hasFile('site_logo_file')) {
            $file = $request->file('site_logo_file');
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
            $this->organizationSettingsRepository->setKey('cer_file', $cer_file);
            $this->organizationSettingsRepository->setKey('key_file', $key_file);

            $csd = Credential::openFiles($request->file('cer_file')->path(), $request->file('key_file')->path(), $request->fiel_pwd);

            //convert .cer & .key files to pem as needed for gofac
            $this->organizationSettingsRepository->setKey('num_certificado', $csd->certificate()->serialNumber()->bytes());
            $this->organizationSettingsRepository->setKey('cer_pem_file', $csd->certificate()->pem());
            $this->organizationSettingsRepository->setKey('key_pem_file', $csd->privateKey()->pem());
        }

        $request->date_format = $request->date_format_custom;
        $request->time_format = $request->time_format_custom;
        if ('' == $request->date_format) {
            $request->date_format = 'F j,Y';
        }
        if ('' == $request->time_format) {
            $request->time_format = 'H:i';
        }
        $request->merge([
            'date_time_format' => $request->date_format . ' ' . $request->time_format,
        ]);
        foreach ($request->except('_token', 'pdf_logo_file', 'site_logo_file', 'date_format_custom', 'time_format_custom', 'vat_number', 'cer_file', 'key_file') as $key => $value) {
            $this->organizationSettingsRepository->setKey($key, $value);
        }

        $validVAT = 0;
        $validVAT = json_decode($validVAT, true);
        $europian_tax = $this->settingsRepository->getKey('europian_tax');
        if ($europian_tax == 'true' && $request->vat_number != '') {
            if (!isset($validVAT)) {
                flash('Vat Number is not valid.')->error();
                return redirect()->back();
            } else {
                $this->organizationSettingsRepository->setKey('vat_number', $request->vat_number);
            }
        } else {
            $this->organizationSettingsRepository->setKey('vat_number', $request->vat_number);
        }

        return redirect()->back();
    }
}
