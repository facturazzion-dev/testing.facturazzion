<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\CompanyContact;
use App\Models\Company;

/**
 * Class CompanyContactsRepositoryEloquent.
 */
class CompanyContactsRepositoryEloquent extends BaseRepository implements CompanyContactsRepository
{

    private $organization;

    private $userRepository;

    private $organizationRepository;
    private $company;

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return CompanyContact::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
