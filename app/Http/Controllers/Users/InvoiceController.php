<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Common;
use App\Helpers\SatDoc;
use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceMailRequest;
use App\Http\Requests\InvoiceRequest;
use App\Mail\SendCfdi;
use App\Mail\SendQuotation;
use App\Models\City;
use App\Models\Country;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Saleorder;
use App\Models\State;
use App\Repositories\CClaveProdServRepository;
use App\Repositories\CompanyBanksRepository;
use App\Repositories\CompanyContactsRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CountryRepository;
use App\Repositories\EmailRepository;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use CfdiUtils\Internals\TemporaryFile;
use DataTables;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use SoapClient;
use SOAPHeader;
// Invoice Cancellation
use PhpCfdi\Credentials\Credential;
use PhpCfdi\XmlCancelacion\Capsules\Cancellation;
use PhpCfdi\XmlCancelacion\Credentials;
use PhpCfdi\XmlCancelacion\Models\CancelDocument;
use PhpCfdi\XmlCancelacion\Models\CancelDocuments;
use PhpCfdi\XmlCancelacion\Signers\DOMSigner;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    /**
     * @var CompanyRepository
     */
    private $companyRepository;
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var ProductRepository
     */
    private $productRepository;
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
    private $emailRepository;
    private $organizationSettingsRepository;
    private $settingsRepository;
    private $invoicePaymentRepository;
    protected $user;

    /**
     * InvoiceController constructor.
     *
     * @param CompanyRepository           $companyRepository
     * @param InvoiceRepository           $invoiceRepository
     * @param UserRepository              $userRepository
     * @param QuotationRepository         $quotationRepository
     * @param SalesTeamRepository         $salesTeamRepository
     * @param ProductRepository           $productRepository
     * @param QuotationTemplateRepository $quotationTemplateRepository
     * @param OptionRepository            $optionRepository
     * @param CClaveProdServRepository    $cClaveProdServRepository
     * @param TaxRepository               $taxRepository
     * @param CountryRepository           $countryRepository
     */
    public function __construct(
        CompanyRepository $companyRepository,
        InvoiceRepository $invoiceRepository,
        UserRepository $userRepository,
        QuotationRepository $quotationRepository,
        SalesTeamRepository $salesTeamRepository,
        ProductRepository $productRepository,
        QuotationTemplateRepository $quotationTemplateRepository,
        OptionRepository $optionRepository,
        EmailRepository $emailRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        SettingsRepository $settingsRepository,
        InvoicePaymentRepository $invoicePaymentRepository,
        CClaveProdServRepository $cClaveProdServRepository,
        TaxRepository $taxRepository,
        CountryRepository $countryRepository,
        CompanyContactsRepository $companyContactsRepository,
        CompanyBanksRepository $companyBanksRepository
    ) {
        parent::__construct();

        $this->companyRepository = $companyRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->userRepository = $userRepository;
        $this->quotationRepository = $quotationRepository;
        $this->salesTeamRepository = $salesTeamRepository;
        $this->productRepository = $productRepository;
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->optionRepository = $optionRepository;
        $this->emailRepository = $emailRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->settingsRepository = $settingsRepository;
        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->cClaveProdServRepository = $cClaveProdServRepository;
        $this->taxRepository = $taxRepository;
        $this->countryRepository = $countryRepository;
        $this->companyContactsRepository = $companyContactsRepository;
        $this->companyBanksRepository = $companyBanksRepository;

        view()->share('type', 'invoice');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = trans('invoice.invoices');

        // $this->invoicesData();
        return view('user.invoice.index', compact('title'));
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = "Nueva Factura CFDI 4.0";
        

        return view('user.invoice.create', compact('title'));
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param InvoiceRequest|Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(InvoiceRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        // if the user saves the invoice as draft for the first time
        if ($request->input('status') == trans('invoice.draft_invoice')) {
            $invoice = $this->invoiceRepository->updateOrCreateInvoice($request->all());
            return redirect('invoice/draft_invoices');
        }

        DB::beginTransaction();
        $invoice = $this->invoiceRepository->updateOrCreateInvoice($request->all());
        $creator = SatDoc::getCfdiCreator40($invoice);
        $response = $this->invoiceRepository->timbrarCfdi40($creator, $invoice);
        //if response returns json string, it means it contains an error message
        if (!is_array($response)) {
            DB::rollBack();
            //return error message to display it in the view
            return $response;
        }

        DB::commit();

        return response()->json([
            'state' => $response[0], //$state
            'invoice_id' => $invoice->id,
            'pdf_file' => '',
        ], 200);
    }   
    public function preview(InvoiceRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        DB::beginTransaction();
        $invoice = $this->invoiceRepository->updateOrCreateInvoice($request->all());
        $template = 'invoice_template.' . config('settings.invoice_template');
        $pdf = SatDoc::generatePdf($invoice, $template);
        DB::rollBack();

        $base64Pdf = 'data:application/pdf;base64,'.base64_encode($pdf->stream());

        return response()->json([
        'base64Pdf' => $base64Pdf,
    ], 200);
    }  
    public function previewInvoice(Invoice $invoice, $template, $color)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $template = 'invoice_template.' . config('settings.invoice_template');
        $pdf = SatDoc::generatePdf($invoice, $template);

        return $pdf->stream();

    }
    public function reuse(Invoice $invoice)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $invoice->load('products', 'taxes');
        if (!$invoice) {
            abort(404);
        }
        $title = trans('invoice.reuse') . $invoice->invoice_number;
        $invoice->invoice_number = "";
        $invoice->id = "";
        return view('user.invoice.edit', compact('title', 'invoice'));
    }
    public function edit(Invoice $invoice)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $invoice->load('products', 'taxes');
        if (!$invoice) {
            abort(404);
        }
        $title = trans('invoice.edit') . ' ' . $invoice->invoice_number;
        return view('user.invoice.edit', compact('title', 'invoice'));
    }
    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if ($request->input('status') == trans('invoice.draft_invoice')) {
            $this->invoiceRepository->updateOrCreateInvoice($request->all(), $invoice->id);
            return redirect('invoice/draft_invoices');
        }

        DB::beginTransaction();
        $invoice = $this->invoiceRepository->updateOrCreateInvoice($request->all(), $invoice->id);
        $creator = SatDoc::getCfdiCreator40($invoice);
        $response = $this->invoiceRepository->timbrarCfdi40($creator, $invoice);
        //if response returns json string, it means it contains an error message
        if (!is_array($response)) {
            DB::rollBack();
            //return error message to display it in the view
            return $response;
        }

        DB::commit();

        return response()->json([
            'state' => $response[0], //$state
            'invoice_id' => $invoice->id,
            'pdf_file' => '',
        ], 200);
    }
    public function show(Invoice $invoice)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$invoice) {
            abort(404);
        }
        $this->emailRecipients($invoice->company_id);
        $title = 'Ver factura';
        $action = trans('action.show');

        return view('user.invoice.show', compact('title', 'invoice', 'action'));
    }
    public function delete(Invoice $invoice)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if ($invoice->status != trans('invoice.draft_invoice')) {
            abort(404);
        }
        $title = trans('invoice.delete');
        $action = trans('action.delete');

        return view('user.invoice.delete', compact('title', 'invoice', 'action'));
    }

    public function destroy(Invoice $invoice)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if ($invoice->status != trans('invoice.draft_invoice')) {
            abort(404);
        }
        $invoice->delete();

        return redirect('invoice/draft_invoices');
    }
    public function cancel(Invoice $invoice)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        
        $title = 'Cancelar factura';

        return view('user.invoice.cancel', compact('title', 'invoice'));
    }
    public function confirmCancel(Invoice $invoice, Request $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$invoice) {
            abort(404);
        } else {
            DB::beginTransaction();
            foreach ($invoice->products as $key => $invoiceProduct) {
                $invoice_qty = $invoiceProduct->pivot->quantity;
                $p = Product::where('id', $invoiceProduct->id)->get();
                $p[0]['quantity_available'] = $p[0]['quantity_available'] + $invoice_qty;
                $p[0]->save();
            }
            // Enviar peticion de cancelacion.
            $response = SatDoc::cancelCfdi($invoice, $request->motivo, $request->folioSustitucion);
            if ($response == '0') {
                // Si la respuesta es state=0, consultar el estado (getacuse) del uuid cancelado.
                $xmlbase64 = SatDoc::getAcuseCancelacion($invoice);
    
                // Guardar el xmlbase64 del acuse cancelacion y estado de invoice en BD a cancelada.
                $invoice->update(['is_delete_list' => 1, 'acuse_xml' => $xmlbase64]);
                DB::commit();
            } else {
                DB::rollBack();
                return $response;
            }

            return redirect('invoice/' . $invoice->id . '/show');
        }
        
    }
    public function data()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $invoices = $this->invoiceRepository->getAll()
            ->sortByDesc('id')
            ->map(
                function ($invoice) {

                    if ($invoice->is_delete_list == 1) {
                        $invoice_status = "Cancelled";
                    } elseif ($invoice->unpaid_amount == 0) {
                        $invoice_status = "Paid";
                    } else {
                        $invoice_status = "Pending";
                    }

                    return [
                        "responsive_id" => null,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => "#$invoice->invoice_number",
                        'invoice_pay_method' => $invoice->payment_method,
                        'invoice_status' => $invoice_status,
                        'issued_date' => $invoice->invoice_date,
                        'sat_name' => $invoice->companies->sat_name ?? "",
                        'sat_rfc' => $invoice->companies->sat_rfc ?? "",
                        'company_email' => $invoice->companies->email ?? "",
                        'total' => "$invoice->final_price $invoice->currency",
                        "avatar" => "",
                        'balance' => $invoice->unpaid_amount,
                        'due_date' => $invoice->due_date,
                    ];
                }
            );

        return DataTables::of($invoices)->make();
    }
    public function draftIndex()
    {
        $title = trans('invoice.draft_invoices');
        return view('user.invoice.draft_invoices', compact('title'));
    }
    public function draftInvoices()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $invoices = $this->invoiceRepository->draftedInvoice()
            ->sortByDesc('id')
            ->map(
                function ($invoice) {

                    return [
                        "responsive_id" => null,
                        'invoice_id' => $invoice->id,
                        'invoice_number' => "$invoice->invoice_serie $invoice->invoice_number",
                        'invoice_pay_method' => $invoice->payment_method,
                        'issued_date' => $invoice->invoice_date,
                        'sat_name' => $invoice->companies->sat_name ?? "",
                        'sat_rfc' => $invoice->companies->sat_rfc ?? "",
                        'company_email' => $invoice->companies->email ?? "",
                        'total' => "$invoice->final_price $invoice->currency",
                        "avatar" => "",
                        'balance' => $invoice->unpaid_amount,
                        'due_date' => $invoice->due_date,
                    ];
                }
            );

        return DataTables::of($invoices)->make();
    }
    public function createFromQuotation(Quotation $quotation)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $quotation->products;
        $quotation->taxes;
        if (!$quotation) {
            abort(404);
        }
        $invoice = $quotation;
        $invoice->id = "";
        $title = "Nueva";

        return view('user.invoice.create', compact('title', 'invoice'));
    }
    public function createFromSaleorder(Saleorder $saleorder)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $saleorder->products;
        $saleorder->taxes;
        if (!$saleorder) {
            abort(404);
        }
        
        $invoice = $saleorder;
        $invoice->id = "";
        $title = "Nueva";

        return view('user.invoice.create', compact('title', 'invoice'));
    }
    public function makeInvoice($id)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $saleorder = Saleorder::find($id);
        $saleorder->products;
        $saleorder->taxes;
        if (!$saleorder) {
            abort(404);
        }
        $invoice = $saleorder;
        $invoice->id = "";
        $title = "Nueva";

        return view('user.invoice.create', compact('title', 'invoice'));
    }
    public function printQuot(Invoice $invoice)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $invoice->organization_id) {
            return redirect('dashboard');
        }
        $template = 'invoice_template.' . config('settings.invoice_template');
        $pdf = SatDoc::generatePdf($invoice, $template);
        return $pdf->stream();
    }
    public function downloadPdf(Invoice $invoice)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $invoice->organization_id) {
            return redirect('dashboard');
        }
        $template = 'invoice_template.' . config('settings.invoice_template');
        $pdf = SatDoc::generatePdf($invoice, $template);
        $filename = trans('invoice.invoice') . '-' . $invoice->invoice_number;
        
        return $pdf->download($filename . '.pdf');
    }
    public function downloadXml(Invoice $invoice)
    {
        $content = base64_decode($invoice->cfdi_xml);
        $filename = trans('invoice.invoice') . '-' . $invoice->invoice_number;
        return response()->attachment($content, $filename . '.xml');
    }
    /**
     * @param InvoiceMailRequest $request
     */
    public function sendInvoice(InvoiceMailRequest $request)
    {
        $settings = $this->settingsRepository->getAll();
        $email_subject = $request->email_subject;
        $to_company = $this->companyRepository->all()->where('id', $request->recipients);
        $email_body = $request->message_body;
        $message_body = Common::parse_template($email_body);
        $invoice_pdf = $request->invoice_pdf;

        $site_email = $settings['site_email'];

        if (!empty($to_company) && false === !filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
            foreach ($to_company as $item) {
                if (false === !filter_var($item->email, FILTER_VALIDATE_EMAIL)) {

                    // try{
                    Mail::to($item->email)->send(new SendQuotation([
                        'from' => $site_email,
                        'subject' => $email_subject,
                        'message_body' => $message_body,
                        'quotation_pdf' => $invoice_pdf,
                    ]));

                    // } catch(\Swift_TransportException $e) {

                    //     Log::info($e);

                    // }
                }
                $this->emailRepository->create([
                    'assign_customer_id' => $item->id,
                    'from' => $this->userRepository->getOrganization()->id,
                    'to' => $item->email,
                    'subject' => $email_subject,
                    'message' => $message_body,
                ]);
            }
            echo '<div class="alert alert-success">' . trans('invoice.success') . '</div>';
        } else {
            echo '<div class="alert alert-danger">' . trans('invoice.error') . '</div>';
        }
    }
    public function sendCfdi(Request $request)
    {
        $settings = $this->settingsRepository->getAll();
        $organizationSettings = $this->organizationSettingsRepository->getAll();
        $email_subject = 'Factura Electrónica';
        $to_company = $request->email;
        $email_body = 'Hola, esta es una Factura Electrónica de ' . $organizationSettings['sat_name'];
        $message_body = Common::parse_template($email_body);

        $invoice = $this->invoiceRepository->find($request->id);
        $pdf_file = trans('invoice.invoice') . '-' . $invoice->invoice_number . '.pdf';
        $pdf_content = $this->printQuot($invoice);
        $xml_content = base64_decode($invoice->cfdi_xml);
        $xml_file = trans('invoice.invoice') . '-' . $invoice->invoice_number . '.xml';

        $site_email = $settings['site_email'];

        if (!empty($to_company) && false === !filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
            if (false === !filter_var($to_company, FILTER_VALIDATE_EMAIL)) {

                try {
                    Mail::to($to_company)->send(new SendCfdi([
                        'from' => $site_email,
                        'subject' => $email_subject,
                        'message_body' => $message_body,
                        'quotation_pdf' => $pdf_file,
                        'quotation_xml' => $xml_file,
                        'xml_content' => $xml_content,
                        'pdf_content' => $pdf_content,
                    ]));
                } catch (Exception $e) {
                    echo "La Factura no pude ser enviada";
                }
            }
            // $this->emailRepository->create([
            //     'assign_customer_id' => $item->id,
            //     'from' => $this->userRepository->getOrganization()->id,
            //     'to' => $to_company,
            //     'subject' => $email_subject,
            //     'message' => $message_body,
            // ]);

            echo "Factura enviada";
        } else {
            echo "La Factura no pude ser enviada";
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

        $companies = $this->companyRepository->getAll()
            ->sortBy('sat_name')
            ->map(function ($company) {

                return [
                    'name' => $company->sat_rfc . ' - ' . $company->sat_name . ' / ' . $company->name,
                    'id' => $company->id,
                ];
            })
            ->pluck('name', 'id');

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
        view()->share('companies', $companies);
        view()->share('payment_term1', $payment_term1);
        view()->share('payment_term2', $payment_term2);
        view()->share('payment_term3', $payment_term3);

        /*=== ===Getting the current invoice number === ===*/
        $organization->loadCount('invoices');
        if ($organization->invoices_count) {
            $total_fields = $this->invoiceRepository->getAll()->last()->invoice_number;
        } else {
            $total_fields = 0;
            $start_number = (int) config('settings.invoice_start_number');
        }

        $current_number = ((isset($start_number) ? $start_number - 1 : 0) + (isset($total_fields) ? $total_fields : 0) + 1);

        $invoice_number = $current_number;
        $invoice_serie = config('settings.invoice_prefix');

        /*=== ===End of getting the current invoice number === ===*/
        view()->share('invoice_number', $invoice_number);
        view()->share('invoice_serie', $invoice_serie);
        /*=== === === ===*/
    }
    private function emailRecipients($company_id)
    {
        $email_recipients = $this->companyRepository->all()->where('id', $company_id)->pluck('name', 'id')->prepend(trans('quotation.company_id'), '');
        view()->share('email_recipients', $email_recipients);
    }    
    
}
