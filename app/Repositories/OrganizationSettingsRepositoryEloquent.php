<?php

namespace App\Repositories;

use App\Models\OrganizationSetting;
use Illuminate\Support\Facades\Cache;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

use App\Models\Country;
use App\Models\State;
use App\Models\City;
class OrganizationSettingsRepositoryEloquent extends BaseRepository implements OrganizationSettingsRepository
{
    private $organization;

    private $userRepository;

    private $organizationRepository;

    public function getOrganization()
    {
        $this->userRepository = new UserRepositoryEloquent(app());

        $this->organizationRepository = new OrganizationRepositoryEloquent(app());

        $this->organization = $this->userRepository->getOrganization();
    }

    /**
     * Specify Model class name.
     */
    public function model()
    {
        return OrganizationSetting::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function getAll()
    {
        $this->getOrganization();

        // $values = Cache::rememberForever('organization'.$this->organization->id, function () {
        //     return $this->findByField('organization_id', $this->organization->id)->pluck('value', 'key');
        // });
        // if (count($values) < 5){
            $values = $this->get()->where('organization_id', $this->organization->id)->pluck('value', 'key');
        // }

        return $values;
    }

    public function getFullAddress()
    {
        $values = $this->getAll();
        $street = isset($values['street']) ? $values['street'] : '';
        $exterior_no = isset($values['exterior_no']) ? $values['exterior_no'] : '';
        $interior_no = isset($values['interior_no']) ? $values['interior_no'] : '';
        $suburb = isset($values['suburb']) ? $values['suburb'] : '';
        $city = empty($values['city_id']) ? '' : City::find($values['city_id'])->name;
        $state = empty($values['state_id']) ? '' : State::find($values['state_id'])->name;
        $country = empty($values['country_id']) ? '' : Country::find($values['country_id'])->name;
        $zip_code = isset($values['zip_code']) ? $values['zip_code'] : '';
        
        $address = $street.' '.$exterior_no.' '.$interior_no.' '.$suburb.' '.$city.' '.$state.' '.$country.' '.$zip_code;        
        
        return $address;
    }

    public function getKey($key, $default = null)
    {
        $values = $this->getAll();

        return $values[$key] ?? $default;
    }

    public function setKey($key, $value, $organization = null)
    {
        $this->getOrganization();

        $organization_id = $organization ?? $this->organization->id;

        if (!$organization_id) {
            return;
        }

        OrganizationSetting::updateOrCreate([
            'organization_id' => $organization_id,
            'key' => $key,
        ], [
            'value' => $value,
        ]);

        $settings = Cache::get('organization'.$organization_id) ?? [];
        $settings[$key] = $value;
        Cache::forever('organization'.$organization_id, $settings);

        if ('logo' == $key) {
            $this->organizationRepository->update(['logo' => $value], $organization_id);
        }

        if ('site_name' == $key) {
            $this->organizationRepository->update(['name' => $value], $organization_id);
        }

        if ('site_email' == $key) {
            $this->organizationRepository->update(['email' => $value], $organization_id);
        }

        if ('phone' == $key) {
            $this->organizationRepository->update(['phone_number' => $value], $organization_id);
        }

        return;
    }

    public function forgetKey($key)
    {
        $this->getOrganization();
        $values = $this->findWhere([
            'organization_id', $this->organization->id,
            'key', $key,
            ])->delete();

        $settings = Cache::get('organization'.$this->organization->id) ?? [];
        unset($settings[$key]);
        Cache::forever('organization'.$this->organization->id, $settings);

        return;
    }
}
