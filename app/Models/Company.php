<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Company extends Model implements Transformable
{
    use SoftDeletes,RevisionableTrait;
    use TransformableTrait;


    protected $dates = ['deleted_at'];
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function contactPerson()
    {
        return $this->belongsTo(User::class, 'main_contact_person');
    }

    public function salesTeam()
    {
        return $this->belongsTo(Salesteam::class, 'sales_team_id');
    }

    public function cities()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function contacts()
    {
        return $this->hasMany(CompanyContact::class);
    }

    public function banks()
    {
        return $this->hasMany(CompanyBank::class);
    }

    public function getFullAddress()
    {
        $street = isset($this->street) ? $this->street : '';
        $exterior_no = isset($this->exterior_no) ? $this->exterior_no : '';
        $interior_no = isset($this->interior_no) ? $this->interior_no : '';
        $suburb = isset($this->suburb) ? $this->suburb : '';
        $city = ($this->city_id !== null && $this->city_id != '') ? $this->cities->name:'';
        $state = ($this->state_id !== null && $this->state_id != '') ? $this->state->name:'';
        $country = ($this->country_id !== null && $this->country_id != '') ? $this->country->name:'';
        $zip_code = isset($this->zip_code) ? $this->zip_code : '';
        
        $address = $street.' '.$exterior_no.' '.$interior_no.' '.$suburb.' '.$city.' '.$state.' '.$country.' '.$zip_code;
        
        return $address;
    }

    public function isNational()
    {
        return $this->country_id === 142; //Country id for Mexico
    }

    public function getSatRfcAttribute($value)
    {
        return strtoupper($value);
    }
}
