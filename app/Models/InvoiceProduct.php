<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceProduct extends Model
{
    public $table = "invoices_products";

    protected $casts = [
        'quantity' => 'float',
        'price' => 'float',
    ];

    /* To access taxes for a concept. */
    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'invoices_products_taxes', 'tax_id', 'invoice_product_id')->withPivot('invoice_id','organization_id','company_id','user_id')->withTimestamps();
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
