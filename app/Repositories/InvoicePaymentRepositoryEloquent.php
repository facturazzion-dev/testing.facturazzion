<?php

namespace App\Repositories;

use App\Helpers\SatDoc;
use App\Models\InvoiceReceivePayment;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Models\Invoice;
use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\Elements\Pagos20\Pagos;
use CfdiUtils\Utils\Format;
use Illuminate\Support\Facades\Log;
use SoapClient;
use SoapHeader;

class InvoicePaymentRepositoryEloquent extends BaseRepository implements InvoicePaymentRepository
{
    private $userRepository;

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return InvoiceReceivePayment::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams()
    {
        $this->userRepository = new UserRepositoryEloquent(app());
    }

    public function getAll()
    {
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoiceReceivePayments.invoice.companies', 'invoiceReceivePayments.paidInvoices');
        $invoicesPayment = $org->invoiceReceivePayments;
        return $invoicesPayment;
    }

    public function getAllGroupBySatPayment()
    {
        $this->generateParams();
        $invoicesPayment = $this->userRepository->getOrganization()->invoiceReceivePayments()
        ->groupBy('payment_number')
        ->get();

        return $invoicesPayment;
    }

    public function updateOrCreatePayment(array $data, $payment_id = null)
    {
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();

        $paymentData['user_id'] = $user->id;
        $paymentData['organization_id'] = $organization->id;
        $paymentData['status'] = $data['status'] ?? null;
        $paymentData['company_id'] = $data['company_id'];
        $paymentData['payment_date'] = $data['payment_date'];
        $paymentData['payment_serie'] = $data['payment_serie'];
        $paymentData['payment_number'] = $data['payment_number'];
        $paymentData['exchange_rate'] = $data['exchange_rate'];
        $paymentData['payment_type'] = $data['payment_type']; 
        /* TO-DO: 
            Make sure all these variables have the same name everywhere in
            the project, including the database to avoid mapping them:                 
            */
        $paymentData['payment_method'] = 'PPD'; //not required by sat anymore in pagos 2.0
        $paymentData['payment_received'] = $data['total'];
        $paymentData['currency'] = $data['payment_currency'];

        $paymentData['transaction_number'] = isset($data['transaction_number']) && !empty($data['transaction_number']) ? $data['transaction_number'] : '';
        $paymentData['company_bank'] = isset($data['companyBanks']) && !empty($data['companyBanks']) ? $data['companyBanks'] : '';
        $paymentData['organization_bank'] = isset($data['organization_bank']) && !empty($data['organization_bank']) ? $data['organization_bank'] : '';

        $payment = $this->updateOrCreate(['id' => $payment_id], $paymentData);
        $payment->paidInvoices()->detach();
        $list =[];
        foreach ($data['invoices'] as $invoice) {
            if ($invoice['invoice_id'] != "otro") {
                $invoiceObj = Invoice::find($invoice['invoice_id']);

                $paymentAddData['user_id'] = $paymentData['user_id'];
                $paymentAddData['organization_id'] = $paymentData['organization_id'];
                $paymentAddData['company_id'] = $paymentData['company_id'];
                $paymentAddData['invoice_receive_payment_id'] = $payment->id;
                $paymentAddData['invoice_uuid'] = $invoiceObj->uuid_sat;
                $paymentAddData['invoice_date'] = $invoiceObj->invoice_date;
                $paymentAddData['invoice_currency'] = $invoiceObj->currency;
                $paymentAddData['invoice_serie'] = $invoiceObj->invoice_serie;
                $paymentAddData['invoice_folio'] = $invoiceObj->invoice_number;
                $paymentAddData['invoice_id'] = $invoiceObj->id;
                $paymentAddData['partiality'] = $invoice['faction'];
                $paymentAddData['total'] = $invoice['total_payment'];

                $list[$invoice['invoice_id']] = $paymentAddData;
            }
        }
        $payment->paidInvoices()->attach($list);  
        return $payment;
    }

    public function createPayment(array $data)
    {
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();

        $data['user_id'] = $user->id;
        $data['organization_id'] = $organization->id;

        $team = collect($data)->except('invoice_id', 'invoice_serie', 'invoice_folio', 'invoice_uuid', 'invoice_date', 'invoice_currency', 'total', 'partiality')->toArray();
        $invoice_payment = $this->create($team);
        
        foreach ($data['invoice_id'] as $key =>$invoice){
            $list =[];
            $temp = []; 
            if ($invoice != "") {
                $aInvoiceReceivePayment = InvoiceReceivePayment:: where('uuid_sat', $data['uuid_sat'])
                ->select('id')
                ->get();

                $temp['user_id'] = $data['user_id'];
                $temp['organization_id'] = $data['organization_id'];
                $temp['company_id'] = $data['company_id'];
                $temp['invoice_receive_payment_id'] = $aInvoiceReceivePayment[0]['id'];
                $temp['invoice_uuid'] = $data['invoice_uuid'][$key];
                $temp['invoice_currency'] = $data['invoice_currency'][$key];
                $temp['total'] = $data['total'][$key];
                $temp['partiality'] = $data['partiality'][$key];

                if(isset($data['invoice_serie'][$key]) && $data['invoice_serie'][$key] != ''){
                    $temp['invoice_serie'] = $data['invoice_serie'][$key];
                    $temp['invoice_folio'] = $data['invoice_folio'][$key];

                    $i = new Invoice();
                    $i->organization_id = $temp['organization_id'];
                    $i->invoice_serie = $temp['invoice_serie'];
                    $i->invoice_number = $temp['invoice_folio'];
                    $i->invoice_date =$data['invoice_date'][$key];
                    $i->due_date = $data['invoice_date'][$key];
                    $i->payment_method = 'PPD';
                    $i->status = 'otro';
                    $i->total = $temp['total'];
                    $i->tax_amount = 0;
                    $i->grand_total = $temp['total'];
                    $i->final_price = $temp['total'];
                    $i->uuid_sat = $temp['invoice_uuid'];
                    $i->currency = $temp['invoice_currency'];
                    $i->user_id = $temp['user_id'];
                    $i->payment_type = 99;
                    $i->cfdi_use = 'G03';
                    $i->iva_included = 0;
                    $i->exchange_rate = 1;
                    $i->save();
                }else{
                    $temp['invoice_serie'] = null;
                    $temp['invoice_folio'] = null;
                }

                if($data['invoice_id'][$key] == 'otro'){
                    $temp['invoice_id'] = $i->id;
                }else{
                    $temp['invoice_id'] = $data['invoice_id'][$key];
                }
                

                $list[$data['invoice_id'][$key]] = $temp;
                $invoice_payment->paidInvoices()->attach($list);  
            }
        }
        return $invoice_payment;
    }

    public function getAllPaidForCustomer($company_id)
    {
        $this->generateParams();
        $invoice_payment = $this->userRepository->getOrganization()->invoiceReceivePayments()->where([
            ['company_id','=', $company_id],
        ])->get();
        return $invoice_payment;
    }

    public function getAllPaidGroupBySatPaymentForCustomer($company_id)
    {
        $this->generateParams();
        $invoice_payment = $this->userRepository->getOrganization()->invoiceReceivePayments()
        ->where([
            ['company_id','=', $company_id],
        ])
        ->groupBy('payment_number')
        ->get();
        return $invoice_payment;
    }

    public function getMonth($created_at)
    {
        $invoicesPayment = $this->model->whereMonth('created_at', $created_at)->get();
        return $invoicesPayment;
    }
    
    public function getMonthYear($monthno,$year)
    {
        $this->generateParams();
        $invoicesPayment = $this->userRepository->getOrganization()->invoiceReceivePayments()->whereYear('payment_date', $year)->whereMonth('payment_date', $monthno)->get();
        return $invoicesPayment;
    }

    public function getCfdiCreator40(InvoiceReceivePayment $payment){
        $keyPemFile = $payment->organization->getSetting('key_pem_file');
        $cerPemFile = $payment->organization->getSetting('cer_pem_file');
        $fielPass = $payment->organization->getSetting('fiel_pwd');

        $creatorData = [
            'Serie' => $payment->payment_serie,
            'Folio' => $payment->payment_number,
            'Fecha' => now()->timezone('America/Tijuana')->format('Y-m-d\TH:i:s'),
            'SubTotal' => '0',
            'Moneda' => 'XXX',
            'Total' => '0',
            'TipoDeComprobante' => 'P',
            'LugarExpedicion' => $payment->organization->getSetting('zip_code'),
            'Exportacion' => '01', // No aplica
        ];
        $emisorData = [
            'Rfc' => strtoupper($payment->organization->getSetting('sat_rfc')),
            'Nombre' => strtoupper($payment->organization->getSetting('sat_name')),
            'RegimenFiscal' => $payment->organization->getSetting('fiscal_regimen'),
        ];
        $receptorData = [
            'Rfc' => strtoupper($payment->companies->sat_rfc),
            'Nombre' => strtoupper($payment->companies->sat_name),
            'UsoCFDI' => 'CP01', // value required by SAT
        ];
        if($payment->companies->isNational()) {
            $receptorData['RegimenFiscalReceptor'] = $payment->companies->fiscal_regimen;
            $receptorData['DomicilioFiscalReceptor'] = $payment->companies->zip_code;
        }
        else {
            $receptorData['ResidenciaFiscal'] = 'USA';
            $receptorData['NumRegIdTrib'] = '121585958';
        }

        $certificado = new Certificado($cerPemFile);
        $creator = new CfdiCreator40($creatorData, $certificado);        
        $comprobante = $creator->comprobante();
        $comprobante->addEmisor($emisorData);
        $comprobante->addReceptor($receptorData);

        $conceptoData = [
            'ClaveProdServ' => '84111506', // value required by SAT
            'Cantidad' => '1', // value required by SAT
            'ClaveUnidad' => 'ACT', // value required by SAT
            'Descripcion' => 'Pago', // value required by SAT
            'ValorUnitario' => '0', // value required by SAT
            'Importe' => '0', // value required by SAT
            'ObjetoImp' => '01', // value required by SAT
        ];
        $comprobante->addConcepto($conceptoData);

        $complementoPagos = new Pagos();

        $pagoData = [
            'FechaPago' => date('Y-m-d\TH:i:s', strtotime($payment->payment_date)),
            'FormaDePagoP' => $payment->payment_type,
            'MonedaP' => $payment->currency,
            'Monto' => $payment->payment_received,
            'TipoCambioP' => $payment->exchange_rate,
        ];
        if (isset($payment->transaction_number) && $payment->transaction_number !== "") {
            $pagoData['NumOperacion'] = $payment->transaction_number;
        }

        $pago = $complementoPagos->addPago($pagoData);
        $trasladosP = [];
        $retencionesP = [];

        $payment->load('paidInvoices');
        foreach ($payment->paidInvoices as $invoice){
            $ppd = $invoice->pivot;
            Log::info($ppd);
            $comprobanteCfdi = \CfdiUtils\Cfdi::newFromString(base64_decode($invoice->cfdi_xml))->getQuickReader();                

            $doctoRelacionadoData = [
                'IdDocumento' => $invoice->uuid_sat,
                'Serie' => $invoice->invoice_serie,
                'Folio' => $invoice->invoice_number,
                'MonedaDR' => $invoice->currency,
                'NumParcialidad' => $ppd->partiality,
                'ImpSaldoAnt' => $invoice->unpaid_amount,
                'ImpPagado' => $ppd->total,
                'ImpSaldoInsoluto' => $invoice->unpaid_amount - $ppd->total,
                'ObjetoImpDR' => "01",
            ];

            if($payment->currency == $doctoRelacionadoData['MonedaDR']) {
                /* EquivalenciaDR:
                    Es el tipo de cambio conforme con la moneda registrada en el documento relacionado.
                    Este dato es requerido cuando la moneda del documento relacionado es distinta de la
                    moneda de pago.
                    Se debe registrar el número de unidades de la moneda señalada en el documento
                    relacionado que equivalen a una unidad de la moneda del pago.
                */
                $doctoRelacionadoData['EquivalenciaDR'] = '1';
            }
            
            if(!empty($comprobanteCfdi->impuestos)) {
                $trasladosDR = [];
                $retencionesDR = [];
                $doctoRelacionadoData['ObjetoImpDR'] = "02";
                $doctoRelacionado = $pago->addDoctoRelacionado($doctoRelacionadoData);
                $impuestosDoctoR = $doctoRelacionado->getImpuestosDR();
                /* Se obtiene la proporcion del pago sobre el total de la factura */
                $paymentProportion = $doctoRelacionadoData['ImpPagado'] / Format::number($comprobanteCfdi['Total'], 2); 
                
                foreach (($comprobanteCfdi->impuestos->traslados)() as $i => $impuesto) {
                    $impuestoBaseComprobante = Format::number($impuesto['Importe'] / $impuesto['TasaOCuota'], 2);
                    /* El atributo BaseDR del impuestoDR se obtiene como el producto obtenido de la proporcion del pago sobre el total
                    y la base de los impuestos totales del comprobante (traslado o retencion, segun sea el caso) */
                    $impBaseDr = $paymentProportion * $impuestoBaseComprobante;
                    $trasladosDR[$i] = [
                        'BaseDR' => $impBaseDr,
                        'ImpuestoDR' => $impuesto['Impuesto'],
                        'TipoFactorDR' => $impuesto['TipoFactor'],
                        'TasaOCuotaDR' => $impuesto['TasaOCuota'],
                        'ImporteDR' => Format::number($impBaseDr * $impuesto['TasaOCuota'], 2)
                    ];
                    $impuestosDoctoR->getTrasladosDR()->addTrasladoDR($trasladosDR[$i]);
                }
                foreach (($comprobanteCfdi->impuestos->retenciones)() as $i => $impuesto) {
                    $impuestoBaseComprobante = Format::number($impuesto['Importe'] / $impuesto['TasaOCuota'], 2);
                    $impBaseDr = $paymentProportion * $impuestoBaseComprobante;
                    $retencionesDR[$i] = [
                        'BaseDR' => $impBaseDr,
                        'ImpuestoDR' => $impuesto['Impuesto'],
                        'TipoFactorDR' => $impuesto['TipoFactor'],
                        'TasaOCuotaDR' => $impuesto['TasaOCuota'],
                        'ImporteDR' => Format::number($impBaseDr * $impuesto['TasaOCuota'], 2)
                    ];
                    $impuestosDoctoR->getRetencionesDR()->addRetencionDR($retencionesDR[$i]);
                }
                foreach (SatDoc::groupTaxes($trasladosDR, 'DR', 'P', array('Importe', 'Base')) as $i => $impuesto) {
                    $trasladosP[$i] = $pago->getImpuestosP()->addTrasladosP()->addTrasladoP($impuesto);
                }
                foreach (SatDoc::groupTaxes($retencionesDR, 'DR', 'P', array('Importe', 'Base')) as $impuesto) {
                    $retencionesP[$i] = $pago->getImpuestosP()->addRetencionesP()->addRetencionP($impuesto);
                }
            }
        }
        $totales = SatDoc::getPagos20ImpuestosTotales($retencionesP, $trasladosP);
        $totales['MontoTotalPagos'] = $payment->payment_received;
        $complementoPagos->addTotales($totales);

        $comprobante->addComplemento($complementoPagos);

        $creator->moveSatDefinitionsToComprobante();
        // add additional calculated information sumas sello
        $creator->addSumasConceptos(null, 0);
        $creator->addSello($keyPemFile, $fielPass);

        // validate the comprobante and check it has no errors or warnings
        $asserts = $creator->validate();        
        
        if ($asserts->hasErrors()) {
            foreach ($asserts->errors() as $error) {
                Log::error([
                    $error->getCode(),
                    $error->getStatus(),
                    $error->getTitle(),
                    $error->getExplanation(),
                ]);
            }
        }
        return $creator;
    }

    public function timbrarCfdi40(CfdiCreator40 $creator, InvoiceReceivePayment $payment = null)
    {
        $settingsRepository = new SettingsRepositoryEloquent(app());
        $settings = $settingsRepository->getAll();
        $url = $settings['gofac_url'];
        $xml_en_bruto = $creator->asXml();

        if ($settings['gofac_mode'] == 'sandbox') {
            $usuario = $settings['gofac_sandbox_username'];
            $password = $settings['gofac_sandbox_password'];
            $resultField = 'TestCfd33Result';
        } elseif ($settings['gofac_mode'] == 'live') {
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
        $header = new SoapHeader($ns, 'AuthSoapHd', $headerbody);
        $service->__setSoapHeaders($header);

        if ($settings['gofac_mode'] == 'sandbox') {
            //Utilizamos el método TestCfd33 para timbrar en pruebas del servicio web;enviando como parámetro el CFDI en base64 que vamos a timbrar.
            $StructXml = $service->TestCfd33(array('base64Cfd' => $xmlBase64));
        } elseif ($settings['gofac_mode'] == 'live') {
            $StructXml = $service->GetTicket(array('base64Cfd' => $xmlBase64));
        }

        //Si el estado que responde el servicio es vacío, significa que hubo éxito.
        $state = $StructXml->{$resultField}->state;
        if ($state == '' || $state == '0' || $state == '405') {

            //Podemos obtener información del CFDI que se acaba de timbrar por parte de lo que nos responde el servicio web.
            $cfdi = $StructXml->{$resultField}->Cfdi; //Obtenemos el CFDI timbrado en formato XML.

            //Decodificamos el cfdi de base64
            $xml_decodificado = base64_decode($cfdi);

            $cfdiData = SatDoc::cfdiFromXmlString($xml_decodificado);

            //update payment
            $payment->cfdi_xml = $cfdi;
            $payment->uuid_sat = $cfdiData->timbreFiscalDigital()['UUID'];
            $payment->save();

            // update unpaid_amount for every paid invoice
            $payment->load('paidInvoices');
            foreach ($payment->paidInvoices as $invoice){
                $remaining_balance = round($invoice->unpaid_amount - $invoice->pivot->total, 2);

                if ($remaining_balance <= '0') {
                    $invoice_data['status'] = trans('invoice.paid_invoice');
                }
                
                $invoice_data['unpaid_amount'] = $remaining_balance;
                $invoice->update($invoice_data);
            }


            return [ 0 => $state];
        } else {
            //Si se presentento error obtenemos el mensaje de error y el código del mismo en caso de que no hubo éxito.
            $Descripcion = $StructXml->{$resultField}->Descripcion;

            Log::error($Descripcion . 'xml= ' . $xml_en_bruto);

            $response = response()->json([
                'error' => $state,
                'message' => $Descripcion,
                'validator' => SatDoc::errorHelpText($Descripcion),
            ], 400);
            return $response;
        }
        // fin de proceso para el timbrado
    }
}
