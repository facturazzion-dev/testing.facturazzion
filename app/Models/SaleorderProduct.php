<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class SaleorderProduct extends Model
{
    public $table = "sales_order_products";
    /* To access taxes for a concept. */
    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'sales_order_products_taxes', 'tax_id', 'sale_order_product_id')->withPivot('sale_order_id','organization_id','company_id','user_id')->withTimestamps();
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
