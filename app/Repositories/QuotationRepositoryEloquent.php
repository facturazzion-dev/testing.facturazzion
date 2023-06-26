<?php
namespace App\Repositories;


use App\Models\Quotation;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Eloquent\BaseRepository;
use App\Models\Product;
use App\Models\QuotationProduct;
use CfdiUtils\Utils\Format;

class QuotationRepositoryEloquent extends BaseRepository implements QuotationRepository
{
    private $userRepository;
    private $taxRepository;
    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return Quotation::class;
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
        $org = $this->userRepository->getOrganization()->load('quotations.companies','quotations.salesTeam');
        $quotations = $org->quotations->where('is_delete_list',0)->where('is_converted_list',0)->where('is_quotation_invoice_list',0);
        return $quotations;
    }

    public function withAll()
    {
        $this->generateParams();
        $quotations = $this->userRepository->getOrganization()->quotations()->get();
        return $quotations;
    }

    public function updateOrCreateQuotation(array $data, $quotation_id = null){
        $this->generateParams();
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $taxes = $this->taxRepository->getAll();

        $equalValue = Quotation::where('organization_id', $organization->id)
            ->where('quotation_serie', $data['quotation_serie'])
            ->where('quotation_number', $data['quotation_number'])
            ->first();
        if ($equalValue !== null) {
            $quotationData['quotation_number'] = $this->getAll()->last()->quotation_number + 1;
        }

        $quotationData['user_id']= $user->id;
        $quotationData['organization_id'] = $organization->id;
        $quotationData['date'] = now()->format(config('settings.date_format'));
        
        $quotationData['is_delete_list'] = $data['is_delete_list'] ?? 0;
        $quotationData['is_converted_list'] = $data['is_converted_list'] ?? 0;
        $quotationData['is_quotation_invoice_list'] = $data['is_quotation_invoice_list'] ?? 0;
        $quotationData['status'] = $data['status'] ?? '';
        $quotationData['company_id'] = $data['company_id'];
        $quotationData['quotation_serie'] = $data['quotation_serie'];
        $quotationData['quotation_number'] = $data['quotation_number'];
        $quotationData['payment_method'] = $data['payment_method'];
        $quotationData['currency'] = $data['currency'];
        $quotationData['exchange_rate'] = $data['exchange_rate'];
        $quotationData['payment_term'] = $data['payment_term'];
        $quotationData['products'] = $data['products'];
        $quotationData['terms_and_conditions'] = $data['terms_and_conditions'];
        $quotationData['cfdi_use'] = $data['cfdi_use'];
        $quotationData['payment_type'] = $data['payment_type'];
        /* TO-DO: 
            Make sure all these variables have the same name everywhere in
            the project, including the database to avoid mapping them:                 
            */
        $quotationData['iva_included'] = isset($data['iva_toggle']) ? (bool) $data['iva_toggle'] : 0;
        $quotationData['total'] = $data['subtotal'];
        $quotationData['discount'] = $data['total_discount'];
        $quotationData['tax_amount'] = $data['tax_iva_tra'];
        $quotationData['grand_total'] = $data['subtotal'] - $data['total_discount'];
        $quotationData['final_price'] = $data['total'];
        
        $quotation = $this->updateOrCreate(['id' => $quotation_id], $quotationData);
        $quotation->products()->detach();
        $quotation->taxes()->detach();
        foreach ($data['products'] as $product){            
            // if iva is included then update product's price
            if ($quotationData['iva_included'] == 1) {
                $product['price'] = Format::number($product['total_amount'] / $product['quantity'], 6);
            }
            /* if the product exists and the quotation is not a draft
                 then decrements its stock quantity */
            if (!empty($product['product_id'])) {
                $productObj = Product::find($product['product_id']);
                if ($quotationData['status'] != trans('quotation.draft_quotation')) {
                    $productObj->decrement('quantity_available', $product['quantity']);
                    $productObj->decrement('quantity_on_hand', $product['quantity']);
                }
            } else { 
                /* If the product doesn't exist.
                Save the product like draft. */
                $productObj = new Product();
                $productObj->user_id = $quotationData['user_id'];
                $productObj->organization_id = $quotationData['organization_id'];
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
            $productAddData['user_id'] = $quotationData['user_id'];
            $productAddData['organization_id'] = $quotationData['organization_id'];
            $productAddData['company_id'] = $quotationData['company_id'];
            $productAddData['sku'] = $product['sku'];
            $productAddData['description'] = $product['description'];
            $productAddData['quantity'] = $product['quantity'];
            $productAddData['price'] = $product['price'];
            $productAddData['discount'] = $product['discount'];
            $productAddData['total'] = $product['total_amount'];
            $productAddData['clave_sat'] = $product['clave_sat'];
            $productAddData['clave_unidad_sat'] = $product['clave_unidad_sat'];
            $productAddData['unidad_sat'] = $product['unidad_sat'];
            
            $quotation->products()->save($productObj, $productAddData);
            $quotation->refresh();
            $quotationProduct = $quotation->products->last();
                                
            if(!isset($product['taxes']))
		        continue;
            
            foreach ($product['taxes'] as $stringTax) {
                /* takes the tax id from the first value of 
                $stringTax: '803_002_Traslado_0.16_Tasa' */
                $taxId = explode("_", $stringTax)[0];
                $taxObj = $taxes->find($taxId);

                $taxAddData['user_id'] = $quotationData['user_id'];
                $taxAddData['organization_id'] = $quotationData['organization_id'];
                $taxAddData['company_id'] = $quotationData['company_id'];
                $taxAddData['quotation_id'] = $quotation['id'];
                $taxAddData['product_id'] = $product['product_id'];
                $taxAddData['tax_id'] = $taxObj->id;
                $taxAddData['quotation_product_id'] = $quotationProduct->pivot->id;

                $quotation->taxes()->save($taxObj, $taxAddData);
            }
        }
        
        return $quotation;
    }

    public function updateQuotation(array $data,$quotation_id)
    {
        $this->generateParams();
        $team = collect($data)->except('product_list','taxes','product_id','description','quantity','price','sub_total','sku','clave_unidad_sat','unidad_sat','clave_sat','total_amount','discount_amount','taxes','quantity_available')->toArray();
        $quotation = $this->update($team,$quotation_id);
        $list =[];

        foreach ($data['product_id'] as $key =>$product) {
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
                $temp['product_id'] = $product;
                $list[$key] = $temp;   
            } else if ($data['description'][$key] != "") { // If the product doesn't exist.
                // Save the product like draft.
                $p = new Product();
                $p->user_id = $data['user_id'];
                $p->organization_id = $data['organization_id'];
                $p->sku = $data['sku'][$key];
                $p->clave_sat = $data['clave_sat'][$key];;
                $p->description = $data['description'][$key];
                $p->product_name = substr($data['description'][$key], 190);
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
            }
        }
        $quotation->products()->sync($list);
        $quotationProduct = quotationProduct::where('quotation_id', $quotation_id)
        ->select('id')
        ->get();
        $y = 0; // Foreach concept.
        \DB::table('quotations_products_taxes')->where('quotation_id', $quotation_id)->delete();
        foreach ($data['taxes'] as $x => $id) {
            $temp = [];
            $list = [];
            $z = 0; // Foreach tax.
            $string_taxes = $data['taxes'][$x];
            $taxes_per_concept = explode(",", $string_taxes);
            if ($id != "") {
                for ($tax = 0;$tax <= count($taxes_per_concept) - 1; $tax++) {
                    $all_taxes[$x] = explode("_", $taxes_per_concept[$tax]);
                    $temp['quotation_id'] = $quotation['id'];
                    $temp['product_id'] = ($data['product_id'][$x] != '') ? $data['product_id'][$x] : null;
                    $temp['organization_id'] = $data['organization_id'];
                    $temp['company_id'] = $data['company_id'];
                    $temp['user_id'] = $data['user_id'];
                    $temp['tax_id'] = $all_taxes[$x][0]; // Data 256_002_Traslado_0.16_Tasa.
                    $temp['quotation_product_id'] = $quotationProduct[$y]['id'];
                    $list[$all_taxes[$x][0]] = $temp;
                    $z++;
                }
            }
            $ip = new QuotationProduct();
            $ip->taxes()->sync($list);
            $y++;
        }
    }

    public function quotationDeleteList(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('quotations.companies','quotations.salesTeam');
        $quotations = $org->quotations->where('is_delete_list',1);
        return $quotations;
    }

    public function draftedQuotation(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('quotations.companies','quotations.salesTeam');
        $quotations = $org->quotations->where('is_delete_list',0)
            ->where('status','=',trans('quotation.draft_quotation'));
        return $quotations;
    }

    public function quotationSalesOrderList(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('quotations.companies','quotations.salesTeam');
        $quotations = $org->quotations->where('is_converted_list',1);
        return $quotations;
    }

    public function onlyQuotationInvoiceLists(){
        $this->generateParams();
        $org = $this->userRepository->getOrganization()->load('quotations.companies','quotations.salesTeam');
        $quotations = $org->quotations->where('is_quotation_invoice_list',1);
        return $quotations;
    }

    public function getAllForCustomer($company_id)
    {
        $this->generateParams();
        $quotations = $this->userRepository->getOrganization()->quotations()->where([
            ['is_delete_list','=',0],
            ['is_converted_list','=',0],
            ['is_quotation_invoice_list','=',0],
            ['status','!=',trans('quotation.draft_quotation')],
            ['company_id','=', $company_id]
        ]);
        return $quotations;
    }

    public function getMonth($created_at)
    {
        $quotations = $this->model->whereMonth('created_at', $created_at)->get();
        return $quotations;
    }

    public function getQuotationsForCustomerByMonthYear($year, $monthno,$company_id)
    {
        $quotations = $this->model->whereYear('created_at', $year)->whereMonth('created_at', $monthno)->where([
            ['is_delete_list','=',0],
            ['is_converted_list','=',0],
            ['is_quotation_invoice_list','=',0],
            ['status','!=',trans('quotation.draft_quotation')],
            ['company_id','=', $company_id]
        ])->get();
        return $quotations;
    }
    public function getMonthYear($monthno, $year)
    {
        $this->generateParams();
        $quotations = $this->userRepository->getOrganization()->quotations()->whereYear('created_at', $year)->whereMonth('created_at', $monthno)->get();
        return $quotations;
    }
}
