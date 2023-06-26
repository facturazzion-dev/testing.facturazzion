<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\InvoiceReceivePaymentRequest;
use App\Mail\SendCfdi;
use App\Repositories\CompanyRepository;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\OptionRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\UserRepository;
use App\Repositories\SettingsRepository;
use App\Helpers\Common;
use App\Helpers\SatDoc;
use App\Models\Invoice;
use App\Models\InvoiceReceivePayment;
use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\Elements\Pagos20\Pagos;
use CfdiUtils\Utils\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SOAPHeader;
use DataTables;
use Illuminate\Support\Facades\DB;

class InvoicesPaymentController extends Controller
{
    /*user site settings*/
    /**
     * @var InvoicePaymentRepository
     */
    private $invoicePaymentRepository;

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
     * @var OptionRepository
     */
    private $optionRepository;

    private $organizationSettingsRepository;

    private $settingsRepository;

    protected $user;

    public function __construct(
        InvoicePaymentRepository $invoicePaymentRepository,
        CompanyRepository $companyRepository,
        InvoiceRepository $invoiceRepository,
        UserRepository $userRepository,
        OptionRepository $optionRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        SettingsRepository $settingsRepository
    ) {
        parent::__construct();

        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->companyRepository = $companyRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->userRepository = $userRepository;
        $this->optionRepository = $optionRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->settingsRepository = $settingsRepository;
        view()->share('type', 'invoices_payment_log');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $title = trans('invoices_payment_log.invoices_payment_log');

        return view('user.invoices_payment_log.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $title = trans('invoices_payment_log.new');
        return view('user.invoices_payment_log.create', compact('title'));
    }

    public function store(InvoiceReceivePaymentRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        // if the user saves the invoice as draft for the first time
        if ($request->input('status') == trans('invoice.draft_invoice')) {
            $payment = $this->invoicePaymentRepository->updateOrCreatePayment($request->all());
            return redirect('invoices_payment_log');
        }

        DB::beginTransaction();
        $payment = $this->invoicePaymentRepository->updateOrCreatePayment($request->all());
        $creator = SatDoc::getPago20Creator40($payment);
        $response = $this->invoicePaymentRepository->timbrarCfdi40($creator, $payment);
        //if response returns json string, it means it contains an error message
        if (!is_array($response)) {
            DB::rollBack();
            //return error message to display it in the view
            return $response;
        }

        DB::commit();

        return response()->json([
            'state' => $response[0], //$state
            'payment_id' => $payment->id,
            'pdf_file' => '',
        ], 200);
    }

    public function preview(InvoiceReceivePaymentRequest $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        DB::beginTransaction();
        $payment = $this->invoicePaymentRepository->updateOrCreatePayment($request->all());
        $template = 'invoice_payment_template.' . config('settings.invoice_payment_template');
        $pdf = SatDoc::generatePdf2($payment, $template);
        DB::rollBack();

        $base64Pdf = 'data:application/pdf;base64,'.base64_encode($pdf->stream());

        return response()->json([
        'base64Pdf' => $base64Pdf,
    ], 200);
    }
    
    public function edit(InvoiceReceivePayment $payment)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $payment->load('paidInvoices');
        $invoices = $this->invoiceRepository->getAllUnpaidPpdForCompany($payment->company_id);
        $title = 'Editar REP ' . $payment->payment_number;
        return view('user.invoices_payment_log.edit', compact('title', 'payment', 'invoices'));
    }

    public function update(InvoiceReceivePaymentRequest $request, InvoiceReceivePayment $payment)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        // if the user saves the invoice as draft for the first time
        if ($request->input('status') == trans('invoice.draft_invoice')) {
            $payment = $this->invoicePaymentRepository->updateOrCreatePayment($request->all(), $payment->id);
            return redirect('invoices_payment_log');
        }

        DB::beginTransaction();
        $payment = $this->invoicePaymentRepository->updateOrCreatePayment($request->all(), $payment->id);
        $creator = SatDoc::getPago20Creator40($payment);
        $response = $this->invoicePaymentRepository->timbrarCfdi40($creator, $payment);
        //if response returns json string, it means it contains an error message
        if (!is_array($response)) {
            DB::rollBack();
            //return error message to display it in the view
            return $response;
        }

        DB::commit();

        return response()->json([
            'state' => $response[0], //$state
            'payment_id' => $payment->id,
            'pdf_file' => '',
        ], 200);
    }

    public function show(InvoiceReceivePayment $payment)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        if (!$payment) {
            abort(404);
        }
        $title = trans('invoices_payment_log.show');
        $action = trans('action.show');
        return view('user.invoices_payment_log.show', compact('title', 'action', 'payment'));
    }

    public function delete(InvoiceReceivePayment $payment)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$payment) {
            abort(404);
        }
        $title = trans('invoices_payment_log.delete');

        return view('user.invoices_payment_log.delete', compact('title', 'payment'));
    }

    public function destroy(InvoiceReceivePayment $payment)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$payment) {
            abort(404);
        }
        $payment->update(['is_delete_list' => 1]);
        //restore status and unpaid_amount values from associated invoices

        foreach ($payment->paidInvoices as $ppd) {
            $ppd->update([
                'status' => null,
                'unpaid_amount' => $ppd->unpaid_amount + $ppd->pivot->total,
            ]);
        }

        return redirect('invoices_payment_log');
    }

    public function cancel(InvoiceReceivePayment $payment)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        
        $title = 'Cancelar REP';

        return view('user.invoices_payment_log.cancel', compact('title', 'payment'));
    }
    public function confirmCancel(InvoiceReceivePayment $payment, Request $request)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        if (!$payment) {
            abort(404);
        } else {
            DB::beginTransaction();
            // update unpaid_amount for every paid invoice
            $payment->load('paidInvoices');
            foreach ($payment->paidInvoices as $invoice){
                $remaining_balance = round($invoice->unpaid_amount + $invoice->pivot->total, 2);
                $invoice_data['unpaid_amount'] = $remaining_balance;
                $invoice_data['status'] = null;
                $invoice->update($invoice_data);
            }
            // Enviar peticion de cancelacion.
            $response = SatDoc::cancelCfdi($payment, $request->motivo, $request->folioSustitucion);
            if ($response == '0') {
                // Si la respuesta es state=0, consultar el estado (getacuse) del uuid cancelado.
                $xmlbase64 = SatDoc::getAcuseCancelacion($payment);
    
                // Guardar el xmlbase64 del acuse cancelacion y estado de invoice en BD a cancelada.
                $payment->update(['is_delete_list' => 1, 'acuse_xml' => $xmlbase64]);
                DB::commit();
            } else {
                DB::rollBack();
                return $response;
            }

            return redirect('invoices_payment_log/' . $payment->id . '/show');
        }
        
    }

    public function data()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['invoices.read'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        $invoice_payments = $this->invoicePaymentRepository->getAll()
            ->sortByDesc('id')
            ->map(
                function ($ip) {

                    return [
                        'id' => $ip->id,
                        'issued_date' => $ip->payment_date,
                        'payment_number' => "#$ip->payment_number",
                        'sat_rfc' => $ip->companies->sat_rfc ?? "",
                        'sat_name' => $ip->companies->sat_name ?? "",
                        'company_name' => $ip->companies->name ?? "",
                        'company_email' => $ip->companies->email ?? "",
                        'total' => $ip->payment_received,
                        'currency' => $ip->currency ?? "",
                        "avatar" => "",
                        'status' => ($ip->is_delete_list == 1) ? "Cancelada" : "Activa",
                        'sat_uuid' => $ip->uuid_sat ?? "",
                    ];
                }
            );

        return DataTables::of($invoice_payments)->make();
    }

    public function printQuot(InvoiceReceivePayment $payment)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $payment->organization_id) {
            return redirect('dashboard');
        }
        $template = 'invoice_payment_template.' . config('settings.invoice_payment_template');
        $pdf = SatDoc::generatePdf2($payment, $template);
        return $pdf->stream();        
    }

    public function downloadPdf(InvoiceReceivePayment $payment)
    {
        $organization = $this->userRepository->getOrganization();
        if ($organization->id !== $payment->organization_id) {
            return redirect('dashboard');
        }
        $template = 'invoice_payment_template.' . config('settings.invoice_payment_template');
        $pdf = SatDoc::generatePdf2($payment, $template);
        $filename = $payment->payment_serie . '-' . $payment->payment_number;

        return $pdf->download($filename . '.pdf');
    }

    public function downloadXml(InvoiceReceivePayment $payment)
    {
        $content = base64_decode($payment->cfdi_xml);
        $filename = $payment->payment_serie . '-' . $payment->payment_number;
        return response()->attachment($content, $filename . '.xml');
    }

    public function ajaxCreatePdf(InvoiceReceivePayment $payment)
    {
        $db_payment = $this->invoicePaymentRepository->find($payment_id);

        $cfdiData = SatDoc::cfdiFromXmlString(base64_decode($db_payment->cfdi_xml));
        $comprobante = $cfdiData->comprobante();
        $emisor = $cfdiData->emisor();
        $receptor = $cfdiData->receptor();
        $tfd = $cfdiData->timbreFiscalDigital();
        $relacionados = $comprobante->searchNode('cfdi:CfdiRelacionados');
        $impuestos = $comprobante->searchNode('cfdi:Impuestos');
        $totalImpuestosTrasladados = $comprobante->searchAttribute('cfdi:Impuestos', 'TotalImpuestosTrasladados');
        $totalImpuestosRetenidos = $comprobante->searchAttribute('cfdi:Impuestos', 'TotalImpuestosRetenidos');
        $qrcode = $cfdiData->qrUrl();
        $tfdString = $cfdiData->tfdSourceString();

        $invoice_array['id'] = $payment_id;

        $payment = json_decode(json_encode($invoice_array), false);

        $payment->payment_number = $db_payment->payment_number;
        $payment->payment_serie = $db_payment->payment_serie;
        $payment->comprobante = $comprobante;
        $payment->emisor = $emisor;
        $payment->receptor = $receptor;
        $payment->tfd = $tfd;
        $payment->relacionados = $relacionados;
        $payment->impuestos = $impuestos;
        $payment->totalImpuestosTrasladados = $totalImpuestosTrasladados;
        $payment->totalImpuestosRetenidos = $totalImpuestosRetenidos;
        $payment->qrcode = $qrcode;
        $payment->tfdString = $tfdString;
        $payment->terms_and_conditions = $db_payment->terms_and_conditions;
        $payment->payment_type = $this->getFormaPago($db_payment->payment_type);
        $payment->payment_date = $db_payment->payment_date;
        $payment->payment_received = $db_payment->payment_received;

        $invoice_payment_template = config('settings.invoice_payment_template');
        $filename = $db_payment->payment_serie . '-' . $payment->payment_number;
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'portrait');
        $pdf->loadView('invoice_payment_template.' . $invoice_payment_template, compact('payment'));

        $pdf->save('./pdf/' . $filename . '.pdf');
        $pdf->stream();
        // echo url('pdf/'.$filename.'.pdf');

        return url('pdf/' . $filename . '.pdf');

        // return $pdf->download($filename.'.pdf');
    }

    public function sendCfdi(Request $request)
    {

        $settings = $this->settingsRepository->getAll();
        $organizationSettings = $this->organizationSettingsRepository->getAll();
        $email_subject = 'Factura Electrónica';
        $to_company = $request->email;
        $email_body = 'Hola, este es un Recibo de Pago Electrónico de ' . $organizationSettings['sat_name'];
        $message_body = Common::parse_template($email_body);

        $payment = $this->invoicePaymentRepository->find($request->id);
        $pdf_file = $payment->payment_serie . '-' . $payment->payment_number . '.pdf';
        $pdf_content = $this->printQuot($payment);
        $xml_content = base64_decode($payment->cfdi_xml);
        $xml_file = $payment->payment_serie . '-' . $payment->payment_number . '.xml';


        $site_email = $settings['site_email'];

        if (!empty($to_company) && false === !filter_var($site_email, FILTER_VALIDATE_EMAIL)) {

            if (false === !filter_var($to_company, FILTER_VALIDATE_EMAIL)) {

                Mail::to($to_company)->send(new SendCfdi([
                    'from' => $site_email,
                    'subject' => $email_subject,
                    'message_body' => $message_body,
                    'quotation_pdf' => $pdf_file,
                    'quotation_xml' => $xml_file,
                    'xml_content' => $xml_content,
                    'pdf_content' => $pdf_content

                ]));
            }
            echo "Recibo de pago enviado";
        } else {
            echo "El Recibo de pago no pudo ser enviado";
        }
    }

    private function generateParams()
    {
        $this->user = $this->getUser();

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

        $payment_methods = $this->optionRepository->getAll()
            ->where('category', 'payment_methods')
            ->map(function ($title) {
                return [
                    'text' => $title->title,
                    'id' => $title->value,
                ];
            })->pluck('text', 'id')->prepend(trans('invoice.payment_method'), '');

        /*=== ===Getting the current payment number === ===*/
        $payment = $this->invoicePaymentRepository->getAll()->count();
        if (0 == $payment) {
            $total_fields = 0;
            $start_number = (int)$this->organizationSettingsRepository->getKey('invoice_payment_start_number');
        } else {
            $total_fields = $this->invoicePaymentRepository->getAll()->last()->payment_number;
        }

        $current_number = ((isset($start_number) ? $start_number : 0) + (isset($total_fields) ? $total_fields : 0) + 1);

        $payment_number = $current_number;
        $payment_serie = $this->organizationSettingsRepository->getKey('invoice_payment_prefix');

        /*=== ===End of getting the current payment number === ===*/

        view()->share('companies', $companies);
        view()->share('org_zip_code', $org_zip_code);
        view()->share('payment_methods', $payment_methods);
        view()->share('payment_number', $payment_number);
        view()->share('payment_serie', $payment_serie);
    }

    public function paymentLog(Request $request)
    {
        return $this->invoiceRepository->getAllUnpaidPpdForCompany($request->id);
    }

    private function getFormaPago($id)
    {
        $path = base_path('resources/assets/sat_catalog/c_FormaPago.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }

    public function storev3(InvoiceReceivePaymentRequest $request)
    {
        $this->generateParams();

        if ((!$this->user->hasAccess(['invoices.write'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }

        $organization = $this->userRepository->getOrganization();
        $organizationSettings = $this->organizationSettingsRepository->getAll();
        $settings = $this->settingsRepository->getAll();
        $company = $this->companyRepository->find($request->company_id);

        if ($settings['timbrador_selected'] == 'gofac') {

            /* GoFac */

            /* Building the CFDI */

            // This Library requires CSD files to be pem format
            $key = $organizationSettings['key_pem_file'];
            $cer = $organizationSettings['cer_pem_file'];

            $cfdi = new CFDI([
                'Version' => '3.3',
                'Serie' => $request['payment_serie'],
                'Folio' => $request['payment_number'],
                'Fecha' => now()->timezone('America/Tijuana')->format('Y-m-d\TH:i:s'),
                'NoCertificado' => $organizationSettings['num_certificado'],
                'SubTotal' => '0',
                'Moneda' => 'XXX',
                'Total' => '0',
                'TipoDeComprobante' => 'P',
                'LugarExpedicion' => $request['org_zip_code'],
            ], $key, $cer);

            // Nodos - Emisor
            $cfdi->add(new Emisor([
                'Rfc' => $organizationSettings['sat_rfc'],
                'Nombre' => $organizationSettings['sat_name'],
                'RegimenFiscal' => $organizationSettings['fiscal_regimen'],
            ]));

            // Nodos - Receptor
            $cfdi->add(new Receptor([
                'Rfc' => $company->sat_rfc,
                'Nombre' => $company->sat_name,
                'UsoCFDI' => 'P01', // value required by SAT
            ]));

            // Nodos - Concepto para REP
            $concepto = new Concepto([
                'ClaveProdServ' => '84111506', // value required by SAT
                'Cantidad' => '1', // value required by SAT
                'ClaveUnidad' => 'ACT', // value required by SAT
                'Descripcion' => 'Pago', // value required by SAT
                'ValorUnitario' => '0', // value required by SAT
                'Importe' => '0', // value required by SAT
            ]);

            $cfdi->add($concepto);

            // Nodos - Complemento de Pago
            $pago_array['FechaPago'] = date('Y-m-d\TH:i:s', strtotime($request['payment_date']));
            $pago_array['FormaDePagoP'] = $request['payment_type'];
            $pago_array['MonedaP'] = $request['payment_currency'];

            if ($request['payment_currency'] == 'USD') {
                $pago_array['TipoCambioP'] = $request['exchange_rate'];
            }


            $pago_array['Monto'] = $request['total'];

            if (isset($request['transaction_number']) && $request['transaction_number'] !== "") {
                $pago_array['NumOperacion'] = $request['transaction_number'];
            }

            $pago = new Pago($pago_array, [
                'Version' => '1.0',
            ]);


            // Nodos - Complementos
            foreach ($request['uuid_sat'] as $arrKey => $arrData) {

                if ($request['invoice_id'][$arrKey] == 'otro') {

                    $doctoRelacionado = new DoctoRelacionado([
                        'IdDocumento' => $request['uuid_sat'][$arrKey],
                        'Serie' => $request['cfdi_serie'][$arrKey],
                        'Folio' => $request['cfdi_number'][$arrKey],
                        'MonedaDR' => $request['currency'][$arrKey],
                        'MetodoDePagoDR' => 'PPD',
                        'NumParcialidad' => $request['faction'][$arrKey],
                        'ImpSaldoAnt' => $request['unpaid_amount'][$arrKey],
                        'ImpPagado' => $request['total_payment'][$arrKey],
                        'ImpSaldoInsoluto' => $request['unpaid_amount'][$arrKey] - $request['total_payment'][$arrKey],
                    ]);
                } else {

                    $db_invoice = $this->invoiceRepository->find($request['invoice_id'][$arrKey]);

                    $cfdiData = $this->cfdiObject(base64_decode($db_invoice->cfdi_xml));
                    $comprobante = $cfdiData->comprobante();

                    $doctoRelacionado = new DoctoRelacionado([
                        'IdDocumento' => $request['uuid_sat'][$arrKey],
                        'Serie' => $comprobante['Serie'],
                        'Folio' => $comprobante['Folio'],
                        'MonedaDR' => $request['currency'][$arrKey],
                        'MetodoDePagoDR' => 'PPD',
                        'NumParcialidad' => $request['faction'][$arrKey],
                        'ImpSaldoAnt' => $request['unpaid_amount'][$arrKey],
                        'ImpPagado' => $request['total_payment'][$arrKey],
                        'ImpSaldoInsoluto' => $request['unpaid_amount'][$arrKey] - $request['total_payment'][$arrKey],
                    ]);
                }

                $pago->add($doctoRelacionado);

                //prepare payment array for payment_log in DB

                $invoice_payment_data[$arrKey]['company_id'] = $request['company_id'];
                $invoice_payment_data[$arrKey]['payment_serie'] = $request['payment_serie'];
                $invoice_payment_data[$arrKey]['payment_number'] = $request['payment_number'];
                $invoice_payment_data[$arrKey]['payment_date'] = $request['payment_date'];
                $invoice_payment_data[$arrKey]['payment_type'] = $request['payment_type'];
                $invoice_payment_data[$arrKey]['currency'] = $request['payment_currency'];
                $invoice_payment_data[$arrKey]['exchange_rate'] = $request['exchange_rate'];
                $invoice_payment_data[$arrKey]['payment_method'] = 'PPD';
                $invoice_payment_data[$arrKey]['payment_received'] = $request['total'];

                if (isset($request['transaction_number']) && $request['transaction_number'] !== "") {
                    $invoice_payment_data[$arrKey]['transaction_number'] = $request['transaction_number'];
                }

                if (isset($request['companyBanks']) && $request['companyBanks'] !== "") {
                    $invoice_payment_data[$arrKey]['company_bank'] = $request['companyBanks'];
                }

                if (isset($request['organization_bank']) && $request['organization_bank'] !== "") {
                    $invoice_payment_data[$arrKey]['organization_bank'] = $request['organization_bank'];
                }

                $invoice_payment_data[$arrKey]['invoice_id'] = $request['invoice_id'];
                $invoice_payment_data[$arrKey]['invoice_serie'] = $request['cfdi_serie'];
                $invoice_payment_data[$arrKey]['invoice_folio'] = $request['cfdi_number'];
                $invoice_payment_data[$arrKey]['invoice_uuid'] = $request['uuid_sat'];
                $invoice_payment_data[$arrKey]['invoice_date'] = $request['invoice_date'];
                $invoice_payment_data[$arrKey]['invoice_currency'] = $request['currency'];
                $invoice_payment_data[$arrKey]['total'] = $request['total_payment'];
                $invoice_payment_data[$arrKey]['partiality'] = $request['faction'];
            }

            $cfdi->add($pago);

            /* End of Building the CFDI */

            $url = $settings['gofac_url'];
            $xml_en_bruto = $cfdi->getXML();

            if ($settings['gofac_mode'] == 'sandbox') {
                $usuario = $settings['gofac_sandbox_username'];
                $password = $settings['gofac_sandbox_password'];
                $resultField = 'TestCfd33Result';
            } else if ($settings['gofac_mode'] == 'live') {

                $usuario = $settings['gofac_live_username'];
                $password = $settings['gofac_live_password'];
                $resultField = 'GetTicketResult';
            }

            //Establecemos el usuario y contraseña de timbrado. Estos pueden variar dependiendo quién va a timbrar. 
            $cuentaUsuario = $usuario;
            $claveUsuario = $password;

            //Convertimos nuestra cadena xml a formato base64.

            $xmlBase64 = base64_encode($xml_en_bruto);

            //Creación y consumo del servicio web de timbrado 
            $service = new SoapClient($url . "?WSDL");
            $ns = 'http://tempuri.org/';
            $headerbody = array('strUserName' => $cuentaUsuario, 'strPassword' => $claveUsuario);
            $header = new SOAPHeader($ns, 'AuthSoapHd', $headerbody);
            $service->__setSoapHeaders($header);


            if ($settings['gofac_mode'] == 'sandbox') {

                //Utilizamos el método TestCfd33 para timbrar en pruebas del servicio web;enviando como parámetro el CFDI en base64 que vamos a timbrar.

                $StructXml  = $service->TestCfd33(array('base64Cfd' => $xmlBase64));
            } else if ($settings['gofac_mode'] == 'live') {

                $StructXml  = $service->GetTicket(array('base64Cfd' => $xmlBase64));
            }

            if ($StructXml->{$resultField}->state == '' || $StructXml->{$resultField}->state == '0') //Si el estado que responde el servicio es vacío, significa que hubo éxito.
            {

                $Cfdi = $StructXml->{$resultField}->Cfdi; //Obtenemos el CFDI timbrado en formato XML.
                $Timbre = $StructXml->{$resultField}->Timbre;
                //$Descripcion = $StructXml->TestCfd33Result->Descripcion;
                $state = $StructXml->{$resultField}->state;
                $error = '';

                //Decodificamos el cfdi de base64 
                $xml_decodificado = base64_decode($Cfdi);

                $cfdiData = $this->cfdiObject($xml_decodificado);
                $invoice_payment_data[$arrKey]['uuid_sat'] = $cfdiData->timbreFiscalDigital()['UUID'];
                $invoice_payment_data[$arrKey]['cfdi_xml'] = $Cfdi;

                //create a payment_log for each uuid payed in REP
                $createdPayment = $this->invoicePaymentRepository->createPayment($invoice_payment_data[$arrKey]);

                if ($request['invoice_id'][$arrKey] != "otro") {
                    $unpaid_amount_new = round($db_invoice->unpaid_amount - $request['total_payment'][$arrKey], 2);

                    if ($unpaid_amount_new <= '0') {

                        $invoice_data = [
                            'unpaid_amount' => $unpaid_amount_new,
                            'status' => trans('invoice.paid_invoice'),
                        ];
                    } else {

                        $invoice_data = [
                            'unpaid_amount' => $unpaid_amount_new,
                        ];
                    }

                    $db_invoice->update($invoice_data);
                }

                return response()->json([
                    'state' => $state,
                    'payment_id' => $createdPayment->id,
                    'pdf_file' => $this->ajaxCreatePdf($createdPayment->id)
                ], 200);
            } else {
                //Si se presentento error obtenemos el mensaje de error y el código del mismo en caso de que no hubo éxito.
                $Descripcion = $StructXml->{$resultField}->Descripcion;
                $state = $StructXml->{$resultField}->state;

                return response()->json([
                    'error' => $state,
                    'message' => $Descripcion
                ], 400);
            }
            // fin de proceso para el timbrado

        } 

        return redirect('invoices_payment_log');
    }

    public function payInvoice(Invoice $invoice)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.write'])) && $this->user->orgRole == 'staff') {
            return redirect('dashboard');
        }
        if ($invoice->payment_method != 'PPD') {
            abort(404);
        }
        $payment = new InvoiceReceivePayment();
        $payment->company_id = $invoice->company_id;
        $payment->paidInvoices[0] = $invoice;
        $invoices = $this->invoiceRepository->getAllUnpaidPpdForCompany($payment->company_id);
        $title = 'Registrar pago a CFDI ' . $invoice->invoice_serie . $invoice->invoice_number;
        return view('user.invoices_payment_log.create', compact('title', 'payment', 'invoices'));
    }
}