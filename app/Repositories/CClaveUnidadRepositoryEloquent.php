<?php

namespace App\Repositories;

use App\Models\CClaveUnidad;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CClaveUnidadRepositoryEloquent extends BaseRepository implements CClaveUnidadRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return CClaveUnidad::class;
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
        $cClaveUnits = $this->all();
        return $cClaveUnits;
    }

}
