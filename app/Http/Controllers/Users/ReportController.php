<?php

namespace App\Http\Controllers\Users;

use App\Exports\InvoicesExport;
use App\Helpers\Common;
use App\Helpers\SatDoc;
use App\Http\Controllers\Controller;
use App\Mail\SendReport;
use App\Repositories\InvoiceRepository;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use DataTables;
use DB;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    private $userRepository;
    private $settingsRepository;
    private $organizationSettingsRepository;
    private $invoiceRepository;

    protected $user;

    public function __construct(
        UserRepository $userRepository,
        SettingsRepository $settingsRepository,
        OrganizationSettingsRepository $organizationSettingsRepository,
        InvoiceRepository $invoiceRepository
    ) {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->settingsRepository = $settingsRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->invoiceRepository = $invoiceRepository;

        view()->share('type', 'report');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = trans('report.report');

        $end_date = now()->timezone('America/Tijuana')->format('d/m/Y');
        $start_date = $end_date;
        $report_type = ['invoices' => 'Facturas'];

        return view('user.report.index', compact('title', 'start_date', 'end_date', 'report_type'));
    }

    public function create(Request $request)
    {
        switch ($request->report_type) {
            case 'invoices':
                $export = $this->getInvoicesExportByDate($request->start_date, $request->end_date);
                return Excel::download($export, 'facturas.xlsx');
                break;
            case 'quotations':
                return $this->getQuotations($request);
                break;

            default:
                return null;
                break;
        }
    }

    private function getInvoicesExportByDate($start_date, $end_date)
    {

        if (!isset($start_date) && !isset($end_date)) {
            flash("Favor de seleccionar las fechas 'desde' y 'hasta' para generar el reporte")->error();
            return redirect()->back();
        }

        $from = Carbon::createFromFormat('d/m/Y', $start_date)->format('Y-m-d');
        $to = Carbon::createFromFormat('d/m/Y', $end_date)->format('Y-m-d');
        $invoices = $this->invoiceRepository
            ->getBetweenDates($from, $to)
            ->map(
                function ($invoice) {

                    if (isset($invoice->cfdi_xml)) {
                        $cfdiData = SatDoc::cfdiFromXmlString(base64_decode($invoice->cfdi_xml));
                        $emisor = $cfdiData->emisor();
                        $receptor = $cfdiData->receptor();
                        $comprobante = $cfdiData->comprobante();
                        $tfd = $cfdiData->timbreFiscalDigital();
                        $invoice_datetime = explode("T", $comprobante['Fecha']);

                        $taxes = $this->getTaxes($comprobante);

                        return [

                            'Status' => ($invoice->is_delete_list == 1) ? "Cancelada" : "Activa",
                            'Tipo' => 'Factura',
                            'Serie' => $invoice->invoice_serie,
                            'Folio' => $invoice->invoice_number,
                            'Fecha' => $invoice_datetime[0],
                            'Razón Social' => $receptor['Nombre'],
                            'RFC' => $receptor['Rfc'],
                            'Subtotal' => $comprobante['SubTotal'],
                            'Descuento' => $comprobante['Descuento'],
                            'IVA' => $taxes['IVA'],
                            'RetIVA' => '0',
                            'RetISR' => $taxes['RetISR'],
                            'Total' => $comprobante['Total'],
                            'Moneda' => $comprobante['Moneda'],
                            'Tipo Cambio' => $comprobante['TipoCambio'],
                            'Total TP' => floatval($comprobante['Total']) * floatval($comprobante['TipoCambio']),
                            'UUID' => isset($tfd['UUID']) ? $tfd['UUID'] : '',

                        ];
                    }
                }
            );

        if ($invoices->count() != 0) {
            return new InvoicesExport($invoices->toArray());
        } else {
            flash("No hay facturas en las fechas seleccionadas.")->error();
            return redirect()->back();
        }
    }

    public function downloadPdf($year_month)
    {
        $datetime = explode("-", $year_month);
        $start_date = '01' . '/' . $datetime[1] . '/' . $datetime[0];
        $end_date_temp = '01' . '/' . ($datetime[1] + 1) . '/' . $datetime[0];
        $end_date = Carbon::createFromFormat('d/m/Y', $end_date_temp)->subDays(1)->format('d/m/Y');

        $export = $this->getInvoicesExportByDate($start_date, $end_date);

        return Excel::download($export, 'facturas.pdf', \Maatwebsite\Excel\Excel::MPDF);
    }

    public function downloadXls($year_month)
    {
        $datetime = explode("-", $year_month);
        $start_date = '01' . '/' . $datetime[1] . '/' . $datetime[0];
        $end_date_temp = '01' . '/' . ($datetime[1] + 1) . '/' . $datetime[0];
        $end_date = Carbon::createFromFormat('d/m/Y', $end_date_temp)->subDays(1)->format('d/m/Y');

        $export = $this->getInvoicesExportByDate($start_date, $end_date);

        return Excel::download($export, 'facturas.xlsx');

    }

    public function ajaxCreatePdf($year_month)
    {
        return $year_month;
    }

    public function sendReport(Request $request)
    {
        $settings = $this->settingsRepository->getAll();
        $site_email = $settings['site_email'];
        $email_subject = 'Reporte de Facturazzion';
        $organizationSettings = $this->organizationSettingsRepository->getAll();
        $email_body = 'Hola, hemos generado tu reporte de ' . $organizationSettings['sat_name'];
        $to_company = $request->email;
        $message_body = Common::parse_template($email_body);
        $pdf_filename = 'Reporte-' . $request->year_month . '.pdf';
        $xls_filename = 'Reporte-' . $request->year_month . '.xlsx';
        $datetime = explode("-", $request->year_month);
        $start_date = '01' . '/' . $datetime[1] . '/' . $datetime[0];
        $end_date_temp = '01' . '/' . ($datetime[1] + 1) . '/' . $datetime[0];
        $end_date = Carbon::createFromFormat('d/m/Y', $end_date_temp)->subDays(1)->format('d/m/Y');

        $export = $this->getInvoicesExportByDate($start_date, $end_date);

        if (!empty($to_company) && false === !filter_var($site_email, FILTER_VALIDATE_EMAIL)) {
            if (false === !filter_var($to_company, FILTER_VALIDATE_EMAIL)) {

                try {
                    Mail::to($to_company)->send(new SendReport([
                        'from' => $site_email,
                        'subject' => $email_subject,
                        'message_body' => $message_body,
                        'pdf_filename' => $pdf_filename,
                        'xls_filename' => $xls_filename,
                        'pdf_content' => Excel::raw($export, \Maatwebsite\Excel\Excel::MPDF),
                        'xls_content' => Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX),
                    ]));
                } catch (Throwable $e) {
                    echo $e;
                }

            }

            echo "Reporte enviado";
        } else {
            echo "El Reporte no pude ser enviado";
        }
    }

    public function data()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['invoices.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        $invoices = DB::table('invoices')
            ->select(DB::raw('sum(final_price) as `total`'), DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as 'year_month'"))
            ->where('organization_id', $this->userRepository->getOrganization()->id)
            ->whereNotNull('uuid_sat')
            ->where('is_delete_list', '0')
            ->groupby('year_month')
            ->orderby('year_month', 'desc')
            ->get()
            ->pluck('total', 'year_month');

        $index = 0;

        foreach ($invoices as $year_month => $total) {

            $datetime = explode("-", $year_month);

            $invoices_array[$index]['year_month'] = $year_month;
            $invoices_array[$index]['year'] = $datetime[0];
            $invoices_array[$index]['month'] = $this->getMonthSpanish($datetime[1]);
            $invoices_array[$index]['total'] = number_format($total, 0, '.', ',');

            $index++;
        }

        return DataTables::of($invoices_array)->make();
    }

    private function generateParams()
    {
        $this->user = $this->getUser();
    }

    private function getMonthSpanish($month_number)
    {

        switch ($month_number) {
            case '1':
                return 'Enero';
                break;
            case '2':
                return 'Febrero';
                break;
            case '3':
                return 'Marzo';
                break;
            case '4':
                return 'Abril';
                break;
            case '5':
                return 'Mayo';
                break;
            case '6':
                return 'Junio';
                break;
            case '7':
                return 'Julio';
                break;
            case '8':
                return 'Agosto';
                break;
            case '9':
                return 'Septiembre';
                break;
            case '10':
                return 'Octubre';
                break;
            case '11':
                return 'Noviembre';
                break;
            case '12':
                return 'Diciembre';
                break;

            default:
                return 'Mes desconocido';
                break;
        }
    }

    //TO-DO: Mover esta funcion a la libreria de KINEDU/CFDI para usarla desde ahí (forksito?)
    private function getTaxes($comprobante)
    {

        $impuestos = $comprobante->searchNode('cfdi:Impuestos');

        $taxes['IVA'] = '0';
        $taxes['RetIVA'] = '0';
        $taxes['RetISR'] = '0';

        if (isset($impuestos)) {
            foreach ($impuestos->searchNodes('cfdi:Traslados', 'cfdi:Traslado') as $item) {

                if ($item['Impuesto'] == '002') {
                    $taxes['IVA'] += $item['Importe'];
                }
            }

            foreach ($impuestos->searchNodes('cfdi:Retenciones', 'cfdi:Retencion') as $item) {

                if ($item['Impuesto'] == '002') {
                    $taxes['RetIVA'] += $item['Importe'];
                }
                if ($item['Impuesto'] == '001') {
                    $taxes['RetISR'] += $item['Importe'];
                }
            }
        }

        return $taxes;
    }
}