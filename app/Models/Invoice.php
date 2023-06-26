<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Invoice extends Model
{
    use SoftDeletes, RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'invoices';
    protected $appends = ['invoice_start_date','invoice_due_date'];

    public function date_format()
    {
        return config('settings.date_format');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function setInvoiceDateAttribute($invoice_date)
    {
        $this->attributes['invoice_date'] = Carbon::createFromFormat($this->date_format(), $invoice_date)->format('Y-m-d');
    }

    public function getInvoiceStartDateAttribute()
    {
        if ('0000-00-00' == $this->invoice_date || '' == $this->invoice_date) {
            return '';
        } else {
            return date($this->date_format(), strtotime($this->invoice_date));
        }
    }

    public function setDueDateAttribute($due_date)
    {
        $this->attributes['due_date'] = Carbon::createFromFormat($this->date_format(), $due_date)->format('Y-m-d');
    }

    public function getInvoiceDueDateAttribute()
    {
        if ('0000-00-00' == $this->due_date || '' == $this->due_date) {
            return '';
        } else {
            return date($this->date_format(), strtotime($this->due_date));
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function receivePayment()
    {
        return $this->belongsToMany(InvoiceReceivePayment::class, 'invoice_receive_payments_ppd', 'invoice_id', 'invoice_receive_payment_id')->withPivot('id', 'user_id', 'organization_id', 'customer_id', 'company_id', 'invoice_serie', 'invoice_folio', 'invoice_uuid', 'invoice_date', 'invoice_currency', 'total', 'partiality')->withTimestamps();
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'invoices_products', 'invoice_id', 'product_id')->withPivot('id','quantity', 'price','organization_id','company_id','user_id','sku','discount','total','clave_unidad_sat','unidad_sat','clave_sat','description','complemento')->withTimestamps();
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'invoices_products_taxes')->withPivot('invoice_id','product_id','organization_id','company_id','user_id', 'tax_id', 'invoice_product_id')->withTimestamps();
    }

    public function companies()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
