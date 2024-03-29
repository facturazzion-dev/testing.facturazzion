<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;

class Saleorder extends Model
{
    use SoftDeletes, RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'sales_orders';
    protected $appends = ['start_date','expire_date'];

    public function date_format()
    {
        return config('settings.date_format');
    }

    public function setDateAttribute($date)
    {
        $this->attributes['date'] = Carbon::createFromFormat($this->date_format(), $date)->format('Y-m-d');
    }

    public function getStartDateAttribute()
    {
        if ('0000-00-00' == $this->date || '' == $this->date) {
            return '';
        } else {
            return date($this->date_format(), strtotime($this->date));
        }
    }

    public function setExpDateAttribute($exp_date)
    {
        $this->attributes['exp_date'] = Carbon::createFromFormat($this->date_format(), $exp_date)->format('Y-m-d');
    }

    public function getExpireDateAttribute()
    {
        if ('0000-00-00' == $this->exp_date || '' == $this->exp_date) {
            return '';
        } else {
            return date($this->date_format(), strtotime($this->exp_date));
        }
    }

    public function salesPerson()
    {
        return $this->belongsTo(User::class, 'sales_person_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'sales_order_products', 'saleorder_id', 'product_id')->withPivot('id','quantity', 'price','organization_id','company_id','user_id','sku','discount','total','clave_unidad_sat','unidad_sat','clave_sat','description')->withTimestamps();
    }

    public function taxes()
    {
        return $this->belongsToMany(Tax::class, 'sales_order_products_taxes', 'sale_order_id')->withPivot('sale_order_id','product_id','organization_id','company_id','user_id', 'tax_id', 'sale_order_product_id');
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
