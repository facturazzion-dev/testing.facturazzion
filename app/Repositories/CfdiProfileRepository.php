<?php namespace
App\Repositories;
use Prettus\Repository\Contracts\RepositoryInterface;

interface CfdiProfileRepository extends RepositoryInterface
{
    public function getAll();

    public function createCfdiProfile(array $data);

    public function deleteCfdiProfile($deleteCfdiProfile);

    public function updateCfdiProfile(array $data,$cfdi_profile_id);


}