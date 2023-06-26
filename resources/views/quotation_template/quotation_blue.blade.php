<!DOCTYPE>
<html lang="{{config('app.locale')}}">

<head>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta http-equiv="content-type" content="text-html; charset=utf-8">
    <title>{{ trans('quotation.quotation') }}</title>
    <?php
        if (! isset($comprobante)) {
            $comprobante = $cfdiData->comprobante();
        }
        if (! isset($emisor)) {
            $emisor = $cfdiData->emisor();
        }
        if (! isset($receptor)) {
            $receptor = $cfdiData->receptor();
        }
        $relacionados = $comprobante->searchNode('cfdi:CfdiRelacionados');
        $totalImpuestosTrasladados = $comprobante->searchAttribute('cfdi:Impuestos', 'TotalImpuestosTrasladados');
        $totalImpuestosRetenidos = $comprobante->searchAttribute('cfdi:Impuestos', 'TotalImpuestosRetenidos');
        $conceptos = $comprobante->searchNodes('cfdi:Conceptos', 'cfdi:Concepto');
        $informacionGlobal = $comprobante->searchNode('cfdi:InformacionGlobal');
        $conceptoCounter = 0;
        $conceptoCount = $conceptos->count();
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
        .subtitle {
            padding: 5px 5px;
            height: 12px;            
        }

        .titleQR {
            color: #444;
            background: #eee;
            padding: 5px 5px;
            font-weight: bold;
            width: 85%;

        }

        .qr-table {
            page-break-inside: avoid;
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

        .bg-#444 {
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

        .h-30 {
            height: 30px;
        }

        .px-10 {
            padding: 0 10px;
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
            overflow-wrap: anywhere;
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
                        <h1>Cotización</h1>
                        <table>
                            <tbody>
                                <td class="col-4">
                                    <div class="title text-right">Folio:</div>
                                    <div class="title text-right">Fecha:</div>
                                </td>
                                <td class="col-8">
                                    <div class="bg-gray text-left">
                                        <h4>{{ $comprobante['Serie'] }}{{ $comprobante['Folio'] }}</h4>
                                    </div>
                                    <div class="bg-gray text-left">{{ $comprobante['Fecha'] }}</div>
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
                            @if (isset($quotation->add_address))
                            @if ($quotation->add_address=='true')
                            <strong>Domicilio:</strong> <span class="org_address">{{ $quotation->full_address }}</span>
                            @endif
                            @endif
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
                <table>
                    <td class="col-6 text-left" valign="top">
                        <table>
                            <tr>
                                <td><strong>Nombre:</strong> </td>
                                <td><span>{{ $receptor['Nombre'] ?? '(No se especificó el nombre del receptor)' }}</span></td>
                            </tr>
                            <tr>
                                <td> <strong>RFC:</strong> </td>
                                <td><span class="sat_rfc">{{ $receptor['Rfc'] }}</span></td>
                            </tr>
                        </table>
                    </td>
                    @if ('' !== $receptor['DomicilioFiscalReceptor'])
                    <td class="col-6 text-left" valign="top">
                        <table>
                            <tr>
                                <td><strong>Domicilio:</strong></td>
                                <td><span class="org_address">{{ $receptor['DomicilioFiscalReceptor'] }}</span></td>
                            </tr>
                        </table>
                    </td>
                    @endif
                    @if ('' !== $receptor['RegimenFiscalReceptor'])
                    <td class="col-6 text-left" valign="top">
                        <table>
                            <tr>
                                <td><strong>Régimen fiscal:</strong></td>
                                <td><span class="org_address">{{ $catalogos->catRegimenFiscal($receptor['RegimenFiscalReceptor']) }}</span></td>
                            </tr>
                        </table>
                    </td>
                    @endif
                    @if ('' !== $receptor['ResidenciaFiscal'])
                    <td class="col-6 text-left" valign="top">
                        <table>
                            <tr>
                                <td><strong>Residencia fiscal:</strong></td>
                                <td><span class="org_address">{{ $receptor['ResidenciaFiscal'] }}</span></td>
                            </tr>
                        </table>
                    </td>
                    @endif
                    @if ('' !== $receptor['NumRegIdTrib'])
                    <td class="col-6 text-left" valign="top">
                        <table>
                            <tr>
                                <td><strong>Residencia fiscal:</strong></td>
                                <td><span class="org_address">{{ $receptor['NumRegIdTrib'] }}</span></td>
                            </tr>
                        </table>
                    </td>
                    @endif
                </table>
            </div>
            
            <div>
                <table>
                    <tbody>
                        <td class="col-2">
                            <div class="title text-left">Uso del CFDI:</div>
                            <div class="title text-left">Método de pago:</div>
                            <div class="title text-left">Forma de pago:</div>
                        </td>
                        <td class="col-4">
                            <div class="bg-gray text-left">{{ $catalogos->catUsoCFDI($receptor['UsoCFDI']) }}</div>
                            <div class="bg-gray text-left">{{ $catalogos->catMetodoPago($comprobante['MetodoPago']) }}</div>
                            <div class="bg-gray text-left">{{ $catalogos->catFormaPago($comprobante['FormaPago']) }}</div>
                        </td>
                        <td class="col-2">
                            <div class="title text-left">Moneda:</div>
                            <div class="title text-left">Tipo de cambio:</div>
                            <div class="title text-left">Condiciones de pago:</div>
                        </td>
                        <td class="col-4">
                            <div class="bg-gray text-left">{{ $comprobante['Moneda'] }}</div>
                            <div class="bg-gray text-left">{{ $comprobante['TipoCambio'] }}</div>
                            <div class="bg-gray text-left">{{ $comprobante['CondicionesDePago'] ?? '.' }}</div>
                        </td>
                    </tbody>
                </table>
            </div>

            <div class="clearfix m-t-10">
                <table>
                    <thead class="head">
                        <tr>
                            <th class="text-center no">
                                <div>No</div>
                                <div></div>
                            </th>
                            <th class="text-center clave-sat">
                                <div>(SAT) Clave</div>
                                <div>(Int) Clave</div>
                            </th>
                            <th class="text-center descripcion">
                                <div>Descripción del Concepto</div>
                                <div>SAT Descripción</div>
                            </th>
                            <th class="text-center unidad-sat">
                                <div>Unidad</div>
                                <div>SAT Unidad</div>
                            </th>
                            <th class="text-center cantidad">
                                <div>Cant</div>
                                <div></div>
                            </th>
                            <th class="text-center precio">
                                <div>Precio Unitario</div>
                                <div></div>
                            </th>
                            <th class="text-center descuento">
                                <div>Descuento</div>
                                <div></div>
                            </th>
                            <th class="text-center importe">
                                <div>Importe</div>
                            </th>

                        </tr>
                    </thead>
                    <tbody class="product-table">
                        <?php
                            $conceptoTraslados = array();
                            $conceptoRetenciones = array();
                        ?>
                        @foreach ($conceptos as $key => $concepto)
                            @php
                                array_push($conceptoTraslados, $concepto->searchNodes('cfdi:Impuestos', 'cfdi:Traslados', 'cfdi:Traslado'));
                                array_push($conceptoRetenciones, $concepto->searchNodes('cfdi:Impuestos', 'cfdi:Retenciones', 'cfdi:Retencion'));
                            @endphp
                        <tr>
                            <td class="text-left">{{($key+1)}}</td>
                            <td class="text-center">
                                <div>{{$concepto['ClaveProdServ']}}</div>
                                <hr style="height:1px;border-width:0;color:gray;background-color:gray">
                                <div>{{$concepto['NoIdentificacion']}}</div>
                            </td>
                            <td class="text-center">
                                <div><p id = "description">{{$concepto['Descripcion']}}
                                </p></div>

                            </td>
                            <td class="text-left">
                                <div>{{$concepto['ClaveUnidad']}}</div>
                                <div>{{$concepto['Unidad'] ?? '(ninguna)'}}</div>
                            </td>
                            <td class="text-center">
                                <div>{{$concepto['Cantidad']}}</div>

                            </td>
                            <td class="text-center">
                                <div>${{$concepto['ValorUnitario']}}</div>

                            </td>
                            <td class="text-center">
                                <div>${{$concepto['Descuento'] ?? 'ninguno'}}</div>

                            </td>
                            

                            <td id="numero" class="text-right"><div>${{$concepto['Importe']}}</div></td>

                        </tr>
                        @endforeach                        
                    </tbody>
                </table>
            </div>
            
        @foreach ($comprobante->searchNodes('cfdi:Conceptos', 'cfdi:Concepto','cfdi:ComplementoConcepto') as $key => $complemento)
            
            
            <div class="clearfix m-t-10">
                <table>
                    <thead class="head">
                        <tr>
                            <th class="text-center importe">
                                <div>IEDU Versión</div>                                
                            </th>
                            <th class="text-center descripcion">
                                <div>Nombre del Alumno</div>                                
                            </th>
                            <th class="text-center ">
                                <div>CURP</div>                                
                            </th>
                            <th class="text-center ">
                                <div>Nivel Educativo</div>                                
                            </th>
                            <th class="text-center ">
                                <div>RVOE</div>                                
                            </th>                            
                        </tr>
                    </thead>
                    <tbody class="product-table">
                        @foreach ($comprobante->searchNodes('cfdi:Conceptos', 'cfdi:Concepto','cfdi:ComplementoConcepto','iedu:instEducativas') as $key => $iedu)
                        <tr>
                            <td class="text-center">
                                <div>{{$iedu['version']}}</div>
                            </td>
                            <td class="text-center">
                                <div>{{$iedu['nombreAlumno']}}</div>
                            </td>
                            <td class="text-center">
                                <div>{{$iedu['CURP']}}</div>
                            </td>
                            <td class="text-center">
                                <div>{{$iedu['nivelEducativo']}}</div>
                            </td>
                            <td class="text-center">
                                <div>{{$iedu['autRVOE']}}</div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            

        @endforeach
            <div class="m-t-5">
                <div class="form-group">
                    <div>
                        <br><Strong>Comentarios :<strong>
                    </div>
                    {{ $model->terms_and_conditions ?? '' }}
                </div>
                <table>
                    <tbody>
                        <td class="col-10">
                            <div class="subtitle t-b-5 text-right"></div>
                            <div class="subtitle t-b-5 text-right"></div>
                            @if ($totalImpuestosTrasladados !== '')
                                <div class="subtitle t-b-5 text-right"></div>
                                @foreach ($comprobante->searchNodes('cfdi:Impuestos', 'cfdi:Traslados','cfdi:Traslado') as $key => $impuesto)
                                    @if ($loop->index != 0 )
                                    <div class="subtitle t-b-5 text-right"></div>
                                    @endif
                                @endforeach
                            @endif
                            @if ($totalImpuestosRetenidos != '')
                                <div class="subtitle t-b-5 text-right"></div>
                            @endif
                            @if ($comprobante->searchAttribute('cfdi:Complemento', 'implocal:ImpuestosLocales', 'TotaldeRetenciones') != '')
                                <div class="subtitle t-b-5 text-right"></div>
                            @endif
                        </td>
                        <td class="col-4">
                            <div class="title t-b-5 text-left">Subtotal</div>
                            <div class="title t-b-5 text-left">Descuento</div>
                            <?php
                            $arrayTraslados = groupTaxes($conceptoTraslados);
                            $arrayRetenciones = groupTaxes($conceptoRetenciones);
                            ?>
                            @foreach ($arrayTraslados as $impuesto)
                                <div class="title t-b-5 text-left">
                                    <p>{{$impuesto['Impuesto'] == '002' ? 'IVA' : 'IEPS' }} {{formatNum($impuesto['TasaOCuota']*100)}}% </p>
                                </div>
                            @endforeach
                            @foreach ($arrayRetenciones as $impuesto)
                                <div class="title t-b-5 text-left">
                                    <p>
                                        @if($impuesto['Impuesto'] == '001') {{'ISR'}}
                                        @elseif($impuesto['Impuesto'] == '002') {{'IVA'}}
                                        @else {{'IEPS'}}
                                        @endif
                                        {{formatNum($impuesto['TasaOCuota']*100)}}%
                                    </p>
                                </div>
                            @endforeach
                            @if ($comprobante->searchAttribute('cfdi:Complemento', 'implocal:ImpuestosLocales', 'TotaldeRetenciones') != '')
                                <div class="title t-b-5 text-left">
                                @foreach ($comprobante->searchNodes('cfdi:Complemento', 'implocal:ImpuestosLocales','implocal:RetencionesLocales') as $key => $impuesto)
                                        <p>
                                            RET {{$impuesto['ImpLocRetenido']}} {{$impuesto['TasadeRetencion']}}%
                                        </p>
                                    @endforeach
                                </div>
                            @endif
                            <div class="title t-b-5 text-left">Total</div>
                        </td>
                        <td class="col-4">
                            <div class="bg-gray t-b-5 t-p-5 text-right">${{formatNum($comprobante['SubTotal'])}}</div>
                            <div class="bg-gray t-b-5 t-p-5 text-right">${{formatNum($comprobante['Descuento'])}}</div>
                            @if ($totalImpuestosTrasladados != '')
                                <div class="bg-gray t-b-5 t-p-5 text-right">                                
                                        @foreach ($arrayTraslados as $impuesto)
                                        <p>${{formatNum($impuesto['Importe'])}}</p>                                            
                                        @endforeach                                                                
                                </div>
                            @endif
                            @if ($totalImpuestosRetenidos != '')
                                <div class="bg-gray t-b-5 t-p-5 text-right">                                
                                        @foreach ($arrayRetenciones as $impuesto)
                                        <p>${{formatNum($impuesto['Importe'])}}</p>                                            
                                        @endforeach                                                                
                                </div>
                            @endif
                            @if ($comprobante->searchAttribute('cfdi:Complemento', 'implocal:ImpuestosLocales', 'TotaldeRetenciones') != '')
                                <div class="bg-gray t-b-5 t-p-5 text-right">                                
                                @foreach ($comprobante->searchNodes('cfdi:Complemento', 'implocal:ImpuestosLocales','implocal:RetencionesLocales') as $key => $impuesto)
                                        <p>${{formatNum($impuesto['Importe'])}}</p>                                            
                                        @endforeach                                                                
                                </div>
                            @endif
                            <div class="bg-gray t-b-5 t-p-5 text-right"><strong>${{formatNum($comprobante['Total'])}}</strong></div>
                        </td>
                    </tbody>
                </table>
                <div>
                    <div class="amount"><strong>Importe con letras:</strong> {{ SatDoc::numToLetras($comprobante['Total'], $comprobante['Moneda']) }}</div>
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
                            <td class="col-6 text-center representation"></td>
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
