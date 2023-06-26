<?php

namespace App\Repositories;

use Prettus\Repository\Contracts\RepositoryInterface;


interface CClaveProdServRepository extends RepositoryInterface
{
	public function getAll();
}