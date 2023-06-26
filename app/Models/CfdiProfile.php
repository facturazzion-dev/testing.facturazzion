<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use \Venturecraft\Revisionable\RevisionableTrait;

class CfdiProfile extends Model
{
    use SoftDeletes,RevisionableTrait;

    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];
    protected $table = 'cfdi_profiles';

    
    public function cfdiProfileTaxes()
    {
        return $this->belongsToMany(Tax::class, 'cfdi_profile_taxes');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
