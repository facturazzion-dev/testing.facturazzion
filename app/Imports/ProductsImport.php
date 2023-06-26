<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;

class ProductsImport implements ToModel
{

    public function  __construct($organization_id)
    {
        $this->organization_id =$organization_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Product([
            'sku' => $row[0],
            'clave_sat' => $row[1],
            'description' => $row[2],
            'product_name' => $row[3],
            'product_type' => $row[4],
            'clave_unidad_sat' => $row[5],
            'unidad_sat' => $row[6],
            'sale_price' => $row[7],
            'quantity_available' => $row[8],
            'organization_id' => $this->organization_id
        ]);
    }
}
