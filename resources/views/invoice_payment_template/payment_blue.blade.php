<!DOCTYPE>
<html lang="{{config('app.locale')}}">
<head>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta http-equiv="content-type" content="text-html; charset=utf-8">
    <title>Recibo de Pago</title>
    <?php
        // if (! isset($comprobante)) {
        //     $comprobante = $cfdiData->comprobante();
        // }
        // if (! isset($emisor)) {
        //     $emisor = $cfdiData->emisor();
        // }
        // if (! isset($receptor)) {
        //     $receptor = $cfdiData->receptor();
        // }
        $emisor = $comprobante->emisor;
        $receptor = $comprobante->receptor;
        $conceptos = $comprobante->conceptos;
        $pago = $comprobante->complemento->pagos->pago;
        $totales = $comprobante->complemento->pagos->totales;
        $doctosRelacionados = ($pago)('doctorelacionado');
        $impuestosP = $pago->impuestosp;
        $tfd = $comprobante->complemento->timbreFiscalDigital;
        $TAXES = App\Helpers\SatDoc::TAXES;
        // ($impuestosP->retencionesP)() as $impuesto)
        if (! isset($catalogos) || ! ($catalogos instanceof \PhpCfdi\CfdiToPdf\Catalogs\CatalogsInterface)) {
            $catalogos = new \PhpCfdi\CfdiToPdf\Catalogs\StaticCatalogs();
        }

        function formatNum($value){
            return number_format(floatval($value) , 2);
        }

        function groupTaxes($input)
        {
            $output = array();

            $count = count($input);

            for ($i=0; $i < $count; $i++) { 
                foreach ($input[$i] as $value) {

                    if ($value['TipoFactor'] != 'Exento') {

                        $output_element = &$output[$value['Impuesto']
                            . "_" . $value['TipoFactor']
                            . "_" . $value['TasaOCuota']];
                        $output_element['Impuesto'] = $value['Impuesto'];
                        $output_element['TipoFactor'] = $value['TipoFactor'];
                        $output_element['TasaOCuota'] = $value['TasaOCuota'];

                        !isset($output_element['Importe']) && $output_element['Importe'] = 0;

                        $output_element['Importe'] += $value['Importe'];
                    }
                }
            }

            return array_values($output);
        }
    ?>
    <style type="text/css">
        html,
        body,
        div,
        span,
        applet,
        object,
        iframe,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        blockquote,
        pre,
        a,
        abbr,
        acronym,
        address,
        big,
        cite,
        code,
        del,
        dfn,
        em,
        img,
        ins,
        kbd,
        q,
        s,
        samp,
        small,
        strike,
        strong,
        sub,
        sup,
        tt,
        var,
        b,
        u,
        i,
        center,
        dl,
        dt,
        dd,
        ol,
        ul,
        li,
        fieldset,
        form,
        label,
        legend,
        table,
        caption,
        tbody,
        tfoot,
        thead,
        tr,
        th,
        td,
        article,
        aside,
        canvas,
        details,
        embed,
        figure,
        figcaption,
        footer,
        header,
        hgroup,
        menu,
        nav,
        output,
        ruby,
        section,
        summary,
        time,
        mark,
        audio,
        video {
            margin: 0;
            padding: 0;
            border: 0;
            font-family: DejaVu Sans;
            font-size: 9.5px;
            vertical-align: baseline;
        }

        html {
            line-height: 1;
        }

        ol,
        ul {
            list-style: none;
        }

        a img {
            border: none;
        }

        article,
        aside,
        details,
        figcaption,
        figure,
        footer,
        header,
        hgroup,
        main,
        menu,
        nav,
        section,
        summary {
            display: block;
        }

        body {
            font-family: DejaVu Sans;
            font-size: 10px;
            margin: 0;
            padding: 0;
            color: #000;
        }
        body .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        h1 {
            font-size: 16px !important;
        }

        h4 {
            font-size: 11px !important;
        }        
        header {
            margin-top: 20px;
            padding: 0 5px 0;
        }
        header img {
            max-width: 300px;
            width: auto;
            object-fit: contain;
            max-height: 100px;
        }        
        img.site-logo {
            width: 200px;
            filter: grayscale(100%);
        }        
        section table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            font-size: 9px;
        }
        section table tbody.head {
            vertical-align: middle;
        }
        section table tbody.head th {
            color: #444;
            font-weight: bold;
            text-transform: uppercase;
        }
        section table tbody.body tr.total .total {
            font-size: 10px;
            font-weight: bold;
            color: #eee;
        }
        table .head .unit,
        table .head .total,
        table .unit,
        table .total,
        h1 {
            text-align: right;
        }
        table .head th {
            padding: 5px;
            background: #eee;
            color: #444;
            font-weight: bold;
            text-transform: capitalize;
        }

        table .head th div,
        table .product-table td div {
            font-size: 8px;
        }

        .t-b-5 {
            border-bottom: 5px solid #FFFFFF;
        }

        .title {
            color: #444;
            background: #eee;
            padding: 5px 5px;
            font-weight: bold;
        }

        .titleQR {
            color: #444;
            background: #eee;
            padding: 5px 5px;
            font-weight: bold;
            width: 85%;
        }

        table {
            width: 100%;
        }

        td {
            vertical-align: middle;
            word-wrap: break-word;
            /* All browsers since IE 5.5+ */
            overflow-wrap: break-word;
        }

        .sm-txt {
            font-size: 6px !important;
        }

        td.top-align {
            vertical-align: top !important;
        }

        table .body td {
            padding: 15px 10px;
            border-bottom: 5px solid #FFFFFF;
            border-right: 4px solid #FFFFFF;
            color: black;
        }

        table .product-table td {
            padding: 5px;
            border: 1px solid gray;
            color: black;
            font-size: 8px;
            border-style: solid;

        }

        table.grand-total {
            background: #7bd7f2;
            border-style: solid;

        }

        .no {
            width: 10px !important;
        }

        .clave-sat {
            width: 60px !important;
        }

        .descripcion {
            width: 210px !important;
        }

        .unidad-sat {
            width: 80px !important;
        }

        .cantidad {
            width: 50px !important;
        }

        .precio {
            width: 70px !important;
        }

        .descuento {
            width: 50px !important;
        }


        .importe {
            width: 80px !important;
        }

        .grand-total td {
            padding: 10px 10px;
            color: black;
            border-bottom: 5px solid #FFFFFF;
            border-right: 4px solid #FFFFFF;
        }

        .bg-white {
            background-color: #fff !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-left {
            text-align: left;
            padding: 5px 5px;
            word-wrap: break-word;
            /* All browsers since IE 5.5+ */
            overflow-wrap: break-word;
        }

        .text-center {
            text-align: center;
            padding: 5px 5px;
        }

        .t-p-5 {
            padding: 5px 5px;

        }

        .m-l-10 {
            margin-left: 10px;
        }

        .m-t-2 {
            margin-top: 2px;
        }

        .m-t-5 {
            margin-top: 5px;
        }

        .m-t-10 {
            margin-top: 10px;
        }

        .m-t-20 {
            margin-top: 20px;
        }

        .m-t-30 {
            margin-top: 30px;
        }

        .px-30 {
            padding: 0 30px;
        }

        .col-auto {
            width: auto;
            max-width: none;
        }

        .column {
            float: left;
        }

        .col-1 {
            width: 8.33333333%;
        }

        .col-2 {
            width: 16.66666667%;
        }

        .col-3 {
            width: 25%;
        }

        .col-4 {
            width: 33.33333333%;
        }

        .col-5 {
            width: 41.66666667%;
        }

        .col-6 {
            width: 50%;
        }

        .col-7 {
            width: 58.33333333%;
        }

        .col-8 {
            width: 66.66666667%;
        }

        .col-9 {
            width: 75%;
        }

        .col-10 {
            width: 83.33333333%;
        }

        .col-11 {
            width: 91.66666667%;
        }

        .col-12 {
            width: 100%;
        }

        .sat_rfc,
        .sat_uuid {
            text-transform: uppercase !important;
        }

        .org_address {
            text-transform: capitalize !important;
        }

        .footer {
            width: 100%;
            height: 80px;
            bottom: 0;
            position: fixed;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
        }

        /*ribbon*/
        .corner-ribbon {
            width: 190px;
            background: #e43;
            position: absolute;
            top: 25px;
            left: -50px;
            text-align: center;
            line-height: 20px;
            letter-spacing: 1px;
            color: #f0f0f0;
            transform: rotate(-45deg);
            -webkit-transform: rotate(-45deg);
        }

        /* Custom styles */

        .corner-ribbon.sticky {
            position: fixed;
        }

        .corner-ribbon.shadow {
            box-shadow: 0 0 3px rgba(0, 0, 0, .3);
        }

        /* Different positions */

        .corner-ribbon.top-left {
            top: 25px;
            left: -50px;
            transform: rotate(-45deg);
            -webkit-transform: rotate(-45deg);
        }

        span.red {
            color: #e43;
        }

        td {
            vertical-align: text-top;
        }

        #description {
            white-space: pre-line;
            word-wrap: break-word;
        }
        
        #hidden {
            visibility: hidden;
        }

        .pagare {
            font-size: 8px;
            border: 1px solid #EAE8E7; /*#F2F0EF*/
            padding: 3px;
        }

        .amount {
            width: auto;
            margin-top: 3px;
            font-size: 8px;
            text-align: right;
        }

        .representation{
            font-size: 8px;
        }
    </style>
</head>

<body>
@if(!empty($selloCancelacion))
    <h1 class="corner-ribbon top-left sticky shadow">Cancelado</h1>
@endif
<header class="clearfix">
    <div class="px-30">
        <table>
            <tbody>
                <td class="col-6 text-center" style="vertical-align: middle;">
                    @if (isset($settings['site_logo']))
                        <img class="logo" src="{{ public_path($settings['site_logo']) }}" alt="">
                    @endif
                </td>
                <td class="col-6">
                    <h1>(REP) RECIBO DE PAGO</h1>
                    <table>
                        <tbody>
                            <td class="col-4">
                                <div class="title text-right">Tipo:</div>
                                <div class="title text-right">Serie/Folio:</div>
                                <div class="title text-right">Fecha:</div>
                                <div class="title text-right">Folio Fiscal / UUID:</div>
                                <div class="title text-right">NoCertificado:</div>
                                <div class="title text-right">NoCertificadoSAT:</div>
                            </td>
                            <td class="col-8">
                                <div class="bg-gray text-left">
                                    <h4>{{ $catalogos->catTipoComprobante($comprobante['TipoDeComprobante']) }}</h4>
                                </div>
                                <div class="bg-gray text-left">
                                    <h4>{{ $comprobante['Serie'] }}{{ $comprobante['Folio'] }}</h4>
                                </div>
                                <div class="bg-gray text-left">{{ $comprobante['Fecha'] }}</div>
                                <div class="bg-gray text-left sat_uuid">
                                    {{ $tfd['UUID'] ?? '-' }}
                                </div>
                                <div class="bg-gray text-left sat_uuid">
                                    {{ $comprobante['NoCertificado'] ?? '-' }}
                                </div>
                                <div class="bg-gray text-left sat_uuid">
                                    {{ $tfd['NoCertificadoSAT'] ?? '-' }}
                                </div>
                            </td>
                        </tbody>
                    </table>
                </td>
            </tbody>
        </table>
    </div>
</header>

<section>
    <div class="px-30">
        <div class="m-t-10">
            <div class="col-4">
                <div class="title">Información del Emisor</div>
            </div>
            <table class="header">
                <tbody>
                    <td class="col-6 text-left">
                        <strong>Nombre:</strong> <span>{{ $emisor['Nombre'] }}</span><br>
                        <strong>RFC:</strong> <span class="sat_rfc">{{ $emisor['Rfc'] }}</span><br>
                    </td>
                    <td class="col-6 text-left bg-gray">
                        <strong>Lugar de Expedición:</strong> <span>{{ $comprobante['LugarExpedicion'] }}</span><br>
                        <strong>Regimen Fiscal:</strong> <span>{{ $catalogos->catRegimenFiscal($emisor['RegimenFiscal']) }}</span><br>
                    </td>
                </tbody>
            </table>
        </div>
        <div>
            <div class="col-4">
                <div class="title">Información del Receptor</div>
            </div>
            <table class="header">
                <tbody>
                    <td class="col-6 text-left">
                        <strong>Nombre:</strong> <span>{{ $receptor['Nombre'] ?? '(No se especificó el nombre del receptor)' }}</span><br>
                        <strong>RFC:</strong> <span class="sat_rfc">{{ $receptor['Rfc'] }}</span><br>
                        @if ('' !== $receptor['RegimenFiscalReceptor'])
                            <strong>Régimen fiscal:</strong> <span class="org_address">{{ $catalogos->catRegimenFiscal($receptor['RegimenFiscalReceptor']) }}</span><br>
                        @endif
                    </td>
                    <td class="col-6 text-left bg-gray">
                        <strong>Uso del CFDI:</strong> <span>{{ $receptor['UsoCFDI'] }}</span><br>
                        @if ('' !== $receptor['DomicilioFiscalReceptor'])
                            <strong>Domicilio Fiscal:</strong> <span class="org_address">{{ $receptor['DomicilioFiscalReceptor'] }}</span><br>
                        @endif
                        @if ('' !== $receptor['NumRegIdTrib'])
                            <strong>Residencia fiscal:</strong> <span class="org_address">{{ $receptor['NumRegIdTrib'] }}</span><br>
                        @endif
                    </td>
                </tbody>
            </table>
        </div>
        <div class="m-t-10">
            <div class="col-4">
                <div class="title">Información del Pago</div>
            </div>
            <table class="header">
                <tbody>
                    <td class="col-6 text-left ">
                        <strong>Forma de pago:</strong> <span>{{ $catalogos->catFormaPago($pago['FormaDePagoP']) }}</span><br>
                        <strong>Fecha de pago:</strong> <span>{{ $pago['FechaPago'] }}</span><br>
                        <strong></strong> <span></span><br>
                        <span></span>
                    </td>
                    <td class="col-6 text-left bg-gray">
                        <strong>Moneda: </strong><span>{{ $pago['MonedaP'] }}</span><br>
                        <strong>Tipo de Cambio: </strong><span>{{ $pago['TipoCambioP'] }}</span><br>
                        <strong>Monto: </strong>$ <span>{{ $pago['Monto'] }}</span><br>
                        <span></span>
                    </td>
                </tbody>
            </table>
        </div>
        <div class="clearfix">
            <table>
                <thead class="head">
                <tr>
                    <th class="text-center no">No</th>
                    <th class="text-center ">Detalle del Concepto</th>                    
                    <th class="text-center importe">Importe</th>
                </tr>
                </thead>
                <tbody class="body">
                @foreach ($conceptos() as $key => $concepto)
                    <tr>
                        <td class="text-left">{{($key+1)}}</td>
                        <td class="text-left">
                            <div class="content">
                                <p><strong>Descripcion: {{$concepto['Descripcion']}}</strong></p>
                                <p>
                                    <span>No identificación: {{ isset($concepto['NoIdentificacion']) ? $concepto['NoIdentificacion'] : 'ninguno'}},</span>
                                    <span>Clave SAT: {{$concepto['ClaveProdServ']}},</span>
                                    <span>Clave Unidad: {{$concepto['ClaveUnidad']}},</span>
                                </p>
                                <p>
                                    <strong>Cantidad: {{$concepto['Cantidad']}}</strong>,
                                    <strong>Valor unitario: {{$concepto['ValorUnitario']}}</strong>,
                                </p>                                
                            </div>
                        </td>
                        <td class="text-right"><strong>$ {{ $concepto['Importe']}}</strong></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Documentos relacionados. -->
        <div class="clearfix">
            <table>
                <thead class="head">
                <tr>
                    <th class="text-center no">No</th>
                    <th class="text-center">Facturas relacionadas</th>                    
                    <th class="text-center importe">Saldo insoluto</th>
                </tr>
                </thead>
                <tbody class="body">
                @foreach ($doctosRelacionados as $key => $docRelacionado)
                    <tr>
                        <td class="text-left m-txt">{{ ($key + 1) }}</td>
                        <!-- Posible vista de facturas relacionadas. -->
                        <td class="text-left">
                            <div class="content">
                                <table class="table-no-style m-t-0">
                                    <tr class="m-txt m-t-0">                                                      <!-- strtolower, change uppercase to lowercase -->
                                        <td style="border:none;padding:0;margin:0;"><strong>Id documento: </strong>{{ strtolower($docRelacionado['IdDocumento']) }}</td>
                                        <td style="border:none;padding:0;margin:0;"><strong>Serie: </strong>{{ $docRelacionado['Serie'] }}</td>
                                        <td style="border:none;padding:0;margin:0;"><strong>Folio: </strong>{{ $docRelacionado['Folio'] }}</td>
                                    </tr>
                                    <tr class="m-txt m-t-0">
                                        <td style="border:none;padding:0;margin:0;"><strong>Número Parcialidad: </strong>{{ $docRelacionado['NumParcialidad'] }}</td>
                                        <td style="border:none;padding:0;margin:0;"><strong>Moneda: </strong>{{ $docRelacionado['MonedaDR'] }}</td>
                                        <td style="border:none;padding:0;margin:0;"><strong>Equivalencia: </strong>{{ $docRelacionado['EquivalenciaDR'] }}</td>
                                    </tr>
                                    <tr class="m-txt m-t-0">
                                        <td style="border:none;padding:0;margin:0;"><strong>Saldo anterior: </strong>$ {{ $docRelacionado['ImpSaldoAnt'] }}</td>
                                        <td style="border:none;padding:0;margin:0;"><strong>Importe pagado: </strong>$ {{ $docRelacionado['ImpPagado'] }}</td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                        <td class="text-right m-txt"><strong>$ {{ $docRelacionado['ImpSaldoInsoluto'] }}</strong></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <!-- Fin documentos relacionados. -->
        <!-- Impuestos -->
        <div class="clearfix">
            <table>
                <thead class="head">
                    <tr>
                        <th class="text-center ">Impuestos del Pago</th>                        
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="bg-gray text-center t-p-5">                            
                            <table>
                                <tr>
                                    <th style="width: 20%">Tipo</th>
                                    <th style="width: 20%">Impuesto</th>
                                    <th style="width: 20%">Tipo factor</th>
                                    <th style="width: 20%">Tasa o cuota</th>
                                    <th style="width: 20%">Importe</th>
                                </tr>
                                @foreach (($impuestosP->trasladosP)() as $impuesto)
                                    <tr>
                                        <td class="text-center">Traslado</td>
                                        <td class="text-center">{{ $TAXES[$impuesto['ImpuestoP']]}}</td>
                                        <td class="text-center">{{$impuesto['TipoFactorP']}}</td>
                                        <td class="text-center">{{$impuesto['TasaOCuotaP']}}</td>
                                        <td class="text-center">{{$impuesto['ImporteP']}}</td>
                                    </tr>
                                @endforeach
                                @foreach (($impuestosP->retencionesP)() as $impuesto)
                                    <tr>
                                        <td class="text-center">Retención</td>
                                        <td class="text-center">{{$TAXES[$impuesto['ImpuestoP']]}}</td>
                                        <td class="text-center">{{$impuesto['TipoFactorP']}}</td>
                                        <td class="text-center">{{$impuesto['TasaOCuotaP']}}</td>
                                        <td class="text-center">{{$impuesto['ImporteP']}}</td>
                                    </tr>
                                @endforeach
                            </table>                                                        
                        </td>
                    </tr>                    
                </tbody>
            </table>
        </div>
        @if (!empty($totales['MontoTotalPagos']))
            <div class="m-t-5">
                <table>
                    <tbody>
                        <td class="col-6"></td>
                        <td class="col-3">
                            <div class="title t-b-5 text-right">Monto Total Pago</div>
                        </td>
                        <td class="col-2">
                            <div class="bg-gray t-b-5 t-p-5 text-right">
                            {{ $totales['MontoTotalPagos'] }}
                            </div>
                        </td>
                    </tbody>
                </table>
                <div>
                    <div class="amount"><strong>Importe con letras:</strong> {{ SatDoc::numToLetras($totales['MontoTotalPagos'], $pago['MonedaP']) }}</div>
                </div>
            </div>
        @endif
        <div class="clearfix m-t-2">
            <div class="column col-2">
                <div class="titleQR text-center">Código QR:</div>
                @production
                    @if (isset($cfdiData))
                    <img src="data:image/png;base64, {{ base64_encode(QrCode::format('png')->size(120)->generate($cfdiData->qrUrl())) }} ">
                    @endif
                @endproduction
            </div>
            <div class="column col-10">
                <div class="text-left"><strong>Sello CFDI: </strong></div>@if (isset($tfd['SelloCFD'])) <div class="text-left sm-txt">{{$tfd['SelloCFD']}}</div>
                    @endif
                <div class="text-left"><strong>Sello SAT: </strong></div>@if (isset($tfd['SelloSAT'])) <div class="text-left sm-txt">{{$tfd['SelloSAT']}}</div>
                    @endif
                <div class="text-left"><strong>Cadena original SAT:</strong></div>@if (isset($tfd['SourceString'])) <div class="text-left sm-txt">{{chunk_split($tfd['tfdSourceString'], 100)}}</div>
                    @endif
                <div class="text-left">
                    @if(!empty($selloCancelacion))
                        <span class="text-left red">Sello Cancelación: {{$selloCancelacion}}</span>
                    @endif
                    @if (isset($tfd['RfcProvCertif']))
                    <strong>RFC Proveedor de certificación:</strong>
                    <span>{{ $tfd['RfcProvCertif'] }}</span><br>
                    @endif
                    @if (isset($tfd['NoCertificadoSAT']))
                    <strong>Número de serie del certificado SAT:</strong>
                    <span>{{ $tfd['NoCertificadoSAT'] }}</span><br>
                    @endif
                    @if (isset($tfd['FechaTimbrado']))
                    <strong>Fecha de certificación:</strong>
                    <span>{{ $tfd['FechaTimbrado'] }}</span>
                    @endif
                </div>
            </div>
        </div>
           
    </div>
    <div class="footer">
        <div class="clearfix m-t-10">
            <table>
                <tbody>
                    <tr>
                        <td class="col-4">
                            <img class="site-logo" src="{{ public_path('/img/eficienzze.jpg')}}" alt="">
                        </td>
                        <td class="col-6 text-center representation">Este documento es una representación impresa de un CFDI a través de Internet
        versión {{$comprobante['Version']}}</td>
                        <td class="col-4">
                            <img class="site-logo" src="{{ public_path('/img/zfacturazzion.jpg')}}" alt="">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>
</body>

</html>
