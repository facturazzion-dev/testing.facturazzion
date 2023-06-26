<?php

namespace App\Repositories;

use App\Helpers\SatDoc;
use App\Models\Company;
use App\Models\Invoice;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Models\InvoiceProduct;
use App\Models\Product;
use CfdiUtils\Certificado\Certificado;
use CfdiUtils\CfdiCreator40;
use CfdiUtils\Cleaner\Cleaner;
use CfdiUtils\Elements\CartaPorte20\CartaPorte;
use CfdiUtils\Elements\ImpLocal10\ImpuestosLocales;
use CfdiUtils\Nodes\Node;
use CfdiUtils\Nodes\XmlNodeUtils;
use CfdiUtils\Utils\Format;
use Illuminate\Support\Facades\Log;
use PhpCfdi\CfdiToPdf\CfdiDataBuilder;
use SoapClient;
use SoapHeader;

class InvoiceRepositoryEloquent extends BaseRepository implements InvoiceRepository
{
    private $userRepository;
    private $taxRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Invoice::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams(){
        $this->userRepository = new UserRepositoryEloquent(app());
        $this->taxRepository = new TaxRepositoryEloquent(app());
    }

    public function getAll()
    {
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoices.companies','invoices.receivePayment', 'invoices.products','invoices.taxes');
        $invoices = $org->invoices        
        ->where('status','!=',trans('invoice.draft_invoice'))
        ->where('status', '!=', 'otro');
        return $invoices;
    }

    public function getOne()
    {
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoices.companies','invoices.receivePayment', 'invoices.products','invoices.taxes');
        $invoices = $org->invoices
        ->where('is_delete_list', '!=', 1);
        return $invoices;
    }

    public function withAll()
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->get();
        return $invoices;
    }

    public function updateOrCreateInvoice(array $data, $invoice_id = null){
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $taxes = $this->taxRepository->getAll();

        $equalValue = Invoice::where('organization_id', $organization->id)
            ->where('invoice_serie', $data['invoice_serie'])
            ->where('invoice_number', $data['invoice_number'])
            ->first();
        if ($equalValue !== null) {
            $invoiceData['invoice_number'] = $this->getAll()->last()->invoice_number + 1;
        }

        $invoiceData['user_id']= $user->id;
        $invoiceData['organization_id'] = $organization->id;
        $invoiceData['invoice_date'] = now()->format(config('settings.date_format'));
        $invoiceData['due_date'] = $invoiceData['invoice_date'];

        //check if company exists
        $company = Company::updateOrCreate(['id' => $data['company_id']], [
            'user_id'=> $user->id,
            'organization_id' => $organization->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'country_id' => $data['country_id'] ?? '142',
            'state_id' => $data['state_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'sat_name' => $data['sat_name'],
            'sat_rfc' => $data['sat_rfc'],
            'email' => $data['email'] ?? null,
            'street' => $data['street'] ?? null,
            'exterior_no' => $data['exterior_no'] ?? null,
            'interior_no' => $data['interior_no'] ?? null,
            'suburb' => $data['suburb'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'payment_type' => $data['payment_type'] ?? null,
            'cfdi_use' => $data['cfdi_use'] ?? null,
            'fiscal_regimen' => $data['fiscal_regimen'] ?? null,
            'zip_code' => $data['zip_code'] ?? null
        ]);
        
        $invoiceData['company_id'] = $company->id;
        $invoiceData['status'] = $data['status'] ?? null;
        $invoiceData['is_delete_list'] = $data['is_delete_list'] ?? 0; //default value
        $invoiceData['qtemplate_id'] = $data['qtemplate_id'] ?? 0; //default value
        $invoiceData['invoice_serie'] = $data['invoice_serie'];
        $invoiceData['invoice_number'] = $data['invoice_number'];
        $invoiceData['payment_method'] = $data['payment_method'];
        $invoiceData['currency'] = $data['currency'];
        $invoiceData['exchange_rate'] = $data['exchange_rate'];
        $invoiceData['payment_term'] = $data['payment_term'];
        $invoiceData['products'] = $data['products'];
        $invoiceData['terms_and_conditions'] = $data['terms_and_conditions'];
        $invoiceData['cfdi_use'] = $data['cfdi_use'];
        $invoiceData['payment_type'] = $data['payment_type'];
        /* TO-DO: 
            Make sure all these variables have the same name everywhere in
            the project, including the database to avoid mapping them:                 
            */
        $invoiceData['iva_included'] = isset($data['iva_toggle']) ? (bool) $data['iva_toggle'] : 0;
        $invoiceData['total'] = $data['subtotal'];
        $invoiceData['discount'] = $data['total_discount'];
        $invoiceData['tax_amount'] = $data['tax_iva_tra'];
        $invoiceData['grand_total'] = $data['subtotal'] - $data['total_discount'];
        $invoiceData['final_price'] = $data['total'];
        $invoiceData['unpaid_amount'] = $data['total'];
        
        $invoice = $this->updateOrCreate(['id' => $invoice_id], $invoiceData);
        $invoice->products()->detach();
        $invoice->taxes()->detach();
        foreach ($data['products'] as $product){   
            // if iva is included then update product's price
            if ($invoiceData['iva_included'] == 1) {
                $product['price'] = Format::number($product['total_amount'] / $product['quantity'], 6);
            }
            /* if the product exists and the invoice is not a draft
                 then decrements its stock quantity */
            if (!empty($product['product_id'])) {
                $productObj = Product::find($product['product_id']);
                if ($invoiceData['status'] != trans('invoice.draft_invoice')) {
                    $productObj->decrement('quantity_available', $product['quantity']);
                    $productObj->decrement('quantity_on_hand', $product['quantity']);
                }
            } else { 
                /* If the product doesn't exist.
                Save the product like draft. */
                $productObj = new Product();
                $productObj->user_id = $invoiceData['user_id'];
                $productObj->organization_id = $invoiceData['organization_id'];
                $productObj->sku = $product['sku'];
                $productObj->clave_sat = $product['clave_sat'];
                $productObj->description = $product['description'];
                $productObj->product_name = substr($product['description'], 190);
                $productObj->product_type = $product['unidad_sat'];
                $productObj->clave_unidad_sat = $product['clave_unidad_sat'];
                $productObj->unidad_sat = $product['unidad_sat'];
                $productObj->sale_price = $product['price'];
                $productObj->status = 'Borrador';
                $productObj->save();
                // Finish.
                $product['product_id'] = $productObj->id;
            }
            /* TO-DO: 
            Make sure all these variables have the same name everywhere in
            the project, including the database, to avoid mapping them:                 
            */
            // prepare additional data to be inserted into the intermediate table:
            $productAddData['user_id'] = $invoiceData['user_id'];
            $productAddData['organization_id'] = $invoiceData['organization_id'];
            $productAddData['company_id'] = $invoiceData['company_id'];
            $productAddData['sku'] = $product['sku'];
            $productAddData['description'] = $product['description'];
            $productAddData['quantity'] = $product['quantity'];
            $productAddData['price'] = $product['price'];
            $productAddData['discount'] = $product['discount'];
            $productAddData['total'] = $product['total_amount'];
            $productAddData['clave_sat'] = $product['clave_sat'];
            $productAddData['clave_unidad_sat'] = $product['clave_unidad_sat'];
            $productAddData['unidad_sat'] = $product['unidad_sat'];
            $productAddData['complemento'] = !empty($product['iedu_curp']) ?
                json_encode([
                    'version' => '1.0',
                    'CURP' => $product['iedu_curp'],
                    'autRVOE' => $product['iedu_rvoe'],
                    'nivelEducativo' => $product['iedu_niv_edu'],
                    'nombreAlumno' => $product['iedu_alumno'],
                    'rfcPago' => $product['iedu_rfc']
                ])
            : null;
            
            $invoice->products()->save($productObj, $productAddData);
            $invoice->refresh();
            $invoiceProduct = $invoice->products->last();

            if(!isset($product['taxes']))
		        continue;
            
            foreach ($product['taxes'] as $stringTax) {
                /* takes the tax id from the first value of 
                $stringTax: '803_002_Traslado_0.16_Tasa' */
                $taxId = explode("_", $stringTax)[0];
                $taxObj = $taxes->find($taxId);

                $taxAddData['user_id'] = $invoiceData['user_id'];
                $taxAddData['organization_id'] = $invoiceData['organization_id'];
                $taxAddData['company_id'] = $invoiceData['company_id'];
                $taxAddData['invoice_id'] = $invoice['id'];
                $taxAddData['product_id'] = $product['product_id'];
                $taxAddData['tax_id'] = $taxObj->id;
                $taxAddData['invoice_product_id'] = $invoiceProduct->pivot->id;

                $invoice->taxes()->save($taxObj, $taxAddData);
            }
        }
        
        return $invoice;
    }    
    
    public function invoiceDeleteList(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoices.companies','invoices.receivePayment');
        $invoices = $org->invoices->where('is_delete_list', 1);
        return $invoices;
    }

    public function draftedInvoice(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoices.companies','invoices.receivePayment', 'invoices.products','invoices.taxes');
        $invoices = $org->invoices        
        ->where('status','=',trans('invoice.draft_invoice'));
        return $invoices;
    }

    public function paidInvoice(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoices.companies','invoices.receivePayment');
        $invoices = $org->invoices
        // ->where('is_delete_list', 0)
        ->where('status','=',trans('invoice.paid_invoice'));
        return $invoices;
    }
    public function getAllOpen()
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->where([
            // ['is_delete_list','=',0],
            ['status','=',trans('invoice.open_invoice')]
        ])->get();
        return $invoices;
    }

    public function getAllOverdue()
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->where([
            // ['is_delete_list','=',0],
            ['status','=',trans('invoice.overdue_invoice')]
        ])->get();
        return $invoices;
    }

    public function getAllPaid()
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->where([
            // ['is_delete_list','=',0],
            ['status','=',trans('invoice.paid_invoice')]
        ])->get();
        return $invoices;
    }

    public function getAllForCustomer($company_id)
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->where([
            ['is_delete_list','=',0],
            ['company_id','=', $company_id],
        ])->get();
        return $invoices;
    }
    public function getAllOpenForCustomer($company_id)
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->where([
            // ['is_delete_list','=',0],
            ['company_id','=', $company_id],
            ['status','=',trans('invoice.open_invoice')]
        ])->get();
        return $invoices;
    }

    public function getAllOverdueForCustomer($company_id)
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->where([
            // ['is_delete_list','=',0],
            ['company_id','=', $company_id],
            ['status','=',trans('invoice.overdue_invoice')]
        ])->get();
        return $invoices;
    }

    public function getAllUnpaidPpdForCompany($company_id) {
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoices.receivePayment');
        $invoices = $org->invoices
            ->where('company_id', $company_id)
            ->where('payment_method', 'PPD')
            ->where('unpaid_amount', '>', 0)
            ->where('status','!=',trans('invoice.draft_invoice'))
            ->where('status','!=','otro')
            // ->get()
            ->map(function ($invoice) {
                $receivePayments = $invoice->receivePayment->where('is_delete_list', '!=', 1);
                $partiality = $receivePayments->count() + 1;
                
                return [
                    'id' => $invoice->id,
                    'invoice_serie' => $invoice->invoice_serie,
                    'invoice_number' => $invoice->invoice_number,
                    'uuid_sat' => $invoice->uuid_sat,
                    'invoice_date' => $invoice->invoice_date,
                    'unpaid_amount' => $invoice->unpaid_amount,
                    'paid_amount' => ($invoice->final_price - $invoice->unpaid_amount),
                    'final_price' => $invoice->final_price,
                    'currency' => $invoice->currency,
                    'partiality' => $partiality,
                    'exchange_rate' => $invoice->exchange_rate,
                ];
            });
        return $invoices;
    }

    public function getAllPaidForCustomer($company_id)
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->where([
            // ['is_delete_list','=',0],
            ['company_id','=', $company_id],
            ['status','=',trans('invoice.paid_invoice')]
        ])->get();
        return $invoices;
    }

    public function getMonth($created_at)
    {
        $invoices = $this->model->whereMonth('created_at', $created_at)->get();
        return $invoices;
    }

    public function getBetweenDates($start_date, $end_date)
    {
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('invoices.companies','invoices.receivePayment');
        $invoices = $org->invoices
        ->where('invoice_date', '>=',$start_date)
        ->where('invoice_date', '<=',$end_date)
        ->where('status','!=',trans('invoice.draft_invoice'))
        ->where('status', '!=', 'otro');
        return $invoices;
        // $this->generateParams();
        
        // $invoices = $this->userRepository->getOrganization()->invoices()->where([
        //     // ['invoice_date','>=', $start_date],
        //     // ['invoice_date','<=', $end_date],
        //     ['status','!=',trans('invoice.draft')]
        // ])->get();

        // Log::info($invoices);
        
        // return $invoices;
    }

    public function getInvoicesForCustomerByMonthYear($year,$monthno,$company_id)
    {
        $invoices = $this->model->whereYear('created_at', $year)->whereMonth('created_at', $monthno)->where([
            ['company_id','=', $company_id]
        ])->get();
        return $invoices;
    }

    public function getMonthYear($monthno,$year)
    {
        $this->generateParams();
        $invoices = $this->userRepository->getOrganization()->invoices()->whereYear('invoice_date', $year)->whereMonth('invoice_date', $monthno)->get();
        return $invoices;
    }

    public function timbrarCfdi40(CfdiCreator40 $creator, Invoice $invoice = null)
    {
        $this->settingsRepository = new SettingsRepositoryEloquent(app());
        $settings = $this->settingsRepository->getAll();
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

            // clean cfdi
            $xml = Cleaner::staticClean($xml_decodificado);

            // create the main node structure
            $comprobante = XmlNodeUtils::nodeFromXmlString($xml);

            $cfdiData = (new CfdiDataBuilder())->build($comprobante);

            //update invoice
            $invoice->cfdi_xml = $cfdi;
            $invoice->uuid_sat = $cfdiData->timbreFiscalDigital()['UUID'];
            $invoice->save();

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

    

    private function addCartaPorte($comprobante)
    {
        $cartaPorte = new CartaPorte([
            'TranspInternac'=>'No',
            'TotalDistRec'=>'1',
          ]);
          
          $cartaPorte->addUbicaciones();
          
          $cartaPorte->getUbicaciones()->addUbicacion([
            'IDUbicacion'=>'OR101010',
            'TipoUbicacion'=>'Origen', 
            'RFCRemitenteDestinatario'=>'ETO060405UI2',
            'FechaHoraSalidaLlegada'=>'2021-11-01T00:00:00',
           ])->addDomicilio([
            'Calle'=>'calle',
            'NumeroExterior'=>'211',
            'Colonia'=>'0347',
            'Localidad'=>'23', 
            'Referencia'=>'casa blanca 1',
            'Municipio'=>'004',
            'Estado'=>'COA',
            'Pais'=>'MEX',
            'CodigoPostal'=>'25350',
          ]);
          $cartaPorte->getUbicaciones()->addUbicacion([
          'IDUbicacion'=>'DE202020',
          'TipoUbicacion'=>'Destino',
          'RFCRemitenteDestinatario'=>'XAXX010101000',
          'FechaHoraSalidaLlegada'=>'2021-11-01T01:00:00',
          'DistanciaRecorrida'=>'1',
          ])->addDomicilio([
          'Calle'=>'calle',
          'NumeroExterior'=>'214',
          'Colonia'=>'0347',
          'Localidad'=>'23',
          'Referencia'=>'casa blanca 2',
          'Municipio'=>'004',
          'Estado'=>'COA',
          'Pais'=>'MEX',
          'CodigoPostal'=>'25350',
          ]);
          
          $cartaPorte->addMercancias([
          'PesoBrutoTotal'=>'1.0',
          'UnidadPeso'=>'KGM',
          'NumTotalMercancias'=>'1',
          ]);
          
          $cartaPorte->getMercancias()->addMercancia([
          'BienesTransp'=>'24141501',
          'Descripcion'=>'Productos de perfumería',
          'Cantidad'=>'1.0',
          'ClaveUnidad'=>'XBX',
          'PesoEnKg'=>'1.0',
          ])->addCantidadTransporta([
          'Cantidad'=>'1',
          'IDOrigen'=>'OR101010',
          'IDDestino'=>'DE202020',
          ]);
          
          $cartaPorte->getMercancias()->addAutotransporte([
          'PermSCT'=>'TPAF01', 
          'NumPermisoSCT'=>'NumPermisoSCT',
          ])->addIdentificacionVehicular([
          'ConfigVehicular'=>'VL',
          'PlacaVM'=>'plac892',
          'AnioModeloVM'=>'2020',
          ]);
          
          $cartaPorte->getMercancias()->getAutotransporte()->addSeguros([
          'AseguraRespCivil'=>'INBURSA', 
          'PolizaRespCivil'=>'321654',
          ]);
          
          
          $cartaPorte->addFiguraTransporte();
          
          $cartaPorte->getFiguraTransporte()->addTiposFigura([
          'TipoFigura'=>'01', 
          'RFCFigura'=>'VAAM130719H60', 
          'NumLicencia'=>'a234567890',
          ]);
          
          $comprobante->addComplemento($cartaPorte);

          return $comprobante;
    }
}
