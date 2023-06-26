<?php 
namespace App\Repositories;

use App\Models\Saleorder;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Models\Product;
use App\Models\SaleorderProduct;
use CfdiUtils\Utils\Format;

class SalesOrderRepositoryEloquent extends BaseRepository implements SalesOrderRepository
{
    private $userRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Saleorder::class;
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
        $this->taxRepository = new TaxRepositoryEloquent(app());
    }

    public function getAll()
    {
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('salesOrders.companies', 'salesOrders.salesTeam');
        $salesorders = $org->salesOrders->where('is_delete_list', 0)->where('is_invoice_list', 0);
        return $salesorders;
    }

    public function withAll()
    {
        $this->generateParams();
        $salesorders = $this->userRepository->getOrganization()->salesOrders()->get();
        return $salesorders;
    }

    public function updateOrCreateSalesOrder(array $data, $saleorder_id = null)
    {
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $taxes = $this->taxRepository->getAll();

        $equalValue = Saleorder::where('organization_id', $organization->id)
            ->where('sale_serie', $data['sale_serie'])
            ->where('sale_number', $data['sale_number'])
            ->first();
        if ($equalValue !== null) {
            $saleorderData['sale_number'] = $this->getAll()->last()->sale_number + 1;
        }

        $saleorderData['user_id']= $user->id;
        $saleorderData['organization_id'] = $organization->id;
        $saleorderData['date'] = now()->format(config('settings.date_format'));
        
        $saleorderData['is_delete_list'] = $data['is_delete_list'] ?? 0;
        $saleorderData['is_converted_list'] = $data['is_converted_list'] ?? 0;
        $saleorderData['is_invoice_list'] = $data['is_invoice_list'] ?? 0;
        $saleorderData['status'] = $data['status'] ?? '';
        $saleorderData['company_id'] = $data['company_id'];
        $saleorderData['sale_serie'] = $data['sale_serie'];
        $saleorderData['sale_number'] = $data['sale_number'];
        $saleorderData['payment_method'] = $data['payment_method'];
        $saleorderData['currency'] = $data['currency'];
        $saleorderData['exchange_rate'] = $data['exchange_rate'];
        $saleorderData['payment_term'] = $data['payment_term'];
        $saleorderData['products'] = $data['products'];
        $saleorderData['terms_and_conditions'] = $data['terms_and_conditions'];
        $saleorderData['cfdi_use'] = $data['cfdi_use'];
        $saleorderData['payment_type'] = $data['payment_type'];
        /* TO-DO: 
            Make sure all these variables have the same name everywhere in
            the project, including the database to avoid mapping them:                 
            */
        $saleorderData['iva_included'] = isset($data['iva_toggle']) ? (bool) $data['iva_toggle'] : 0;
        $saleorderData['total'] = $data['subtotal'];
        $saleorderData['discount'] = $data['total_discount'];
        $saleorderData['tax_amount'] = $data['tax_iva_tra'];
        $saleorderData['grand_total'] = $data['subtotal'] - $data['total_discount'];
        $saleorderData['final_price'] = $data['total'];
        
        $saleorder = $this->updateOrCreate(['id' => $saleorder_id], $saleorderData);
        $saleorder->products()->detach();
        $saleorder->taxes()->detach();
        foreach ($data['products'] as $product){        
            // if iva is included then update product's price
            if ($saleorderData['iva_included'] == 1) {
                $product['price'] = Format::number($product['total_amount'] / $product['quantity'], 6);
            }    
            /* if the product exists and the saleorder is not a draft
                 then decrements its stock quantity */
            if (!empty($product['product_id'])) {
                $productObj = Product::find($product['product_id']);
                if ($saleorderData['status'] != trans('sales_order.draft_salesorder')) {
                    $productObj->decrement('quantity_available', $product['quantity']);
                    $productObj->decrement('quantity_on_hand', $product['quantity']);
                }
            } else { 
                /* If the product doesn't exist.
                Save the product like draft. */
                $productObj = new Product();
                $productObj->user_id = $saleorderData['user_id'];
                $productObj->organization_id = $saleorderData['organization_id'];
                $productObj->sku = $product['sku'];
                $productObj->clave_sat = $product['clave_sat'];
                $productObj->description = $product['description'];
                $productObj->product_name = substr($product['description'], 190);
                $productObj->product_type = $product['unidad_sat'];
                $productObj->clave_unidad_sat = $product['clave_unidad_sat'];
                $productObj->unidad_sat = $product['unidad_sat'];
                $productObj->sale_price = $product['price'];
                $productObj->status = 'Borrador';
                $productObj->save();
                // Finish.
                $product['product_id'] = $productObj->id;
            }
            /* TO-DO: 
            Make sure all these variables have the same name everywhere in
            the project, including the database, to avoid mapping them:                 
            */
            // prepare additional data to be inserted into the intermediate table:
            $productAddData['user_id'] = $saleorderData['user_id'];
            $productAddData['organization_id'] = $saleorderData['organization_id'];
            $productAddData['company_id'] = $saleorderData['company_id'];
            $productAddData['sku'] = $product['sku'];
            $productAddData['description'] = $product['description'];
            $productAddData['quantity'] = $product['quantity'];
            $productAddData['price'] = $product['price'];
            $productAddData['discount'] = $product['discount'];
            $productAddData['total'] = $product['total_amount'];
            $productAddData['clave_sat'] = $product['clave_sat'];
            $productAddData['clave_unidad_sat'] = $product['clave_unidad_sat'];
            $productAddData['unidad_sat'] = $product['unidad_sat'];
            
            $saleorder->products()->save($productObj, $productAddData);
            $saleorder->refresh();
            $saleorderProduct = $saleorder->products->last();
                                
            if(!isset($product['taxes']))
		        continue;
            
            foreach ($product['taxes'] as $stringTax) {
                /* takes the tax id from the first value of 
                $stringTax: '803_002_Traslado_0.16_Tasa' */
                $taxId = explode("_", $stringTax)[0];
                $taxObj = $taxes->find($taxId);

                $taxAddData['user_id'] = $saleorderData['user_id'];
                $taxAddData['organization_id'] = $saleorderData['organization_id'];
                $taxAddData['company_id'] = $saleorderData['company_id'];
                $taxAddData['sale_order_id'] = $saleorder['id'];
                $taxAddData['product_id'] = $product['product_id'];
                $taxAddData['tax_id'] = $taxObj->id;
                $taxAddData['sale_order_product_id'] = $saleorderProduct->pivot->id;

                $saleorder->taxes()->save($taxObj, $taxAddData);
            }
        }
        
        return $saleorder;
    }

    public function onlySalesorderInvoiceLists(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('salesOrders.companies','salesOrders.salesTeam');
        $salesorders = $org->salesOrders->where('is_invoice_list', 1);
        return $salesorders;
    }

    public function salesorderDeleteList(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('salesOrders.companies','salesOrders.salesTeam');
        $salesorders = $org->salesOrders->where('is_delete_list', 1);
        return $salesorders;
    }

    public function draftedSalesorder(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('salesOrders.companies','salesOrders.salesTeam');
        $salesorders = $org->salesOrders->where('is_delete_list', 0)
            ->where('status','=',trans('sales_order.draft_salesorder'));
        return $salesorders;
    }

    public function createSalesOrder(array $data){
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $data['user_id']= $user->id;
        $data['organization_id']= $organization->id;
        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total','sku','clave_unidad_sat','unidad_sat','clave_sat','total_amount','discount_amount','taxes')->toArray();
        $salesorders = $this->create($team);
        foreach ($data['product_id'] as $key =>$product){
            $list =[];
            $temp = []; 
            if ($product != "") {
                $aProduct = Product::where('id', $product)->get();
                if ($aProduct[0]['sku'] == $data['sku'][$key]) {
                    $temp['sku'] = null;
                } else {
                    $temp['sku'] = $data['sku'][$key];
                }
                $temp['quantity'] = $data['quantity'][$key];
                $temp['organization_id'] = $data['organization_id'];
                $temp['company_id'] = $data['company_id'];
                $temp['user_id'] = $data['user_id'];
                $temp['discount'] = $data['discount_amount'][$key];
                $temp['total'] = $data['total_amount'][$key];
                $temp['price'] = $data['price'][$key];
                if ($aProduct[0]['clave_unidad_sat'] == $data['clave_unidad_sat'][$key]) {
                    $temp['clave_unidad_sat'] = null;
                } else {
                    $temp['clave_unidad_sat'] = $data['clave_unidad_sat'][$key];
                }
                if ($aProduct[0]['unidad_sat'] == $data['unidad_sat'][$key]) {
                    $temp['unidad_sat'] = null;
                } else {
                    $temp['unidad_sat'] = $data['unidad_sat'][$key];
                }
                if ($aProduct[0]['clave_sat'] == $data['clave_sat'][$key]) {
                    $temp['clave_sat'] = null;
                } else {
                    $temp['clave_sat'] = $data['clave_sat'][$key];
                }
                if ($aProduct[0]['description'] == $data['description'][$key]) {
                    $temp['description'] = null;
                } else {
                    $temp['description'] = $data['description'][$key];
                }
                if (isset($data['status']) && $data['status'] == 'Borrador') { //
                    $aProduct[0]['quantity_available'] =  $aProduct[0]['quantity_available'] - $data['quantity'][$key]; //
                } else { //
                    $aProduct[0]['quantity_available'] =  $aProduct[0]['quantity_available'] - $data['quantity'][$key]; //
                    $aProduct[0]['quantity_on_hand'] =  $aProduct[0]['quantity_on_hand'] - $data['quantity'][$key]; //
                } //
                $aProduct[0]->save(); //
                $list[$data['product_id'][$key]] = $temp;
                $salesorders->products()->attach($list);   
            } else if ($data['description'][$key] != "") { // If the product doesn't exist.
                // Save the product like draft.
                $p = new Product();
                $p->user_id = $data['user_id'];
                $p->organization_id = $data['organization_id'];
                $p->sku = $data['sku'][$key];
                $p->clave_sat = $data['clave_sat'][$key];;
                $p->description = $data['description'][$key];
                $p->product_name = $data['description'][$key];
                $p->product_type = $data['unidad_sat'][$key];
                $p->clave_unidad_sat = $data['clave_unidad_sat'][$key];
                $p->unidad_sat = $data['unidad_sat'][$key];
                $p->sale_price = $data['price'][$key];
                $p->status = 'Borrador';
                $p->save();
                // Finish.
                $temp['sku'] = $data['sku'][$key];
                $temp['quantity'] = $data['quantity'][$key];
                $temp['organization_id'] = $data['organization_id'];
                $temp['company_id'] = $data['company_id'];
                $temp['user_id'] = $data['user_id'];
                $temp['discount'] = $data['discount_amount'][$key];
                $temp['total'] = $data['total_amount'][$key];
                $temp['price'] = $data['price'][$key];
                $temp['clave_unidad_sat'] = $data['clave_unidad_sat'][$key];
                $temp['unidad_sat'] = $data['unidad_sat'][$key];
                $temp['clave_sat'] = $data['clave_sat'][$key];
                $temp['description'] = $data['description'][$key];
                $temp['product_id'] = $p->id;
                $list[$key] = $temp; 
                $salesorders->products()->attach($list);  
            }
        }
        $salesordersProduct = SaleorderProduct::where('saleorder_id', $salesorders['id'])
        ->select('id')
        ->get()
        ->toArray();
        $y = 0;
        foreach ($data['taxes'] as $x => $id) {
            $temp = [];
            $z = 0;
            $string_taxes = $data['taxes'][$x];
            $taxes_per_concept = explode(",", $string_taxes);
            if ($id != "") {
                for ($tax = 0;$tax <= count($taxes_per_concept) - 1; $tax++) {
                    $list = [];
                    $all_taxes[$x] = explode("_", $taxes_per_concept[$tax]);
                    $temp['sale_order_id'] = $salesorders['id'];
                    $temp['product_id'] = ($data['product_id'][$x] != '') ? $data['product_id'][$x] : null;
                    $temp['organization_id'] = $data['organization_id'];
                    $temp['company_id'] = $data['company_id'];
                    $temp['user_id'] = $data['user_id'];
                    $temp['tax_id'] = $all_taxes[$x][0]; // data 256_002_Traslado_0.16_Tasa
                    $temp['sale_order_product_id'] = $salesordersProduct[$y]['id'];
                    $list[$all_taxes[$x][0]] = $temp;
                    $ip = new SaleorderProduct();
                    $ip->saleOrderProductTaxes()->attach($list);
                    $z++;
                }
            }
            $y++;
        }
        return $salesorders;
    }

    public function updateSalesOrder(array $data,$saleorder_id){
        $this->generateParams();
        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total')->toArray();
        $salesorders = $this->update($team,$saleorder_id);
        $list =[];

        foreach ($data['product_id'] as $key =>$product){
            if ($product != "") {
                $temp['quantity'] = $data['quantity'][$key];
                $temp['price'] = $data['price'][$key];
                $list[$data['product_id'][$key]] = $temp;
            }
        }

        $salesorders->salesOrderProducts()->sync($list);
    }

    public function getAllForCustomer($company_id)
    {
        $this->generateParams();
        $salesorders = $this->userRepository->getOrganization()->salesOrders()->where([
            ['is_delete_list','=',0],
            ['is_invoice_list','=',0],
            ['status','!=',trans('sales_order.draft_salesorder')],
            ['company_id','=', $company_id]
        ]);
        return $salesorders;
    }

    public function getMonthYear($monthno, $year)
    {
        $this->generateParams();
        $salesorders = $this->userRepository->getOrganization()->salesOrders()->whereYear('created_at', $year)->whereMonth('created_at', $monthno)->get();
        return $salesorders;
    }
}
