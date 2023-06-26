<?php

namespace App\Repositories;

use App\Models\Invoice;
use CfdiUtils\CfdiCreator40;
use Prettus\Repository\Contracts\RepositoryInterface;

interface InvoiceRepository extends RepositoryInterface
{
    public function getAll();

    public function withAll();

    public function updateOrCreateInvoice(array $data, $invoice_id = null);

    public function invoiceDeleteList();

    public function draftedInvoice();

    public function paidInvoice();

    public function getAllOpen();

    public function getAllOverdue();

    public function getAllPaid();

    public function getAllForCustomer($company_id);

    public function getAllOpenForCustomer($company_id);

    public function getAllOverdueForCustomer($company_id);

    public function getAllUnpaidPpdForCompany($company_id);

    public function getAllPaidForCustomer($company_id);

    public function getMonth($created_at);

    public function getBetweenDates($start_date, $end_date);

    public function getMonthYear($monthno,$year);

    public function getInvoicesForCustomerByMonthYear($year,$monthno,$company_id);

    public function timbrarCfdi40(CfdiCreator40 $creator, Invoice $invoice = null);    

}