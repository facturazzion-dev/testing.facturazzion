<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;


interface CClaveUnidadRepository extends RepositoryInterface
{
	public function getAll();
}