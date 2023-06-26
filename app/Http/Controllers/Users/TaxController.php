<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxRequest;
use App\Models\Tax;
use App\Repositories\OrganizationRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use DataTables;
use Illuminate\Http\Request;
use Storage;

class TaxController extends Controller
{
    /**
     * @var TaxRepository
     */
    private $taxRepository;

    private $userRepository;

    private $organizationRepository;

    protected $user;

    /**
     * @param TaxRepository  $taxRepository
     * @param OptionRepository   $optionRepository
     */
    public function __construct(TaxRepository $taxRepository,

        UserRepository $userRepository,
        OrganizationRepository $organizationRepository) {
        parent::__construct();

        $this->taxRepository = $taxRepository;

        $this->userRepository = $userRepository;
        $this->organizationRepository = $organizationRepository;

        view()->share('type', 'tax');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['taxes.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $title = 'Impuestos Federales';

        return view('user.tax.index', compact('title'));
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
        $title = trans('tax.new');

        return view('user.tax.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TaxRequest|Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(TaxRequest $request)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $user = $this->userRepository->getUser();
        $organization = $this->userRepository->getOrganization();
        $request->merge(['user_id' => $user->id, 'organization_id' => $organization->id]);

        $this->taxRepository->create($request->except('_token'));

        return redirect('tax');
    }

    public function edit($tax)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $tax = $this->taxRepository->find($tax);
        $title = trans('tax.edit');

        return view('user.tax.edit', compact('title', 'tax'));
    }

    public function update(TaxRequest $request, $tax)
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $tax = $this->taxRepository->find($tax);

        $tax->update($request->except('_token'));

        return redirect('tax');
    }

    public function show($tax)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['taxes.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $tax = $this->taxRepository->find($tax);
        $action = trans('action.show');
        $title = trans('tax.details');

        return view('user.tax.show', compact('title', 'tax', 'action'));
    }

    public function delete($tax)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['products.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $tax = $this->taxRepository->find($tax);
        $title = trans('tax.delete');

        return view('user.tax.delete', compact('title', 'tax'));
    }

    public function destroy($tax)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['taxes.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $tax = $this->taxRepository->find($tax);
        $tax->delete();

        return redirect('tax');
    }

    public function toggleFavorite(Tax $tax)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['taxes.write'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }

        $tax->update(['favorite' => !$tax->favorite]);

        return response()->json([
            'state' => 'updated',
            'tax' => $tax->only(['id', 'name']),
        ], 200);
    }

    public function data()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $orgRole = $this->getUser()->orgRole;
        $taxes = $this->taxRepository
            ->getAllNonLocal()
            ->map(function ($tax) use ($orgRole) {
                return [
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'tax' => $tax->tax,
                    'tax_type' => $tax->tax_type,
                    'percentage' => $tax->percentage,
                    'factor_type' => $tax->factor_type,
                    'orgRole' => $orgRole,
                    'favorite' => $tax->favorite,

                ];
            });

        return DataTables::of($taxes)->make();
    }

    public function localData()
    {
        $this->generateParams();
        if ((!$this->user->hasAccess(['products.read'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $orgRole = $this->getUser()->orgRole;
        $taxes = $this->taxRepository
            ->getAllLocal()
            ->map(function ($tax) use ($orgRole) {
                return [
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'tax' => $tax->tax,
                    'tax_type' => $tax->tax_type,
                    'percentage' => $tax->percentage,
                    'factor_type' => $tax->factor_type,
                    'orgRole' => $orgRole,
                    'favorite' => $tax->favorite,

                ];
            });

        return DataTables::of($taxes)->make();
    }

    private function generateParams()
    {
        $this->user = $this->getUser();
        // $statuses = $this->optionRepository->getAll()
        //     ->where('category', 'product_status')->pluck('title', 'value')->prepend(trans('product.status'), '');

        // $product_types = $this->optionRepository->getAll()
        //     ->where('category', 'product_type')->pluck('title', 'value')->prepend(trans('product.product_type'), '');

        // $categories = $this->categoryRepository->orderBy('id', 'asc')->getAll()->pluck('name', 'id')->prepend(trans('product.category_id'), '');

        // view()->share('statuses', $statuses);
        // view()->share('product_types', $product_types);
        // view()->share('categories', $categories);
    }

    // public function downloadExcelTemplate()
    // {
    //     ob_end_clean();
    //     $path = base_path('resources/excel-templates/taxes.xlsx');

    //     if (file_exists($path)) {
    //         return response()->download($path);
    //     }

    //     return 'File not found!';
    // }

}
