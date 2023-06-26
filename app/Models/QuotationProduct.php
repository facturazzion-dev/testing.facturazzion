<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationProduct extends Model
{
    public $table = "quotations_products";
    /* To access taxes for a concept. */
    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'quotations_products_taxes', 'tax_id', 'quotation_product_id')->withPivot('quotation_id','organization_id','company_id','user_id')->withTimestamps();
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
