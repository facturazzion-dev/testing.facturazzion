<?php

namespace App\Repositories;

use App\Models\CClaveProdServ;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CClaveProdServRepositoryEloquent extends BaseRepository implements CClaveProdServRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return CClaveProdServ::class;
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
        $cClaveProdServs = $this->all();
        return $cClaveProdServs;
    }

}
