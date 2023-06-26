<?php

namespace App\Repositories;
use Prettus\Repository\Contracts\RepositoryInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface TaxRepository extends RepositoryInterface
{
    public function getAll();
    
    public function getAllLocal();

    public function getAllNonLocal();

    
}