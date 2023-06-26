<?php

namespace App\Repositories;

use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use App\Models\CompanyBank;
use App\Models\Company;

/**
 * Class CompanyBanksRepositoryEloquent.
 */
class CompanyBanksRepositoryEloquent extends BaseRepository implements CompanyBanksRepository
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
        return CompanyBank::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }
    
}
