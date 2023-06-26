<?php

namespace App\Http\Controllers\Users;

use App\Helpers\ExcelfileValidator;
use App\Helpers\Thumbnail;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Imports\ProductsImport;
use App\Models\Product;
use App\Repositories\CategoryRepository;
use App\Repositories\OptionRepository;
use App\Repositories\OrganizationRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use DataTables;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var CategoryRepository
     */
    private $categoryRepository;
    /**
     * @var OptionRepository
     */
    private $optionRepository;

    private $userRepository;

    private $organizationRepository;

    protected $user;

    /**
     * @param ProductRepository  $productRepository
     * @param CategoryRepository $categoryRepository
     * @param OptionRepository   $optionRepository
     */
    public function __construct(ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        OptionRepository $optionRepository,
        UserRepository $userRepository,
        OrganizationRepository $organizationRepository) {
        parent::__construct();

        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->optionRepository = $optionRepository;
        $this->userRepository = $userRepository;
        $this->organizationRepository = $organizationRepository;

        view()->share('type', 'product');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = trans('product.products');

        $statuses = $this->optionRepository->findByField('category', 'product_status')
            ->map(function ($title) {
                return [
                    'title' => $title->title,
                    'value' => $title->value,
                ];
            })->toArray();
        $colors = ['#3295ff', '#2daf57', '#fc4141', '#fcb410', '#17a2b8', '#3295ff', '#2daf57', '#fc4141', '#fcb410', '#17a2b8'];
        foreach ($statuses as $key => $status) {
            $statuses[$key]['color'] = isset($colors[$key]) ? $colors[$key] : "";
            $statuses[$key]['products'] = $this->productRepository->getAll()->where('status', $status['value'])->count();
        }

        $graphics = [];

        for ($i = 11; $i >= 0; --$i) {
            $monthno = now()->subMonth($i)->format('m');
            $month = now()->subMonth($i)->format('M');
            $year = now()->subMonth($i)->format('Y');
            $graphics[] = [
                'month' => $month,
                'year' => $year,
                'products' => $this->productRepository->getMonthYear($monthno, $year)->count(),
            ];
        }

        return view('user.product.index', compact('title', 'statuses', 'graphics'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = 'Crear Producto';

        return view('user.product.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductRequest|Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $request->merge(['user_id' => $user->id, 'organization_id' => $organization->id]);

        if ($request->hasFile('product_image_file')) {
            $file = $request->file('product_image_file');
            $file = $this->productRepository->uploadProductImage($file);

            $request->merge([
                'product_image' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateProductThumbnail($file);
        }
        $this->productRepository->create($request->except('product_image_file'));

        return redirect('product');
    }

    public function edit($product)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $product = $this->productRepository->find($product);
        $title = trans('product.edit');

        return view('user.product.edit', compact('title', 'product'));
    }

    public function update(ProductRequest $request, $product)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $product = $this->productRepository->find($product);
        $reserved = $product['quantity_on_hand'] - $product['quantity_available']; //
        $request['quantity_on_hand'] = $reserved + $request['quantity_available']; //
        if ($request->hasFile('product_image_file')) {
            $file = $request->file('product_image_file');
            $file = $this->productRepository->uploadProductImage($file);

            $request->merge([
                'product_image' => $file->getFileInfo()->getFilename(),
            ]);

            $this->generateProductThumbnail($file);
        }

        $product->update($request->except('product_image_file'));

        return redirect('product');
    }

    public function show($product)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $product = $this->productRepository->find($product);
        $action = trans('action.show');
        $title = trans('product.details');

        return view('user.product.show', compact('title', 'product', 'action'));
    }

    public function delete($product)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['products.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $product = $this->productRepository->find($product);
        $title = trans('product.delete');

        return view('user.product.delete', compact('title', 'product'));
    }

    public function destroy($product)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['products.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $product = $this->productRepository->find($product);
        $product->delete();

        return redirect('product');
    }

    public function toggleFavorite(Product $product)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['products.favorite'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        
        $favoriteProducts = $this->productRepository->findWhere(['favorite' => 1]);
        
        foreach ($favoriteProducts as $favProduct) {
            $favProduct->update(['favorite' => 0]);
        }

        $product->update(['favorite' => !$product->favorite]);

        return response()->json([
            'state' => 'updated',
            'product' => $product->only(['id', 'product_name']),
        ], 200);
    }

    public function data()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $products = $this->productRepository
            ->getAll()->Where('status', '!=', 'Borrador')
            ->sortByDesc('id')
            ->map(function ($product) {
                return [
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'clave_sat' => $product->clave_sat,
                    'description' => $product->description,
                    'product_name' => $product->product_name,
                    'quantity_available' => $product->quantity_available,
                    'clave_unidad_sat' => $product->clave_unidad_sat,
                    'unidad_sat' => $product->unidad_sat,
                    'favorite' => $product->favorite,
                ];
            });

        return DataTables::of($products)->make();            
    }

    public function clave_sat_data(Request $request)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        $path = base_path('resources/assets/sat_catalog/c_ClaveProdServ.json');
        $array = json_decode(file_get_contents($path), true);

        if (isset($request->searchable_name)) {

            $collection = collect($array)->filter(function ($item) use ($request) {
                return (false !== stristr($item['descripcion'], $request->searchable_name) || (false !== stristr($item['id'], $request->searchable_name)));
            });
        } else {
            $collection = collect($array);
        }

        return DataTables::of($collection)->make();
    }

    public function products_data(Request $request)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        $products = $this->productRepository
            ->orderBy('id', 'desc')
            ->getAll()
            ->where('status', '!=', 'Borrador');
        $array = json_decode($products);

        if (isset($request->searchable_product_name)) {

            $collection = collect($array)->filter(function ($item) use ($request) {
                return (false !== stristr($item->product_name, $request->searchable_product_name)
                    || (false !== stristr($item->id, $request->searchable_product_name))
                    || (false !== stristr($item->description, $request->searchable_product_name))
                    || (false !== stristr($item->sku, $request->searchable_product_name)));
            });
        } else {
            $collection = collect($array);
        }
        return DataTables::of($collection)->make();

    }

    public function products_autocomplete(Request $request)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        $data = $this->productRepository->getAll()->where('status', '!=', 'Borrador')->filter(function ($item) use ($request) {
            return (false !== stristr($item->product_name, $request->searchable_product_name)
                || (false !== stristr($item->id, $request->searchable_product_name))
                || (false !== stristr($item->description, $request->searchable_product_name))
                || (false !== stristr($item->sku, $request->searchable_product_name)));
        })->take(5);
        
        $output = '<ul class="dropdown-menu" style="display:block;">';
        foreach($data as $row)
        {
            $output .= '
            <li><a href="#">'.$row->product_name.'</a></li>
            ';
        }
        $output .= '</ul>';
        return $output;

    }

    public function clave_unidad_sat_data(Request $request)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        $path = base_path('resources/assets/sat_catalog/c_ClaveUnidad.json');
        $array = json_decode(file_get_contents($path), true);

        if (isset($request->searchable_unidad_sat)) {

            $collection = collect($array)->filter(function ($item) use ($request) {
                return (false !== stristr($item['nombre'], $request->searchable_unidad_sat) || (false !== stristr($item['id'], $request->searchable_unidad_sat)));
            });
        } else {
            $collection = collect($array);
        }

        return DataTables::of($collection)->make();

    }

    /**
     * @param $file
     */
    private function generateProductThumbnail($file)
    {
        $sourcePath = $file->getPath() . '/' . $file->getFilename();
        $thumbPath = $file->getPath() . '/thumb_' . $file->getFilename();
        Thumbnail::generate_image_thumbnail($sourcePath, $thumbPath);
    }

    private function generateParams()
    {
        $this->user = $this->getUser();
        $statuses = $this->optionRepository->getAll()
            ->where('category', 'product_status')->pluck('title', 'value')->prepend(trans('product.status'), '');

        $product_types = $this->optionRepository->getAll()
            ->where('category', 'product_type')->pluck('title', 'value')->prepend(trans('product.product_type'), '');

        $categories = $this->categoryRepository->orderBy('id', 'asc')->getAll()->pluck('name', 'id')->prepend(trans('product.category_id'), '');

        view()->share('statuses', $statuses);
        view()->share('product_types', $product_types);
        view()->share('categories', $categories);
    }

    public function getImport()
    {
        $title = trans('product.import');

        return view('user.product.import', compact('title'));
    }

    public function postImport(Request $request)
    {
        // $this->validate($request, [
        //     'file' => 'required|mimes:xlsx,xls,csv|max:5000',
        // ]);

        if (!ExcelfileValidator::validate($request)) {
            return response('invalid File or File format', 500);
        }

        $organization = $this->userRepository->getOrganization();

        Excel::import(new ProductsImport($organization->id), $request->file('file'));

        return redirect('/product')->with('success', 'All good!');
    }

    public function postAjaxStore(ProductRequest $request)
    {
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $request->merge(['user_id' => $user->id, 'organization_id' => $organization->id]);
        $this->productRepository->create($request->except('created', 'errors', 'selected', 'variants'));

        return response()->json([], 200);
    }

    public function downloadExcelTemplate()
    {
        ob_end_clean();
        $path = base_path('resources/excel-templates/FZZProductos.xlsx');

        if (file_exists($path)) {
            return response()->download($path);
        }

        return 'File not found!';
    }

    private function getProductVariants($variants = [])
    {
        if (isset($variants)) {
            $variants = array_map(
                function ($v) {
                    return explode(':', $v);
                },
                explode(',', $variants)
            );
        }

        return $variants;
    }
}
