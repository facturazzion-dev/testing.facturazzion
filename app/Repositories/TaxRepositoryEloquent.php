<?php

namespace App\Repositories;

use App\Models\Tax;
use Illuminate\Support\Str;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TaxRepositoryEloquent extends BaseRepository implements TaxRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Tax::class;
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
        $taxes = $this->userRepository->getOrganization()->taxes()->get();
        return $taxes;
    }

    public function getAllNonLocal()
    {
        $this->generateParams();
        $taxes = $this->userRepository->getOrganization()->taxes()->nonLocal()->get();
        return $taxes;
    }

    public function getAllLocal()
    {
        $this->generateParams();
        $taxes = $this->userRepository->getOrganization()->taxes()->local()->get();
        return $taxes;
    }


    
}
