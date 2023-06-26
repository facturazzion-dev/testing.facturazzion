<?php

namespace App\Helpers;

use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Saleorder;
use App\Repositories\SettingsRepositoryEloquent;
use App\Repositories\UserRepositoryEloquent;
use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\Elements\ImpLocal10\ImpuestosLocales;
use CfdiUtils\Elements\Pagos20\ImpuestosP;
use CfdiUtils\Elements\Pagos20\Pagos;
use CfdiUtils\Nodes\Node;
use CfdiUtils\Utils\Format;
use DateTimeImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use PhpCfdi\Credentials\Credential;
use PhpCfdi\XmlCancelacion\Capsules\Cancellation;
use PhpCfdi\XmlCancelacion\Credentials;
use PhpCfdi\XmlCancelacion\Models\CancelDocument;
use PhpCfdi\XmlCancelacion\Models\CancelDocuments;
use PhpCfdi\XmlCancelacion\Signers\DOMSigner;
use SoapClient;
use SoapHeader;

class SatDoc
{
    private const GROUP_BY_TAX_KEYS = ['Impuesto', 'TipoFactor', 'TasaOCuota'];
    private const SUM_TAX_KEYS = ['Importe'];
    public const TAXES = [
        '001' => 'ISR',
        '002' => 'IVA',
        '003' => 'IEPS'
    ];
    
    public static function errorHelpText($description)
    {   
        $code = explode(' ', $description)[0];
        $helper = ['message' => '', 'fields' => array()];

        switch ($code) {
            case 'CFDI40144':
                $helper['message'] = 'Revisa que la Razon Social este bien escrito y si es personal moral elimina ( SA de CV, S DE RL DE CV, etc.)';
                $helper['fields'] = ['sat_name'];
                break;
            
            case 'CFDI40145':
                $helper['message'] = 'Revisa que la Razon Social este bien escrito y si es personal moral elimina ( SA de CV, S DE RL DE CV, etc.)';
                $helper['fields'] = ['sat_name'];
                break;

            case 'CFDI40148':
                $helper['message'] = 'Verifica el código postal de tu cliente solicita la Constancia de Situacion Fiscal';
                $helper['fields'] = ['zip_code'];
                break;

            case 'CFDI40158':
                $helper['message'] = 'Revisa el Regimen Fiscal del Cliente sea correcto.';
                $helper['fields'] = ['fiscal_regimen'];
                break;
            case 'CFDI40157':
                $helper['message'] = 'Revisa el Regimen Fiscal del Cliente sea correcto.';
                $helper['fields'] = ['fiscal_regimen'];
                break;
            
            default:
                break;
        }

        return $helper;
    }

    public static function cfdiFromXmlString($xml_string)
    {
        // clean cfdi
        $xml_string = \CfdiUtils\Cleaner\Cleaner::staticClean($xml_string);

        // create the main node structure
        $comprobante = \CfdiUtils\Nodes\XmlNodeUtils::nodeFromXmlString($xml_string);

        $cfdiData = (new \PhpCfdi\CfdiToPdf\CfdiDataBuilder())
            ->build($comprobante);

        return $cfdiData;
    }
    public static function getRegimen($id)
    {
        $path = base_path('resources/assets/sat_catalog/c_RegimenFiscal.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }
    public static function getUsoCfdi($id)
    {
        $path = base_path('resources/assets/sat_catalog/c_UsoCFDI.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }
    public static function getMetodoPago($id)
    {
        $path = base_path('resources/assets/sat_catalog/c_MetodoPago.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }
    public static function getFormaPago($id)
    {
        $path = base_path('resources/assets/sat_catalog/c_FormaPago.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['descripcion'];
    }
    public static function getClaveUnidad($id)
    {
        $path = base_path('resources/assets/sat_catalog/c_ClaveUnidad.json');
        $content = json_decode(file_get_contents($path), true);

        $key = array_search($id, array_column($content, 'id'));

        return $id . ' - ' . $content[$key]['nombre'];
    }
    public static function cancelCfdi($model, $motivo, $folioSustitucion = null)
    {
        $cancel_xml = self::getCancelXml($model, $motivo, $folioSustitucion);
        $cancel_base64 = base64_encode($cancel_xml);

        $settingsRepository = new SettingsRepositoryEloquent(app());
        $settings = $settingsRepository->getAll();
        $url = $settings['gofac_url'];
        
        //Establecemos el usuario y contraseña de timbrado. Estos pueden variar dependiendo quién va a timbrar.
        if ($settings['gofac_mode'] == 'sandbox') {
            $usuario = $settings['gofac_sandbox_username'];
            $password = $settings['gofac_sandbox_password'];
            $serviceOperation = 'CancelTest';
            $base64Cfd['CanB64'] = $cancel_base64;
        } elseif ($settings['gofac_mode'] == 'live') {
            $usuario = $settings['gofac_live_username'];
            $password = $settings['gofac_live_password'];
            $serviceOperation = 'CancelTicket';
            $base64Cfd['base64Cfd'] = $cancel_base64;
        }
        $resultField = $serviceOperation . 'Result';

        //Creación y consumo del servicio web de timbrado
        $service = new SoapClient($url . "?WSDL");
        $ns = 'http://tempuri.org/';
        $headerbody = array('strUserName' => $usuario, 'strPassword' => $password);
        $header = new SoapHeader($ns, 'AuthSoapHd', $headerbody);
        $service->__setSoapHeaders($header);

        $StructXml = $service->{$serviceOperation}($base64Cfd);

        $result = $StructXml->{$resultField};
        $state = $result->state;

        if ($state == '' || $state == '0') { //Si el estado que responde el servicio es vacío, significa que hubo éxito.
            return $state;

            // $cfdiData = self::cfdiFromXmlString($xml_decodificado);
        } else {
            return json_encode($result);
        }
    }

    public static function getAcuseCancelacion($model)
    {
        $settingsRepository = new SettingsRepositoryEloquent(app());
        $settings = $settingsRepository->getAll();
        $url = $settings['gofac_url'];
        $RfcEmisor = $model->organization->getSetting('sat_rfc');

        //Establecemos el usuario y contraseña de timbrado. Estos pueden variar dependiendo quién va a timbrar.
        $cuentaUsuario = $settings['gofac_live_username'];
        $claveUsuario = $settings['gofac_live_password'];

        //Creación y consumo del servicio web de timbrado
        $service = new SoapClient($url . "?WSDL");
        $ns = 'http://tempuri.org/';
        $headerbody = array('strUserName' => $cuentaUsuario, 'strPassword' => $claveUsuario);
        $header = new SOAPHeader($ns, 'AuthSoapHd', $headerbody);
        $service->__setSoapHeaders($header);

        $StructXml = $service->GetAcuse(array(
            'RfcEmisor' => $RfcEmisor,
            'Usuario' => $cuentaUsuario,
            'Clave' => $claveUsuario,
            'Uuid' => $model->uuid_sat
        ));

        if ($StructXml->GetAcuseResult->Estado == '' || $StructXml->GetAcuseResult->Estado == '0') { //Si el estado que responde el servicio es vacío, significa que hubo éxito.
            $CfdiB64 = $StructXml->GetAcuseResult->CfdiB64;
            $state = $StructXml->GetAcuseResult->Estado;

            return $CfdiB64;
        } else {
            return $StructXml->GetAcuseResult->Estado;
        }
    }
    public static function getCancelXml($model, $motivo, $folioSustitucion)
    {
        $keyPemFile = $model->organization->getSetting('key_pem_file');
        $cerPemFile = $model->organization->getSetting('cer_pem_file');
        $fielPass = $model->organization->getSetting('fiel_pwd');
        $rfc = $model->organization->getSetting('sat_rfc');
        
        $date = now()->timezone('America/Tijuana')->format('Y-m-d\TH:i:s');

        // certificado, llave privada y clave de llave
        $credentials = Credentials::createWithPhpCfdiCredential(Credential::create($cerPemFile, $keyPemFile, $fielPass));

        switch ($motivo) {
            case '01':
                $cancel_document = CancelDocument::newWithErrorsRelated($model->uuid_sat, $folioSustitucion);
                break;
            case '02':
                $cancel_document = CancelDocument::newWithErrorsUnrelated($model->uuid_sat);
                break;
            case '04':
                $cancel_document = CancelDocument::newNormativeToGlobal($model->uuid_sat);
                break;
            default: //03
                $cancel_document = CancelDocument::newNotExecuted($model->uuid_sat);
                break;
        }

        // datos de cancelación
        $data = new Cancellation(
            $rfc,
            new CancelDocuments($cancel_document),
            new DateTimeImmutable($date)
        );

        // generación del xml
        $xml = (new DOMSigner())->signCapsule($data, $credentials);
        return $xml;
    }    

    public static function generatePdf($model, $template)
    {
        if (!empty($model->cfdi_xml)) {
            /* Si cfdi_xml tiene información significa que se puede 
            extraer la informacion del xml para llenar la plantilla pdf */
            $cfdiData = self::cfdiFromXmlString(base64_decode($model->cfdi_xml));
            $tfd = $cfdiData->timbreFiscalDigital();
            $tfd['SourceString'] = $cfdiData->tfdSourceString();
            $selloCancelacion = self::getSelloCancelacion($model);
            $paramsArray = compact('cfdiData', 'tfd', 'selloCancelacion', 'model');
        } else {
            /* En caso contrario, se puede extraer la informacion del objeto 
            creator para llenar la plantilla pdf */
            $creator = self::getCfdiCreator40($model);
            $comprobante = $creator->comprobante();
            $emisor = $comprobante->getEmisor();
            $receptor = $comprobante->getReceptor();
            $paramsArray = compact('comprobante', 'emisor', 'receptor', 'model');
        }
        
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'portrait');
        $pdf->loadView($template, $paramsArray);
        return $pdf;
    }

    public static function getSelloCancelacion($cfdi)
    {
        $sello = '';
        if ($cfdi->is_delete_list == 1 && !empty($cfdi->acuse_xml)) {
            $xml_acuse = base64_decode($cfdi->acuse_xml);
            $cfdiCancelado = new \SimpleXMLElement($xml_acuse);
            $sello = $cfdiCancelado->Signature->SignatureValue;
        }
        return $sello;
    }

    public static function generatePdf2($model, $template)
    {
        if (!empty($model->cfdi_xml)) {
            /* Si cfdi_xml tiene información significa que se puede 
            extraer la informacion del xml para llenar la plantilla pdf */
            $comprobante = \CfdiUtils\Cfdi::newFromString(base64_decode($model->cfdi_xml))->getQuickReader();                            
            $cfdiData = self::cfdiFromXmlString(base64_decode($model->cfdi_xml));
            $selloCancelacion = self::getSelloCancelacion($model);
            $paramsArray = compact('comprobante', 'cfdiData', 'selloCancelacion');
        } else {
            /* En caso contrario, se puede extraer la informacion del objeto 
            creator para llenar la plantilla pdf */
            $creator = self::getPago20Creator40($model);
            $comprobante = \CfdiUtils\Cfdi::newFromString($creator->asXml())->getQuickReader();                            
            $paramsArray = compact('comprobante');            
        }
        
        $pdf = App::make('dompdf.wrapper');
        $pdf->setPaper('a4', 'portrait');
        $pdf->loadView($template, $paramsArray);
        return $pdf;
    }

    public static function getCfdiCreator40($model){
        
        $keyPemFile = $model->organization->getSetting('key_pem_file');
        $cerPemFile = $model->organization->getSetting('cer_pem_file');
        $fielPass = $model->organization->getSetting('fiel_pwd');

        switch(true) {  
            case $model instanceof Invoice:
                $ref = 'invoice';
                $refPivotColumn = 'pivot.'.$ref.'_product_id';
                break;
            case $model instanceof Quotation:
                $ref = 'quotation';
                $refPivotColumn = 'pivot.'.$ref.'_product_id';
                break;
            case $model instanceof Saleorder:
                $ref = 'sale';
                $refPivotColumn = 'pivot.'.$ref.'_order_product_id';
                break;
        }
                
        $serie = $ref . '_serie';
        $folio = $ref . '_number';
        $creatorData = [
            'Serie' => $model->{$serie},
            'Folio' => $model->{$folio},
            // 'Fecha' => $model->created_at->timezone('America/Tijuana')->format('Y-m-d\TH:i:s'),
            'Fecha' => now()->timezone('America/Tijuana')->format('Y-m-d\TH:i:s'),
            'FormaPago' => $model->payment_type,
            'MetodoPago' => $model->payment_method,
            'Moneda' => $model->currency,
            'TipoCambio' => $model->exchange_rate,
            'TipoDeComprobante' => 'I',
            'Exportacion' => '01', // No aplica
            'LugarExpedicion' => $model->organization->getSetting('zip_code'),
        ];
        $emisorData = [
            'Rfc' => strtoupper($model->organization->getSetting('sat_rfc')),
            'Nombre' => strtoupper($model->organization->getSetting('sat_name')),
            'RegimenFiscal' => $model->organization->getSetting('fiscal_regimen'),
        ];
        $receptorData = [
            'Rfc' => strtoupper($model->companies->sat_rfc),
            'Nombre' => strtoupper($model->companies->sat_name),
            'UsoCFDI' => $model->cfdi_use,
        ];
        if($model->companies->isNational()) {
            $receptorData['RegimenFiscalReceptor'] = $model->companies->fiscal_regimen;
            $receptorData['DomicilioFiscalReceptor'] = $model->companies->zip_code;
        }
        else {
            $receptorData['ResidenciaFiscal'] = 'USA';
            $receptorData['NumRegIdTrib'] = '121585958';
        }
        if(!empty($cerPemFile)) {
            $certificado = new Certificado($cerPemFile);
            $creator = new CfdiCreator40($creatorData, $certificado);        
        } else {
            $creator = new CfdiCreator40($creatorData);        
        }
        $comprobante = $creator->comprobante();
        $comprobante->addEmisor($emisorData);
        $comprobante->addReceptor($receptorData);
        $impLocal = new ImpuestosLocales();
        $hasImpLocal = false;

        $model->load('products','taxes');
        // Nodos - Conceptos
        foreach ($model->products as $product) {
            $pivotProduct = $product->pivot;
            $discount = $pivotProduct->total * $pivotProduct->discount / 100;
            $taxes = $model->taxes->where($refPivotColumn, $pivotProduct->id)->all();
            $discount = $pivotProduct->total * $pivotProduct->discount / 100;

            $noIdentificacion = $pivotProduct->sku;
            if(empty($pivotProduct->sku)) {
                $noIdentificacion = $pivotProduct->clave_sat;
                if (empty($pivotProduct->clave_sat)) {
                    $noIdentificacion = $product->clave_sat;
                }
            }
            
            $conceptoData = [
                'ClaveProdServ' => $pivotProduct->clave_sat ?? $product->clave_sat,
                'NoIdentificacion' => $noIdentificacion,
                'Cantidad' => $pivotProduct->quantity,
                'ClaveUnidad' => $pivotProduct->clave_unidad_sat ?? $product->clave_unidad_sat,
                'Unidad' => $pivotProduct->unidad_sat ?? $product->unidad_sat,
                'Descripcion' => $pivotProduct->description ?? $product->description,
                'ValorUnitario' => $pivotProduct->price,
                'Importe' => Format::number($pivotProduct->total, 6),
                'Descuento' => Format::number($discount, 6),//this field requires 6 decimals
                'ObjetoImp' => count($taxes) > 0 ? '02' : '01',
            ];
            $concepto = $comprobante->addConcepto($conceptoData);

            // Nodos - Impuestos            
            foreach ($taxes as $tax) {
                $taxSatArray = $tax->convertToSatArray($pivotProduct->total - $discount);
                if($tax->isTraslado()) {
                    if ($tax->isLocal()) {
                        //Agrega el impuesto local trasladado que será agregado a nivel del comprobante
                        $impLocal->addTrasladoLocal($taxSatArray);
                        $hasImpLocal = true;
                    } else {
                        //Agrega el impuesto federal trasladado al concepto
                        $concepto->addTraslado($taxSatArray);
                    }
                } else {
                    if ($tax->isLocal()) {
                        //Agrega el impuesto local retenido que será agregado a nivel del comprobante
                        $impLocal->addRetencionLocal($taxSatArray);
                        $hasImpLocal = true;
                    } else {
                        //Agrega el impuesto federal retenido al concepto
                        $concepto->addRetencion($taxSatArray);
                    }
                    }
            }
            //Node - Complemento Concepto
            if (isset($pivotProduct->complemento)) {
                $complementoConcepto = new Node(
                    'cfdi:ComplementoConcepto'
                );
                $iedu = new Node(
                    'iedu:instEducativas', // nombre del elemento raíz
                    [ // nodos obligatorios de XML y del nodo
                        'xmlns:iedu' => 'http://www.sat.gob.mx/iedu',
                        'xsi:schemaLocation' => 'http://www.sat.gob.mx/iedu'
                            . ' http://www.sat.gob.mx/sitio_internet/cfd/iedu/iedu.xsd',
                    ]
                );
                $iedu->addAttributes(json_decode($pivotProduct->complemento, true));
                $concepto->addChild($complementoConcepto)->addChild($iedu);
            }
        }
        if($hasImpLocal) {
            $comprobante->addComplemento($impLocal);
        }
        $creator->moveSatDefinitionsToComprobante();
        // add additional calculated information sumas sello
        $creator->addSumasConceptos(null, 2);
        if(!empty($keyPemFile)) {
            $creator->addSello($keyPemFile, $fielPass);
        }
        
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

    public static function getPago20Creator40($payment){
        $keyPemFile = $payment->organization->getSetting('key_pem_file');
        $cerPemFile = $payment->organization->getSetting('cer_pem_file');
        $fielPass = $payment->organization->getSetting('fiel_pwd');

        $creatorData = [
            'Serie' => $payment->payment_serie,
            'Folio' => $payment->payment_number,
            'Fecha' => $payment->created_at->timezone('America/Tijuana')->format('Y-m-d\TH:i:s'),
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
                        'BaseDR' => Format::number($impBaseDr, 6),
                        'ImpuestoDR' => $impuesto['Impuesto'],
                        'TipoFactorDR' => $impuesto['TipoFactor'],
                        'TasaOCuotaDR' => $impuesto['TasaOCuota'],
                        'ImporteDR' => Format::number($impBaseDr * $impuesto['TasaOCuota'], 6)
                    ];
                    $impuestosDoctoR->getTrasladosDR()->addTrasladoDR($trasladosDR[$i]);
                }
                /************
                El mismo caso no aplica para retenciones porque el nodo cfdi:Retencion 
                solo tiene los atributos Impuesto e Importe, y no TasaOCuota ni TipoFactor

                    foreach (($comprobanteCfdi->impuestos->retenciones)() as $i => $impuesto) {
                        $impuestoBaseComprobante = Format::number($impuesto['Importe'] / $impuesto['TasaOCuota'], 2); <==== ERROR
                        $impBaseDr = $paymentProportion * $impuestoBaseComprobante;
                        $retencionesDR[$i] = [
                            'BaseDR' => Format::number($impBaseDr, 6),
                            'ImpuestoDR' => $impuesto['Impuesto'],
                            'TipoFactorDR' => $impuesto['TipoFactor'],
                            'TasaOCuotaDR' => $impuesto['TasaOCuota'],
                            'ImporteDR' => Format::number($impBaseDr * $impuesto['TasaOCuota'], 6)
                        ];
                        $impuestosDoctoR->getRetencionesDR()->addRetencionDR($retencionesDR[$i]);
                } 
                ************/
                $conceptoRetenciones = array();
                if(!empty($comprobanteCfdi->impuestos->retenciones)) {
                    foreach (($comprobanteCfdi->conceptos)() as $concepto) {
                        foreach (($concepto->impuestos->retenciones)() as $retencion) {
                            array_push($conceptoRetenciones, $retencion);
                        }
                    }
                    foreach (self::groupTaxes($conceptoRetenciones) as $i => $impuesto) {
                        $impuestoBaseComprobante = Format::number($impuesto['Importe'] / $impuesto['TasaOCuota'], 2);
                        $impBaseDr = $paymentProportion * $impuestoBaseComprobante;
                        $retencionesDR[$i] = [
                            'BaseDR' => Format::number($impBaseDr, 6),
                            'ImpuestoDR' => $impuesto['Impuesto'],
                            'TipoFactorDR' => $impuesto['TipoFactor'],
                            'TasaOCuotaDR' => $impuesto['TasaOCuota'],
                            'ImporteDR' => Format::number($impBaseDr * $impuesto['TasaOCuota'], 6)
                        ];
                        $impuestosDoctoR->getRetencionesDR()->addRetencionDR($retencionesDR[$i]);
                    } 
                }

                foreach (self::groupTaxes($trasladosDR, 'DR', 'P', array('Importe', 'Base')) as $i => $impuesto) {
                    $trasladosP[$i] = $pago->getImpuestosP()->addTrasladosP()->addTrasladoP($impuesto);
                }
                foreach (self::groupTaxes($retencionesDR, 'DR', 'P', array('Importe', 'Base')) as $impuesto) {
                    $retencionesP[$i] = $pago->getImpuestosP()->addRetencionesP()->addRetencionP(Arr::only($impuesto, ['ImpuestoP', 'ImporteP']));
                }
            }
        }
        $totales = self::getPagos20ImpuestosTotales($retencionesP, $trasladosP);
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
    /**
     * * Group $taxes array based on these properties: TipoFactor, Impuesto and TasaOCuota
     * and sum up Importe 
     * $type values: '', 'DR', 'P'
     * $output_type values: '', 'DR', 'P'
     * @param array $taxes
     * @param string $type
     * @return array
     */
    public static function groupTaxes(
        array $taxes, 
        string $type = '', 
        string $output_type = '',
        array $sum_keys = self::SUM_TAX_KEYS,
        array $group_by_keys = self::GROUP_BY_TAX_KEYS) 
    {
        $output = array();
        $input_keys = array_combine(
            array_map(function($k)use($type){ return $k.$type; }, array_keys($group_by_keys)),
            $group_by_keys
        );

        foreach ($taxes as $value) {
            
            if ($value["TipoFactor$type"] != 'Exento') {
                $output_element = &$output[implode("_", array_only($taxes, $input_keys))];
                foreach($group_by_keys as $key) {
                    $output_element[$key.$output_type] = $value[$key.$type];
                }
                foreach($sum_keys as $key) {
                    !isset($output_element[$key.$output_type]) && $output_element[$key.$output_type] = 0;
                    $output_element[$key.$output_type] += $value[$key.$type];
                }                    
            }
        }

        return array_values($output);
    }

    public static function getPagos20ImpuestosTotales(array $retencionesP, array $trasladosP) {
        
        $impuestosTotales = array();

        if(count($retencionesP) > 0) {
            foreach($retencionesP as $impuesto) {
                $key = self::TAXES[$impuesto['ImpuestoP']];
                $impuestosTotales["TotalRetenciones$key"] = Format::number($impuesto['ImporteP'], 2);
                
            }
        }
        if(count($trasladosP) > 0) {
            foreach ($trasladosP as  $impuesto) {
                $key = self::TAXES[$impuesto['ImpuestoP']];
                $number = preg_replace(array('/0/', '/\./'), array('',''), $impuesto['TasaOCuotaP']);
                $impuestosTotales['TotalTrasladosBase'.$key.$number] = Format::number($impuesto['BaseP'], 2);
                $impuestosTotales['TotalTrasladosImpuesto'.$key.$number] = Format::number($impuesto['ImporteP'], 2);
            }
        }

        return $impuestosTotales;
        // TO-DO:
        // $impuestosTotales['TotalTrasladosBaseIVA0'] = '';
        // $impuestosTotales['TotalTrasladosImpuestoIVA0'] = '';
        // $impuestosTotales['TotalTrasladosBaseIVAExento'] = '';
    }

    public static function numToLetras($num, $currency = 'MXN', $fem = false, $dec = true)
    {
        $matuni[2]  = "DOS";
        $matuni[3]  = "TRES";
        $matuni[4]  = "CUATRO";
        $matuni[5]  = "CINCO";
        $matuni[6]  = "SEIS";
        $matuni[7]  = "SIETE";
        $matuni[8]  = "OCHO";
        $matuni[9]  = "NUEVE";
        $matuni[10] = "DIEZ";
        $matuni[11] = "ONCE";
        $matuni[12] = "DOCE";
        $matuni[13] = "TRECE";
        $matuni[14] = "CATORCE";
        $matuni[15] = "QUINCE";
        $matuni[16] = "DIECISEIS";
        $matuni[17] = "DIECISIETE";
        $matuni[18] = "DIECIOCHO";
        $matuni[19] = "DIECINUEVE";
        $matuni[20] = "VEINTE";
        $matunisub[2] = "DOS";
        $matunisub[3] = "TRES";
        $matunisub[4] = "CUATRO";
        $matunisub[5] = "QUIN";
        $matunisub[6] = "SEIS";
        $matunisub[7] = "SETE";
        $matunisub[8] = "OCHO";
        $matunisub[9] = "NOVE";

        $matdec[2] = "VEINT";
        $matdec[3] = "TREINTA";
        $matdec[4] = "CUARENTA";
        $matdec[5] = "CINCUENTA";
        $matdec[6] = "SESENTA";
        $matdec[7] = "SETENTA";
        $matdec[8] = "OCHENTA";
        $matdec[9] = "NOVENTA";
        $matsub[3]  = 'MILL';
        $matsub[5]  = 'BILL';
        $matsub[7]  = 'MILL';
        $matsub[9]  = 'TRILL';
        $matsub[11] = 'MILL';
        $matsub[13] = 'BILL';
        $matsub[15] = 'MILL';
        $matmil[4]  = 'MILLONES';
        $matmil[6]  = 'BILLONES';
        $matmil[7]  = 'DE BILLONES';
        $matmil[8]  = 'MILLONES DE BILLONES';
        $matmil[10] = 'TRILLONES';
        $matmil[11] = 'DE TRILLONES';
        $matmil[12] = 'MILLONES DE TRILLONES';
        $matmil[13] = 'DE TRILLONES';
        $matmil[14] = 'BILLONES DE TRILLONES';
        $matmil[15] = 'DE BILLONES DE TRILLONES';
        $matmil[16] = 'MILLONES DE BILLONES DE TRILLONES';

        //Zi hack
        $float = explode('.', $num);
        $num = $float[0];

        $num = trim((string)@$num);
        if ($num[0] == '-') {
            $neg = 'MENOS ';
            $num = substr($num, 1);
        } else
            $neg = '';
        while ($num[0] == '0') $num = substr($num, 1);
        if ($num[0] < 'L' or $num[0] > 9) $num = '0' . $num;
        $zeros = true;
        $punt = false;
        $ent = '';
        $fra = '';
        for ($c = 0; $c < strlen($num); $c++) {
            $n = $num[$c];
            if (!(strpos(".,'''", $n) === false)) {
                if ($punt) break;
                else {
                    $punt = true;
                    continue;
                }
            } elseif (!(strpos('0123456789', $n) === false)) {
                if ($punt) {
                    if ($n != '0') $zeros = false;
                    $fra .= $n;
                } else

                    $ent .= $n;
            } else

                break;
        }
        $ent = '     ' . $ent;
        if ($dec and $fra and !$zeros) {
            $fin = ' COMA';
            for ($n = 0; $n < strlen($fra); $n++) {
                if (($s = $fra[$n]) == '0')
                    $fin .= ' CERO';
                elseif ($s == '1')
                    $fin .= $fem ? ' UNA' : ' UN';
                else
                    $fin .= ' ' . $matuni[$s];
            }
        } else
            $fin = '';
        if ((int)$ent === 0) return 'CERO ' . $fin;
        $tex = '';
        $sub = 0;
        $mils = 0;
        $neutro = false;
        while (($num = substr($ent, -3)) != '   ') {
            $ent = substr($ent, 0, -3);
            if (++$sub < 3 and $fem) {
                $matuni[1] = 'UNA';
                $subcent = 'AS';
            } else {
                $matuni[1] = $neutro ? 'UN' : 'UNO';
                $subcent = 'OS';
            }
            $t = '';
            $n2 = substr($num, 1);
            if ($n2 == '00') {
            } elseif ($n2 < 21)
                $t = ' ' . $matuni[(int)$n2];
            elseif ($n2 < 30) {
                $n3 = $num[2];
                if ($n3 != 0) $t = 'I' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            } else {
                $n3 = $num[2];
                if ($n3 != 0) $t = ' Y ' . $matuni[$n3];
                $n2 = $num[1];
                $t = ' ' . $matdec[$n2] . $t;
            }
            $n = $num[0];
            if ($n == 1) {
                $t = ' CIENTO' . $t;
            } elseif ($n == 5) {
                $t = ' ' . $matunisub[$n] . 'IENT' . $subcent . $t;
            } elseif ($n != 0) {
                $t = ' ' . $matunisub[$n] . 'CIENT' . $subcent . $t;
            }
            if ($sub == 1) {
            } elseif (!isset($matsub[$sub])) {
                if ($num == 1) {
                    $t = ' MIL';
                } elseif ($num > 1) {
                    $t .= ' MIL';
                }
            } elseif ($num == 1) {
                $t .= ' ' . $matsub[$sub] . '?n';
            } elseif ($num > 1) {
                $t .= ' ' . $matsub[$sub] . 'ONES';
            }
            if ($num == '000') $mils++;
            elseif ($mils != 0) {
                if (isset($matmil[$sub])) $t .= ' ' . $matmil[$sub];
                $mils = 0;
            }
            $neutro = true;
            $tex = $t . $tex;
        }
        if ($currency == 'USD') {
            $currency = ' DÓLARES ';
        } else {
            $currency = ' PESOS ';
        }
        $tex = $neg . substr($tex, 1) . $fin;
        if (count($float) > 1) {
            $end_num = ucfirst($tex) . ' ' . $float[1] . '/100 ' . $currency;
        } else {
            $end_num = ucfirst($tex) . ' 00/100 ' . $currency;
        }
        return $end_num;
    }

    public static function printAddress()
    {
        $userRepository = new UserRepositoryEloquent(app());
        $organization = $userRepository->getOrganization();
        return $organization->getSetting('print_address');
    }
    
}
