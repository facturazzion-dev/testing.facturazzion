<?php

namespace App\Repositories;

use App\Models\City;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;

class CityRepositoryEloquent extends BaseRepository implements CityRepository
{
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return City::class;
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

    public function getByState($state_id)
    {
        $this->generateParams();
        $user = $this->userRepository->getUser();

        return City::where(function ($query) use ($user) {
            return $query->whereNull('user_id')
                ->orWhere('user_id', '=', $user->id);
        })->where('state_id', '=', $state_id)
            ->orderBy('name', 'asc')->pluck('name', 'id')->prepend(trans('lead.select_city'), '');
            
    }
}
