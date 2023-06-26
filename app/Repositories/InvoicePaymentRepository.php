<?php namespace App\Repositories;

use App\Models\InvoiceReceivePayment;
use CfdiUtils\CfdiCreator40;

interface InvoicePaymentRepository
{
    public function getAll();

    public function getAllGroupBySatPayment();

    public function updateOrCreatePayment(array $data, $payment_id = null);

    public function createPayment(array $data);

    public function getAllPaidForCustomer($company_id);

    public function getAllPaidGroupBySatPaymentForCustomer($company_id);

    public function getMonth($created_at);
    
    public function getMonthYear($monthno,$year);

    public function getCfdiCreator40(InvoiceReceivePayment $payment);

    public function timbrarCfdi40(CfdiCreator40 $creator, InvoiceReceivePayment $payment = null);
}