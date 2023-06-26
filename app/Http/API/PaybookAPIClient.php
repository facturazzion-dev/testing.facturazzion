<?php

namespace App\Http\API;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Log;

Class PaybookAPIClient
{

	private $client = null;
	const API_URL = "https://sync.paybook.com/v1/";
	
	var $auth_key;
	var $auth_email;
	var $accessToken;

	public function __construct($email,$key){
		$this->auth_email = $email;
		$this->auth_key = $key;
		$this->client = new Client();
	}

	public function prepare_access_token(){
		try{
			$url = self::API_URL . "sessions?api_key=".$this->auth_key;
			$data = ['email' => $this->auth_email];
			$response = $this->client->post($url, ['query' => $data]);
			$result = json_decode($response->getBody()->getContents());
			$this->accessToken = $result->token;
		}
		catch (RequestException $e){
			$response = $this->StatusCodeHandling($e);
			return $response;
		}
	}

	public function stamp_invoice(){
		try{
			$url = "https://sync.paybook.com/v1/invoicing/mx/invoices";
			$data = [				
						"api_key" => "e50faa7204703e26390ea4ef83af2785",
						"id_user" => "5b957acc0c212a95288b456e",
						"id_provider"=>"acme",
						"invoice_data"=> [
							"Version"=> "3.3",
							"Serie"=> "A",
							"Folio"=> "36",
							"Fecha"=> "2018-09-13T15=>11=>37",
							"FormaPago"=> "99",
							"CondicionesDePago"=>"3 meses",
							"SubTotal"=> "50",
							"Descuento"=> "0",
							"Moneda"=>"MXN",
							"Total"=>"58",
							"TipoDeComprobante"=> "I",
							"MetodoPago"=>"PUE",
							"LugarExpedicion"=> "05000",
							"CfdiRelacionados" => [
						        "TipoRelacion"=> "01",
						        "CfdiRelacionado"=> 
					        	[
					            	"CfdiRelacionado"=> [
					                "UUID"=> "560a8451-a29c-41d4-a716-544676554400"
					           	 	]
					           	], 
					           	[
					            	"CfdiRelacionado"=> [
					                "UUID"=> "560a8451-a29c-41d4-a716-544676554400"
					           	 	]
					           	]
									
							],
							"Emisor"=> [
								"Rfc"=> 	"AAA010101AAA",
								"Nombre"=> "Manuel Alejandro",
								"RegimenFiscal"=> "601"
							],

							"Receptor"=>[
								"Rfc"=> "XAXX010101000",
								"Nombre"=>"Pedro Perez Hernandez",
								"UsoCFDI"=> "D01"
							],
							"Conceptos" => [
								"Concepto" => [												
									"Concepto" => [
							            "ClaveProdServ"=> "01010101",
							            "NoIdentificacion"=> "UT421510 ",
							            "Cantidad"=> "1",
							            "ClaveUnidad"=> "H87",
							            "Unidad"=> "PZA",
							            "Descripcion"=> "ARTICULO",
							            "ValorUnitario"=> "50",
							            "Importe"=> "50",
							            "Descuento"=> "0",
							            "Impuestos"=> [
							                "Retenciones"=> [
							                    "Retencion"=> [
							                        "Retencion"=> [
							                            "Base"=> "50",
							                            "Impuesto"=> "002",
							                            "TipoFactor"=> "Tasa",
							                            "TasaOCuota"=> "0.160000",
							                            "Importe"=> "8"
							                        ]
							                    ]
							                ]
							            ],
							            "InformacionAduanera"=> [
							                "NumeroPedimento"=> "15  48  3009  0001234"
							            ],
							            "CuentaPredial"=> [
							                "Numero"=> "112212"
							            ],
							            "ComplementoConcepto" => [],
							            "Parte"=> [
						                    "Parte"=> [
						                        "ClaveProdServ"=> "41116401",
						                        "NoIdentificacion"=> "3nn",
						                        "Cantidad"=> "1",
						                        "Unidad"=> "Piezas",
						                        "Descripcion"=> "Martillo",
						                        "ValorUnitario"=> "8",
						                        "Importe"=> "8",
							                    "InformacionAduanera"=> [
							                        "NumeroPedimento"=> "15  48  3009  0001234"
							                    ]
						                    ]
						                ]
							        ]									    
								]
							],
						    "Impuestos"=> [
						        "TotalImpuestosRetenidos"=> "0",
						        "TotalImpuestosTrasladados"=> "8",
						        "Traslados"=> [
						            "Traslado"=> [							            	
							                "Traslado"=> [
							                    "Impuesto"=> "002",
							                	"TipoFactor"=> "Tasa",
					                    		"TasaOCuota"=> "0.160000",
							                    "Importe"=> "8"
							                ]
						            ]
					        	]
					        ]
						]
					];
			
			// $client = new Client([
			//   'base_uri' => 'https://sync.paybook.com/v1/',
			// ]);



			// $response = $client->post('invoicing/mx/invoices', [
			//   'debug' => TRUE,
			//   'body' => $data,
			//   'headers' => [
			//     'Content-Type' => 'application/json',
			//   ]
			// ]);

			// $response = $this->client->post($url, ['json' => $data]);
			Log::info($response);
			return json_decode($response->getBody()->getContents());
			// $this->accessToken = $result->access_token;
		}
		catch (RequestException $e){
			$response = $this->StatusCodeHandling($e);
			return $response;
		}
	}

	public function StatusCodeHandling($e){
		Log:info($e->getResponse()->getStatusCode());
		if ($e->getResponse()->getStatusCode() == '400')
		{
			$this->prepare_access_token();
		}
		elseif ($e->getResponse()->getStatusCode() == '422')
		{
			$response = json_decode($e->getResponse()->getBody(true)->getContents());
			return $response;
		}
		elseif ($e->getResponse()->getStatusCode() == '500')
		{
			$response = json_decode($e->getResponse()->getBody(true)->getContents());
			return $response;
		}
		elseif ($e->getResponse()->getStatusCode() == '401')
			{
			$response = json_decode($e->getResponse()->getBody(true)->getContents());
			return $response;
		}
		elseif ($e->getResponse()->getStatusCode() == '403')
		{
			$response = json_decode($e->getResponse()->getBody(true)->getContents());
			return $response;
		}
		else
		{
			$response = json_decode($e->getResponse()->getBody(true)->getContents());
			return $response;
		}
	}

	Public function get_servers(){
		try
		{
			$url = self::API_URL . "/server";
			$option = array('exceptions' => false);
			$header = array('Authorization'=>'Bearer' . $this->accessToken);
			$response = $this->client->get($url, array('headers' => $header));
			$result = $response->getBody()->getContents();
			return $result;
		}
		catch (RequestException $e)
		{
			$response = $this->StatusCodeHandling($e);
			return $response;
		}
	}
}