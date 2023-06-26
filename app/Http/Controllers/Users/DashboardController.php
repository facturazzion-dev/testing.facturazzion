<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Repositories\InvoicePaymentRepository;
use App\Repositories\InvoiceRepository;
use App\Repositories\UserRepository;
use DB;

class DashboardController extends Controller
{
    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;
    /**
     * @var InvoicePaymentRepository
     */
    private $invoicePaymentRepository;
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * DashboardController constructor.
     *
     * @param InvoiceRepository         $invoiceRepository
     * @param InvoicePaymentRepository  $invoicePaymentRepository
     * @param UserRepository            $userRepository
     */
    public function __construct(InvoiceRepository $invoiceRepository,
        InvoicePaymentRepository $invoicePaymentRepository,
        UserRepository $userRepository) {
        parent::__construct();
        $this->invoiceRepository = $invoiceRepository;
        $this->invoicePaymentRepository = $invoicePaymentRepository;
        $this->userRepository = $userRepository;
    }

    public function dashboard()
    {
        // $monthno = now()->format('m');
        // $year = now()->format('Y');

        // $invoices = DB::table('invoices')
        // ->select(DB::raw('payment_method'), DB::raw('sum(final_price) as `total`'), DB::raw("DATE_FORMAT(invoice_date, '%m-%Y') new_date"),  DB::raw('YEAR(invoice_date) year, MONTH(invoice_date) month'))
        // ->where('organization_id', $this->userRepository->getOrganization()->id)
        // ->whereNotNull('uuid_sat')
        // ->where('is_delete_list','0')
        // ->whereYear('invoice_date', $year)
        // ->groupby('year','month', 'new_date', 'payment_method')
        // ->get();

        // $payments = DB::table('invoice_receive_payments')
        // ->select(DB::raw('sum(payment_received) as `total`'), DB::raw("DATE_FORMAT(payment_date, '%m-%Y') new_date"),  DB::raw('YEAR(payment_date) year, MONTH(payment_date) month'))
        // ->where('organization_id', $this->userRepository->getOrganization()->id)
        // ->whereNotNull('uuid_sat')
        // ->whereNull('is_delete_list')
        // ->whereYear('payment_date', $year)
        // ->groupby('year','month', 'new_date','payment_method')
        // ->get();
                        
        // $invoices_pue = number_format(($invoices->where('payment_method', 'PUE')->sum('total')), 2, '.', ',');
        // $invoices_ppd = number_format(($invoices->where('payment_method', 'PPD')->sum('total')), 2, '.', ',');                        
        // $invoice_payments = number_format(($payments->sum('total')), 2, '.', ',');

        // $chart_data = [];

        // for ($i = now()->subMonth(1)->format('m'); $i >= 0; --$i) {
        //     $monthno = now()->subMonth($i)->format('m');
        //     $year = now()->subMonth($i)->format('Y');
        //     $chart_data[] =
        //         ['month' => $this->getMonthSpanish($monthno),
        //         'year' => $year,
        //         'invoices_pue' => $invoices->where('month', $monthno)->where('year', $year)->where('payment_method', 'PUE')->pluck('total')[0] ?? 0,
        //         'invoices_ppd' => $invoices->where('month', $monthno)->where('year', $year)->where('payment_method', 'PPD')->pluck('total')[0] ?? 0,
        //         'invoice_payments' => $payments->where('month', $monthno)->where('year', $year)->pluck('total')[0] ?? 0,
        //     ];
        // }

        $title = 'Panel de Información';

        view()->share('title', $title);

        return view('user.index');
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
}
