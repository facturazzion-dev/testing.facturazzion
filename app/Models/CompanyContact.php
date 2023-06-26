<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;


class CompanyContact extends Model implements Transformable
{
    use SoftDeletes,RevisionableTrait;
    use TransformableTrait;


    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'company_contacts';

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    
}
