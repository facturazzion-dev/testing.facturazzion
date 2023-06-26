<?php

namespace App\Http\Controllers\Users;

use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Helpers\Common;
use App\Helpers\SatDoc;
use App\Http\Controllers\Controller;
use App\Http\Requests\QuotationRequest;
use App\Mail\SendCfdi;
use App\Models\Product;
use App\Repositories\CompanyRepository;
use App\Repositories\CompanyContactsRepository;
use App\Repositories\CompanyBanksRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\EmailRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\UserRepository;
use App\Repositories\CClaveProdServRepository;
use App\Repositories\TaxRepository;
use App\Repositories\CountryRepository;
use App\Repositories\SettingsRepository;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * @var QuotationRepository
     */
    private $quotationRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var QuotationTemplateRepository
     */
    private $quotationTemplateRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    /**
     * @var CClaveProdServRepository
     */
    private $cClaveProdServRepository;
    /**
     * @var TaxRepository
     */
    private $taxRepository;
    /**
     * @var CountryRepository
     */
    private $countryRepository;

    private $settingsRepository;

    private $salesOrderRepository;

    private $organizationSettingsRepository;

    protected $user;

    /**
     * QuotationController constructor.
     *
     * @param QuotationRepository         $quotationRepository
     * @param UserRepository              $userRepository
     * @param SalesTeamRepository         $salesTeamRepository
     * @param ProductRepository           $productRepository
     * @param CompanyRepository           $companyRepository
     * @param QuotationTemplateRepository $quotationTemplateRepository
     * @param OptionRepository            $optionRepository
     * @param CClaveProdServRepository    $cClaveProdServRepository
     * @param TaxRepository               $taxRepository
     * @param CountryRepository           $countryRepository
     * @param CompanyContactsRepository   $companyContactsRepository
     * @param CompanyBanksRepository      $companyBanksRepository     
     * @param SettingsRepository          $settingsRepository     
     */

    public function __construct(
        QuotationRepository $quotationRepository,
        UserRepository $userRepository,
        SalesTeamRepository $salesTeamRepository,
        ProductRepository $productRepository,
        CompanyRepository $companyRepository,
        QuotationTemplateRepository $quotationTemplateRepository,
        OptionRepository $optionRepository,
        OrganizationRepository $organizationRepository,
        CustomerRepository $customerRepository,
        SalesOrderRepository $salesOrderRepository,
        InvoiceRepository $invoiceRepository,
        EmailRepository $emailRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        CClaveProdServRepository $cClaveProdServRepository,
        TaxRepository $taxRepository,
        CountryRepository $countryRepository,
        CompanyContactsRepository $companyContactsRepository,
        CompanyBanksRepository $companyBanksRepository,
        SettingsRepository $settingsRepository
    ) {
        $this->quotationRepository = $quotationRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->companyRepository = $companyRepository;
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->optionRepository = $optionRepository;
        $this->organizationRepository = $organizationRepository;
        $this->customerRepository = $customerRepository;
        $this->salesOrderRepository = $salesOrderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->emailRepository = $emailRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->cClaveProdServRepository = $cClaveProdServRepository;
        $this->taxRepository = $taxRepository;
        $this->countryRepository = $countryRepository;
        $this->companyContactsRepository = $companyContactsRepository;
        $this->companyBanksRepository = $companyBanksRepository;
        $this->settingsRepository = $settingsRepository;
        view()->share('type', 'quotation');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $title = trans('quotation.quotations');

        $graphics = [];
        for ($i = 11; $i >= 0; --$i) {
            $monthno = now()->subMonth($i)->format('m');
            $month = now()->subMonth($i)->format('M');
            $year = now()->subMonth($i)->format('Y');
            $quotation = $this->quotationRepository->getMonthYear($monthno, $year);
            $graphics[] = [
                'month' => $month,
                'year' => $year,
                'send_quotation' => $quotation->where('is_delete_list', 0)
                    ->where('is_converted_list', 0)->where('is_quotation_invoice_list', 0)->where('status', '!=', trans('quotation.draft_quotation'))->count(),
                'draft_quotation' => $quotation->where('is_delete_list', 0)->where('status', '=', trans('quotation.draft_quotation'))->count(),
                'salesorder_list' => $quotation->where('is_converted_list', 1)->count(),
                'invoice_list' => $quotation->where('is_quotation_invoice_list', 1)->count(),
                'delete_list' => $quotation->where('is_delete_list', 1)->count(),
            ];
        }

        return view('user.quotation.index', compact('title', 'graphics'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['quotations.write'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $title = 'Nueva Cotización';
        
        return view('user.quotation.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param QuotationRequest|Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(QuotationRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $quotation = $this->quotationRepository->updateOrCreateQuotation($request->all());
        // if the user saves the quotation as draft for the first time
        if ($request->input('status') == trans('quotation.draft_quotation')) {
            return redirect('quotation/draft_quotations');
        }
        return response()->json([
            'quotation_id' => $quotation->id,
        ], 200);
    }
    public function preview(QuotationRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        DB::beginTransaction();
        $quotation = $this->quotationRepository->updateOrCreateQuotation($request->all());
        $template = 'quotation_template.' . config('settings.quotation_template');
        $pdf = SatDoc::generatePdf($quotation, $template);
        DB::rollBack();

        $base64Pdf = 'data:application/pdf;base64,' . base64_encode($pdf->stream());

        return response()->json([
            'base64Pdf' => $base64Pdf,
        ], 200);
    }
    public function edit(Quotation $quotation)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['quotations.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$quotation) {
            abort(404);
        }
        $quotation->load('products', 'taxes');
        
        $title = trans('quotation.edit') . $quotation->quotation_number;
        return view('user.quotation.edit', compact('title', 'quotation'));
    }

    public function update(QuotationRequest $request, Quotation $quotation)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if ($request->input('status') == trans('quotation.draft_quotation')) {
            $this->quotationRepository->updateOrCreateQuotation($request->all(), $quotation->id);
            return redirect('quotation/draft_quotations');
        }

        $quotation = $this->quotationRepository->updateOrCreateQuotation($request->all(), $quotation->id);

        return response()->json([
            'quotation_id' => $quotation->id,
        ], 200);
    }

    public function show(Quotation $quotation)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        if (!$quotation) {
            abort(404);
        }
        $this->emailRecipients($quotation->company_id);
        $title = trans('quotation.show');
        $action = trans('action.show');

        return view('user.quotation.show', compact('title', 'quotation', 'action'));
    }

    public function delete(Quotation $quotation)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.delete'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        if (!$quotation) {
            abort(404);
        }
        $title = trans('quotation.delete');

        return view('user.quotation.delete', compact('title', 'quotation'));
    }

    public function destroy(Quotation $quotation)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.delete'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        if (!$quotation) {
            abort(404);
        }
        $quotation->update(['is_delete_list' => 1]);
        return redirect('quotation');
    }

    /**
     * @return mixed
     */
    public function data()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $quotations = $this->quotationRepository->getAll()
            ->sortByDesc('id')
            ->map(
                function ($quotation) {

                    return [
                        "responsive_id" => null,
                        'quotation_id' => $quotation->id,
                        'quotation_number' => "$quotation->quotation_serie $quotation->quotation_number",
                        'quotation_pay_method' => $quotation->payment_method,
                        'quotation_status' => $quotation->is_delete_list == 1 ? "Cancelada" : "Activa",
                        'issued_date' => $quotation->date,
                        'sat_name' => $quotation->companies->sat_name ?? "",
                        'sat_rfc' => $quotation->companies->sat_rfc ?? "",
                        'company_email' => $quotation->companies->email ?? "",
                        'total' => "$quotation->final_price $quotation->currency",
                        "avatar" => "",
                        'balance' => $quotation->unpaid_amount,
                        'due_date' => $quotation->due_date,
                    ];
                }
            );

        return DataTables::of($quotations)->make();
    }

    public function draftIndex()
    {
        $title = trans('quotation.draft_quotations');
        return view('user.quotation.draft_quotations', compact('title'));
    }

    public function draftQuotations()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $quotations = $this->quotationRepository->draftedQuotation()
            ->sortByDesc('id')
            ->map(
                function ($quotation) {

                    return [
                        "responsive_id" => null,
                        'quotation_id' => $quotation->id,
                        'quotation_number' => "$quotation->quotation_serie $quotation->quotation_number",
                        'quotation_pay_method' => $quotation->payment_method,
                        'quotation_status' => $quotation->is_delete_list == 1 ? "Cancelada" : "Activa",
                        'issued_date' => $quotation->date,
                        'sat_name' => $quotation->companies->sat_name ?? "",
                        'sat_rfc' => $quotation->companies->sat_rfc ?? "",
                        'company_email' => $quotation->companies->email ?? "",
                        'total' => "$quotation->final_price $quotation->currency",
                        "avatar" => "",
                        'due_date' => $quotation->due_date,
                    ];
                }
            );

        return DataTables::of($quotations)->make();
    }

    public function confirmSalesOrder(Quotation $quotation)
    {
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();

        if (!$quotation) {
            abort(404);
        }

        $salesOrder = $this->salesOrderRepository->withAll()->count();;
        if ($salesOrder == 0) {
            $total_fields = 0;
        } else {
            $total_fields = $this->salesOrderRepository->withAll()->last()->id;
        }
        $start_number = config('settings.quotation_start_number');
        $sale_no = config('settings.sales_prefix') . ((is_int($start_number) ? $start_number : 0) + (isset($total_fields) ? $total_fields : 0) + 1);

        $saleorder = $this->salesOrderRepository->create([
            'sale_number' => $sale_no,
            'company_id' => $quotation->company_id,
            'date' => date(config('settings.date_format')),
            'exp_date' => $quotation->expire_date,
            'qtemplate_id' => $quotation->qtemplate_id,
            'payment_term' => isset($quotation->payment_term) ? $quotation->payment_term : 0,
            "sales_team_id" => $quotation->sales_team_id,
            'terms_and_conditions' => $quotation->terms_and_conditions,
            'total' => $quotation->total,
            'tax_amount' => $quotation->tax_amount,
            'vat_amount' => $quotation->vat_amount,
            'grand_total' => $quotation->grand_total,
            'discount' => $quotation->discount,
            'final_price' => $quotation->final_price,
            'status' => trans('sales_order.draft_salesorder'),
            'user_id' => $user->id,
            'organization_id' => $organization->id,
            'quotation_id' => $quotation->id,
            'is_delete_list' => 0,
            'is_invoice_list' => 0,
        ]);

        $list = [];
        if (!empty($quotation->products->count() > 0)) {
            foreach ($quotation->products as $key => $item) {
                $temp['quantity'] = $item->pivot->quantity;
                $temp['price'] = $item->pivot->price;
                $list[$item->pivot->product_id] = $temp;
            }
        }
        $saleorder->salesOrderProducts()->attach($list);

        $quotation->update(['is_converted_list' => 1]);

        return redirect('sales_order/draft_salesorders');
    }
    public function ajaxQtemplatesProducts($qtemplate)
    {
        $qtemplateProduct = $this->quotationTemplateRepository->find($qtemplate);
        $templateProduct = [];
        foreach ($qtemplateProduct->qTemplateProducts as $product) {
            $templateProduct[] = $product;
        }
        return $templateProduct;
    }
    public function downloadPdf(Quotation $quotation)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $quotation->organization_id) {
            return redirect('dashboard');
        }
        $template = 'quotation_template.' . config('settings.quotation_template');
        $pdf = SatDoc::generatePdf($quotation, $template);
        $filename = trans('quotation.quotation') . '-' . $quotation->quotation_number;

        return $pdf->download($filename . '.pdf');
    }
    public function printQuot(Quotation $quotation)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $quotation->organization_id) {
            return redirect('dashboard');
        }
        $template = 'quotation_template.' . config('settings.quotation_template');
        $pdf = SatDoc::generatePdf($quotation, $template);
        return $pdf->stream();
    }

    public function ajaxCreatePdf(Quotation $quotation)
    {
        $organization = $this->userRepository->getOrganization();
        $organizationSettings = $this->organizationSettingsRepository->getAll();

        $company = $this->companyRepository->find($quotation->company_id);

        //se agregan valores de la organizacion / emisor del comprobante
        $quotation->sat_name = $organizationSettings['sat_name'];
        $quotation->sat_rfc = $organizationSettings['sat_rfc'];
        $quotation->full_address = $this->organizationSettingsRepository->getFullAddress();
        $quotation->zip_code = $organizationSettings['zip_code'];
        $quotation->fiscal_regimen = $organizationSettings['fiscal_regimen'];
        $quotation->email = $organization->email;
        $quotation->phone = $organizationSettings['phone'];

        // this is to determine if the organization address will be included
        $quotation->add_address = isset($organizationSettings['print_address']) ? $organizationSettings['print_address'] : 'false';

        //agregar valores completos con su llave
        $quotation->payment_method = $this->getMetodoPago($quotation->payment_method);
        $quotation->payment_type = $this->getFormaPago($quotation->payment_type);
        $quotation->cfdi_use = $this->getUsoCfdi($quotation->cfdi_use);

        $quotation->taxes = $this->groupTaxesWithFormat($quotation->taxes);

        $quotation_template = config('settings.quotation_template');
        $filename = trans('quotation.quotation') . '-' . $quotation->quotation_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'portrait');
        $pdf->loadView('quotation_template.' . $quotation_template, compact('quotation'));

        return '$pdf->stream();';
    }

    public function sendQuotation(Request $request)
    {

        $settings = $this->settingsRepository->getAll();
        $organizationSettings = $this->organizationSettingsRepository->getAll();
        $email_subject = 'Cotización';
        $to_company = $request->email;
        $email_body = 'Hola, esta es una Cotización de ' . $organizationSettings['sat_name'];
        $message_body = Common::parse_template($email_body);

        $quotation = $this->quotationRepository->find($request->id);
        $pdf_file = trans('quotation.quotation') . '-' . $quotation->quotation_number . '.pdf';
        $pdf_content = $this->printQuot($quotation);
        // $xml_content = base64_decode($quotation->cfdi_xml);
        // $xml_file = trans('quotation.quotation').'-'.$quotation->quotation_number.'.xml';


        $site_email = $settings['site_email'];

        if (!empty($to_company) && false === !filter_var($site_email, FILTER_VALIDATE_EMAIL)) {

            if (false === !filter_var($to_company, FILTER_VALIDATE_EMAIL)) {

                Mail::to($to_company)->send(new SendCfdi([
                    'from' => $site_email,
                    'subject' => $email_subject,
                    'message_body' => $message_body,
                    'quotation_pdf' => $pdf_file,
                    'quotation_xml' => null,
                    'xml_content' => null,
                    'pdf_content' => $pdf_content
                ]));
            }
            // $this->emailRepository->create([
            //     'assign_customer_id' => $item->id,
            //     'from' => $this->userRepository->getOrganization()->id,
            //     'to' => $to_company,
            //     'subject' => $email_subject,
            //     'message' => $message_body,
            // ]);

            echo "Cotización enviada";
        } else {
            echo "La Cotización no pude ser enviada";
        }
    }

    private function generateParams()
    {
        $this->user = $this->getUser();

        $organization = $this->userRepository->getOrganization();

        $products = $this->productRepository->orderBy('id', 'desc')->getAll();

        $product_types = $this->optionRepository->getAll()
            ->where('category', 'product_type')->pluck('title', 'value')->prepend(trans('product.product_type'), '');

        $prodservs = $this->cClaveProdServRepository->orderBy('id', 'desc')->getAll();

        $qtemplates = $this->quotationTemplateRepository->getAll()->pluck('quotation_template', 'id')->prepend(trans('quotation.select_template'), '');

        $companies = $this->companyRepository->orderBy('name', 'asc')->getAll()
            ->map(function ($company) {

                return [
                    'name' => $company->sat_rfc . ' - ' . $company->sat_name . ' / ' . $company->name,
                    'id' => $company->id,
                ];
            })
            ->pluck('name', 'id')->prepend(trans('quotation.company_id'), '');

        $org_zip_code = $this->organizationSettingsRepository->getKey('zip_code');

        $taxes = $this->taxRepository->getAll();

        $countries = $this->countryRepository->orderBy('name', 'asc')->pluck('name', 'id')->prepend(trans('company.select_country'), '');

        $payment_term1 = config('settings.payment_term1');
        $payment_term2 = config('settings.payment_term2');
        $payment_term3 = config('settings.payment_term3');

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

        $company_fav = $this->companyRepository->getAll()->where('favorite', 1);
        $taxes_fav = $taxes->where('favorite', 1)
            ->map(function ($tax) {
                return $tax->id.'_'.$tax->tax.'_'.$tax->tax_type.'_'.$tax->percentage.'_'.$tax->factor_type;
            })->flatten();

        $product_fav = $this->productRepository->getFavorite();
        if(empty($product_fav)) {
            $product_fav = new Product([
                'sku' => '01010101',
                'clave_sat' => '01010101',
                'clave_unidad_sat' => 'E48',
                'description' => 'No existe en el catálogo',
                'unidad_sat' => 'Unidad de servicio',
                'quantity' => '1',
                'price' => '0',
                'discount' => '0',
                'total_amount' => '0'
            ]);
        }

        view()->share('payment_method', $payment_method);
        view()->share('payment_type', $payment_type);
        view()->share('cfdi_use', $cfdi_use);
        view()->share('company_fav', $company_fav);
        view()->share('taxes_fav', $taxes_fav);
        view()->share('product_fav', $product_fav);
        view()->share('organization', $organization);
        view()->share('countries', $countries);
        view()->share('products', $products);
        view()->share('product_types', $product_types);
        view()->share('taxes', $taxes);
        view()->share('prodservs', $prodservs);
        view()->share('qtemplates', $qtemplates);
        view()->share('org_zip_code', $org_zip_code);
        // view()->share('salesteams', $salesteams);
        view()->share('companies', $companies);
        view()->share('payment_term1', $payment_term1);
        view()->share('payment_term2', $payment_term2);
        view()->share('payment_term3', $payment_term3);

        /*=== ===Getting the current quotation number === ===*/
        $quotation = $this->quotationRepository->withAll()->count();

        if (0 == $quotation) {
            $total_fields = 0;
            $start_number = (int)config('settings.quotation_start_number');
        } else {
            $total_fields = $this->quotationRepository->withAll()->last()->quotation_number;
        }



        $current_number = ((isset($start_number) ? $start_number - 1 : 0) + (isset($total_fields) ? $total_fields : 0) + 1);

        $quotation_number = $current_number;
        $quotation_serie = config('settings.quotation_prefix');

        /*=== ===End of getting the current quotation number === ===*/

        $sales_tax = $this->organizationSettingsRepository->getKey('sales_tax');

        view()->share('sales_tax', isset($sales_tax) ? floatval($sales_tax) : 1);
        view()->share('quotation_number', $quotation_number);
        view()->share('quotation_serie', $quotation_serie);
    }

    private function emailRecipients($company_id)
    {
        $email_recipients = $this->companyRepository->all()->where('id', $company_id)->pluck('name', 'id')->prepend(trans('quotation.company_id'), '');
        view()->share('email_recipients', $email_recipients);
    }

    private function groupTaxesWithFormat($input)
    {
        $output = array();

        foreach ($input as $value) {
            $output_element = &$output[$value->name
                . "_" . $value->factor_type
                . "_" . $value->percentage
                . "_" . $value->tax_type];
            $output_element['name'] = $value->name;
            $output_element['factor_type'] = $value->factor_type;
            $output_element['percentage'] = $value->percentage;
            $output_element['tax_type'] = $value->tax_type;

            !isset($output_element['tax_amount']) && $output_element['tax_amount'] = 0;

            $tax_amount = QuotationProduct::find($value->pivot->quotation_product_id)->total * $value->percentage;

            $output_element['tax_amount'] += $tax_amount;
        }
        return array_values($output);
    }

    public function reuse(Quotation $quotation)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['quotations.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $quotation->load('products', 'taxes');
        if (!$quotation) {
            abort(404);
        }
        $title = trans('quotation.reuse') . $quotation->quotation_number;
        $quotation->quotation_number = "";
        $quotation->id = "";
        return view('user.quotation.edit', compact('title', 'quotation'));
    }

    public function convertToInvoice(Quotation $quotation)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$quotation) {
            abort(404);
        }
        return redirect("/invoice/create_from_quotation/$quotation->id");
    }

    public function convertToSaleOrder(Quotation $quotation)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['quotations.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$quotation) {
            abort(404);
        }
        return redirect("/saleorder/create_from_quotation/$quotation->id");
    }
}