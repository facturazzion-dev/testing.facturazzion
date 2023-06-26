<?php

namespace App\Models;

use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class Product extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'products';

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if(empty($model->user_id)) {
                $user = Sentinel::getUser();
                $model->user_id = $user->id;
                $model->organization_id = $user->organizations()->first()->id;
            }
        });
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function invoiceProduct()
    {
        return $this->hasMany(InvoiceProduct::class, 'product_id');
    }

    public function quotationProduct()
    {
        return $this->hasMany(QuotationProduct::class, 'product_id');
    }

    public function qtemplates()
    {
        return $this->belongsToMany(Qtemplate::class, 'qtemplate_products');
    }

    public function salesOrderProduct()
    {
        return $this->hasMany(SaleorderProduct::class, 'product_id');
    }

    public function getPriceAttribute()
    {
        return $this->sale_price;
    }
}
