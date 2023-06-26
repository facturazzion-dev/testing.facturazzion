<?php

namespace App\Repositories;

use App\Models\CfdiProfile;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CfdiProfileRepositoryEloquent extends BaseRepository implements CfdiProfileRepository
{
    private $userRepository;

    public function model()
    {
        return CfdiProfile::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    public function generateParams(){


        $this->userRepository = new UserRepositoryEloquent(app());
    }

    public function getAll()
    {
        $this->generateParams();
        $cfdi_profiles = $this->userRepository->getOrganization()->cfdiProfiles()->get();
        return $cfdi_profiles;
    }

    public function createCfdiProfile(array $data)
    {
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();

        $data['user_id']= $user->id;
        $data['organization_id']= $organization->id;
        $list = collect($data)->except('taxes')->toArray();

        $cfdi_profile = $this->create($list);
        

        $cfdi_profile->cfdiProfileTaxes()->attach($data['taxes']);
    }

    public function updateCfdiProfile(array $data,$cfdi_profile_id)
    {
        $this->generateParams();
        $cfdi_profile = $this->update($data,$cfdi_profile_id);
        $list =[];

        foreach ($data['tax_id']as $key =>$tax){
            if ($tax != "") {
                $list[$data['tax_id'][$key]] = $temp;
            }
        }

        $cfdi_profile->cfdiProfileTaxes()->sync($list);
    }

    public function deleteCfdiProfile($deleteCfdiProfile)
    {
        $this->generateParams();
        $cfdiProfileTax = $this->find($deleteCfdiProfile);
        $cfdiProfileTax->cfdiProfileTaxes()->detach();
        $this->delete($deleteCfdiProfile);
    }
}
