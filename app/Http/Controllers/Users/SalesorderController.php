<?php

namespace App\Http\Controllers\Users;

use App\Helpers\Common;
use App\Helpers\SatDoc;
use App\Http\Controllers\Controller;
use App\Http\Requests\SaleorderRequest;
use App\Mail\SendCfdi;
use App\Models\Product;
use App\Models\SaleorderProduct;
use App\Models\Quotation;
use App\Models\Saleorder;
use App\Repositories\CClaveProdServRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\CountryRepository;
use App\Repositories\EmailRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\ProductRepository;
use App\Repositories\QuotationTemplateRepository;
use App\Repositories\SalesOrderRepository;
use App\Repositories\SalesTeamRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Log;

class SalesorderController extends Controller
{

    private $salesOrderRepository;
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

    private $organizationSettingsRepository;

    private $cClaveProdServRepository;
    private $taxRepository;
    private $countryRepository;

    private $settingsRepository;

    protected $user;

    public function __construct(
        SalesOrderRepository $salesOrderRepository,
        UserRepository $userRepository,
        ProductRepository $productRepository,
        CompanyRepository $companyRepository,
        QuotationTemplateRepository $quotationTemplateRepository,
        OptionRepository $optionRepository,
        InvoiceRepository $invoiceRepository,
        EmailRepository $emailRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        CClaveProdServRepository $cClaveProdServRepository,
        TaxRepository $taxRepository,
        CountryRepository $countryRepository,
        SettingsRepository $settingsRepository
    ) {

        parent::__construct();

        $this->salesOrderRepository = $salesOrderRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
        $this->companyRepository = $companyRepository;
        $this->quotationTemplateRepository = $quotationTemplateRepository;
        $this->optionRepository = $optionRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->emailRepository = $emailRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->cClaveProdServRepository = $cClaveProdServRepository;
        $this->taxRepository = $taxRepository;
        $this->countryRepository = $countryRepository;
        $this->settingsRepository = $settingsRepository;

        view()->share('type', 'sales_order');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $title = trans('sales_order.sales_orders');

        $graphics = [];
        for ($i = 11; $i >= 0; --$i) {
            $monthno = now()->subMonth($i)->format('m');
            $month = now()->subMonth($i)->format('M');
            $year = now()->subMonth($i)->format('Y');
            $order = $this->salesOrderRepository->getMonthYear($monthno, $year);
            $graphics[] = [
                'month' => $month,
                'year' => $year,
                'send_salesorder' => $order->where('is_delete_list', 0)
                    ->where('is_invoice_list', 0)->where('status', '!=', trans('sales_order.draft_salesorder'))->count(),
                'draft_salesorder' => $order->where('is_delete_list', 0)->where('status', '=', trans('sales_order.draft_salesorder'))->count(),
                'invoice_list' => $order->where('is_invoice_list', 1)->count(),
                'delete_list' => $order->where('is_delete_list', 1)->count(),
            ];
        }

        return view('user.sales_order.index', compact('title', 'graphics'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['sales_orders.write'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $title = trans('sales_order.new');
        
        return view('user.sales_order.create', compact('title'));
    }

    public function store(SaleorderRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $saleorder = $this->salesOrderRepository->updateOrCreateSalesOrder($request->all());
        // if the user saves the saleorder as draft for the first time
        if ($request->input('status') == trans('sales_order.draft_salesorder')) {
            return redirect('sales_order/draft_saleorders');
        }
        return response()->json([
            'saleorder_id' => $saleorder->id,
        ], 200);
    }
    public function preview(SaleorderRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        DB::beginTransaction();
        $saleorder = $this->salesOrderRepository->updateOrCreateSalesOrder($request->all());
        $template = 'saleorder_template.' . config('settings.saleorder_template');
        $pdf = SatDoc::generatePdf($saleorder, $template);
        DB::rollBack();

        $base64Pdf = 'data:application/pdf;base64,'.base64_encode($pdf->stream());

        return response()->json([
        'base64Pdf' => $base64Pdf,
    ], 200);
    }

    public function edit(Saleorder $saleorder)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['sales_orders.write'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        if (!$saleorder) {
            abort(404);
        }
        $saleorder->load('products', 'taxes');
        $title = trans('sales_order.edit') . $saleorder->sale_number;
        return view('user.sales_order.edit', compact('title', 'saleorder'));
    }

    public function update(SaleorderRequest $request, Saleorder $saleorder)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if ($request->input('status') == trans('quotation.draft_quotation')) {
            $this->salesOrderRepository->updateOrCreateSalesOrder($request->all(), $saleorder->id);
            return redirect('sales_order/draft_saleorders');
        }

        $saleorder = $this->salesOrderRepository->updateOrCreateSalesOrder($request->all(), $saleorder->id);

        return response()->json([
            'saleorder_id' => $saleorder->id,
        ], 200);
    }

    public function show(Saleorder $saleorder)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        if (!$saleorder) {
            abort(404);
        }
        $this->emailRecipients($saleorder->company_id);
        $title = trans('sales_order.show');
        $action = trans('action.show');

        return view('user.sales_order.show', compact('title', 'saleorder', 'action'));
    }

    public function delete($saleorder)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.delete'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $saleorder = $this->salesOrderRepository->getAll()->find($saleorder);
        $title = trans('sales_order.delete');
        return view('user.sales_order.delete', compact('title', 'saleorder'));
    }

    public function makeSaleOrderQuotation($id)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $quotation = Quotation::find($id);
        $quotation->products;
        $quotation->taxes;
        if (!$quotation) {
            abort(404);
        }
        $saleorder = $quotation;
        $saleorder->id = "";
        $title = "Nueva";
        
        return view('user.sales_order.create', compact('title', 'saleorder'));
    }
    
    public function destroy($saleorder)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.delete'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $saleorder = $this->salesOrderRepository->find($saleorder);
        foreach ($saleorder->products as $key => $saleOrderProduct) {
            $saleorder_qty = $saleOrderProduct->pivot->quantity;
            $p = Product::where('id', $saleOrderProduct->id)->get();
            $p[0]['quantity_available'] = $p[0]['quantity_available'] + $saleorder_qty;
            $p[0]['quantity_on_hand'] = $p[0]['quantity_on_hand'] + $saleorder_qty;
            $p[0]->save();
        }
        $saleorder->update(['is_delete_list' => 1]);
        return redirect('sales_order');
    }

    public function data()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.read'])) && $this->user->orgRole=='staff') {
            return redirect('dashboard');
        }
        $sales = $this->salesOrderRepository->getAll()
            ->sortByDesc('id')
            ->map(
                function ($saleorder){

                    return [
                        "responsive_id" => null,
                        'saleorder_id' => $saleorder->id,
                        'sale_number' => "$saleorder->sale_serie $saleorder->sale_number",
                        'sale_pay_method' => $saleorder->payment_method,
                        'saleorder_status' => $saleorder->is_delete_list == 1 ? "Cancelada" : "Activa",
                        'issued_date' => $saleorder->date,
                        'sat_name' => $saleorder->companies->sat_name ?? "",
                        'sat_rfc' => $saleorder->companies->sat_rfc ?? "",
                        'company_email' => $saleorder->companies->email ?? "",
                        'total' => "$saleorder->final_price $saleorder->currency",
                        "avatar" => "",
                        'balance' => $saleorder->unpaid_amount,
                        'due_date' => $saleorder->due_date,
                    ];                   
                }
            );

        return DataTables::of($sales)->make();         
    }

    public function draftIndex()
    {
        $title = trans('sales_order.draft_salesorder');
        return view('user.sales_order.draft_salesorders', compact('title'));
    }
    public function draftSalesOrders()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $sales = $this->salesOrderRepository->draftedSalesorder()
            ->sortByDesc('id')
            ->map(
                function ($saleorder){

                    return [
                        "responsive_id" => null,
                        'saleorder_id' => $saleorder->id,
                        'sale_number' => "$saleorder->sale_serie $saleorder->sale_number",
                        'sale_pay_method' => $saleorder->payment_method,
                        'saleorder_status' => $saleorder->is_delete_list == 1 ? "Cancelada" : "Activa",
                        'issued_date' => $saleorder->date,
                        'sat_name' => $saleorder->companies->sat_name ?? "",
                        'sat_rfc' => $saleorder->companies->sat_rfc ?? "",
                        'company_email' => $saleorder->companies->email ?? "",
                        'total' => "$saleorder->final_price $saleorder->currency",
                        "avatar" => "",
                        'due_date' => $saleorder->due_date,
                    ];                   
                }
            );

        return DataTables::of($sales)->make();   

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

    public function downloadPdf(Saleorder $saleorder)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $saleorder->organization_id) {
            return redirect('dashboard');
        }
        $template = 'saleorder_template.' . config('settings.saleorder_template');
        $pdf = SatDoc::generatePdf($saleorder, $template);
        $filename = trans('saleorder.saleorder') . '-' . $saleorder->sale_number;

        return $pdf->download($filename.'.pdf');
    }

    public function printQuot(Saleorder $saleorder)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $saleorder->organization_id) {
            return redirect('dashboard');
        }
        $template = 'saleorder_template.' . config('settings.saleorder_template');
        $pdf = SatDoc::generatePdf($saleorder, $template);
        return $pdf->stream();
    }

    public function ajaxCreatePdf($saleorder_id)
    {
        $organization = $this->userRepository->getOrganization();
        $organizationSettings = $this->organizationSettingsRepository->getAll();

        $saleorder = $this->salesOrderRepository->find($saleorder_id);

        $company = $this->companyRepository->find($saleorder->company_id);

        //se agregan valores de la organizacion / emisor del comprobante
        $saleorder->sat_name = $organizationSettings['sat_name'];
        $saleorder->sat_rfc = $organizationSettings['sat_rfc'];
        $saleorder->full_address = $this->organizationSettingsRepository->getFullAddress();
        $saleorder->zip_code = $organizationSettings['zip_code'];
        $saleorder->fiscal_regimen = $organizationSettings['fiscal_regimen'];
        $saleorder->email = $organization->email;
        $saleorder->phone = $organizationSettings['phone'];

        // this is to determine if the organization address will be included
        $saleorder->add_address = isset($organizationSettings['print_address']) ? $organizationSettings['print_address'] : 'false';

        //agregar valores completos con su llave
        $saleorder->payment_method = $this->getMetodoPago($saleorder->payment_method);
        $saleorder->payment_type = $this->getFormaPago($saleorder->payment_type);
        $saleorder->cfdi_use = $this->getUsoCfdi($saleorder->cfdi_use);

        $saleorder->taxes = $this->groupTaxesWithFormat($saleorder->taxes);

        $saleorder_template = config('settings.saleorder_template');
        $filename = trans('saleorder.saleorder') . '-' . $saleorder->sale_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'portrait');
        $pdf->loadView('saleorder_template.' . $saleorder_template, compact('saleorder'));

        return '$pdf->stream();';
    }

    public function sendSaleorder(Request $request)
    {

        $settings = $this->settingsRepository->getAll();
        $organizationSettings = $this->organizationSettingsRepository->getAll();
        $email_subject = 'Nota de Venta';
        $to_company = $request->email;
        $email_body = 'Hola, esta es una Nota de Venta de ' . $organizationSettings['sat_name'];
        $message_body = Common::parse_template($email_body);

        $saleorder = $this->salesOrderRepository->find($request->id);
        $pdf_file = trans('sales_order.sales_order') . '-' . $saleorder->sale_number . '.pdf';
        $pdf_content = $this->printQuot($saleorder);
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
                    'pdf_content' => $pdf_content,
                ]));
            }
            echo "Nota de Venta enviada";
        } else {
            echo "La Nota de Venta no pude ser enviada";
        }
    }

    private function generateParams()
    {
        $this->user = $this->getUser();

        $organization = $this->userRepository->getOrganization();

        $products = $this->productRepository->orderBy('id', 'desc')->getAll();

        $product_types = $this->optionRepository->getAll()
            ->where('category', 'product_type')->pluck('title', 'value')->prepend(trans('product.product_type'), '');

        $qtemplates = $this->quotationTemplateRepository->getAll()->pluck('quotation_template', 'id')->prepend(trans('quotation.select_template'), '');

        $prodservs = $this->cClaveProdServRepository->orderBy('id', 'desc')->getAll();

        $companies = $this->companyRepository->orderBy('name', 'asc')->getAll()
            ->map(function ($company) {

                return [
                    'name' => $company->sat_rfc . ' - ' . $company->sat_name . ' / ' . $company->name,
                    'id' => $company->id,
                ];
            })
            ->pluck('name', 'id')->prepend(trans('sales_order.company_id'), '');

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
        view()->share('products', $products);
        view()->share('qtemplates', $qtemplates);
        view()->share('companies', $companies);
        view()->share('payment_term1', $payment_term1);
        view()->share('payment_term2', $payment_term2);
        view()->share('payment_term3', $payment_term3);
        view()->share('countries', $countries);
        view()->share('product_types', $product_types);
        view()->share('taxes', $taxes);
        view()->share('prodservs', $prodservs);
        view()->share('org_zip_code', $org_zip_code);

        /*=== ===Getting the current saleorder number === ===*/
        $saleorder = $this->salesOrderRepository->withAll()->count();
        if (0 == $saleorder) {
            $total_fields = 0;
            $start_number = (int) config('settings.sales_start_number');
        } else {
            $total_fields = $this->salesOrderRepository->withAll()->last()->sale_number;
        }

        $current_number = ((isset($start_number) ? $start_number - 1 : 0) + (isset($total_fields) ? $total_fields : 0) + 1);

        $sale_number = $current_number;
        $sale_serie = config('settings.sales_prefix');

        /*=== ===End of getting the current saleorder number === ===*/

        /*=== === === ===*/
        $sales_tax = $this->organizationSettingsRepository->getKey('sales_tax');

        view()->share('sales_tax', isset($sales_tax) ? floatval($sales_tax) : 1);
        view()->share('sale_number', $sale_number);
        view()->share('sale_serie', $sale_serie);
    }
    private function emailRecipients($company_id)
    {
        $email_recipients = $this->companyRepository->all()->where('id', $company_id)->pluck('name', 'id')->prepend(trans('quotation.company_id'), '');
        view()->share('email_recipients', $email_recipients);
    }

    private function getUsoCfdi($id)
    {

        $path = base_path('resources/assets/sat_catalog/c_UsoCFDI.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }

    private function getMetodoPago($id)
    {

        $path = base_path('resources/assets/sat_catalog/c_MetodoPago.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }

    private function getFormaPago($id)
    {

        $path = base_path('resources/assets/sat_catalog/c_FormaPago.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }

    private function groupTaxesWithFormat($input){
        $output = Array();

        foreach ($input as $value) {
            $output_element = &$output[$value->name
                                . "_" . $value->factor_type
                                . "_" . $value->percentage
                                . "_" . $value->tax_type
                                ]; 
            $output_element['name'] = $value->name;
            $output_element['factor_type'] = $value->factor_type;
            $output_element['percentage'] = $value->percentage;
            $output_element['tax_type'] = $value->tax_type;
            
            !isset($output_element['tax_amount']) && $output_element['tax_amount'] = 0;
            
            $tax_amount = SaleorderProduct::find($value->pivot->sale_order_product_id)->total * $value->percentage;            

            $output_element['tax_amount'] += $tax_amount;
        }
        return array_values($output);
    }

    public function reuse(Saleorder $saleorder)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['sales_orders.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $saleorder->load('products', 'taxes');
        if (!$saleorder) {
            abort(404);
        }
        
        $title = trans('sales_order.reuse') . $saleorder->sale_number;
        $saleorder->sale_number = "";
        $saleorder->id = "";
        return view('user.sales_order.edit', compact('title', 'saleorder'));
    }

    public function convertToInvoice(Saleorder $saleorder)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['sales_orders.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$saleorder) {
            abort(404);
        }
        return redirect("/invoice/create_from_saleorder/$saleorder->id");
    }
}
