<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaxRequest;
use App\Repositories\OrganizationRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use DataTables;
use Illuminate\Http\Request;
use Storage;

class LocalTaxController extends Controller
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

        view()->share('type', 'local_tax');
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

        return view('user.local_tax.create', compact('title'));
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
        $request->merge(['user_id' => $user->id, 'organization_id' => $organization->id, 'is_local' => true]);

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

        return view('user.local_tax.edit', compact('title', 'tax'));
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

        return view('user.local_tax.show', compact('title', 'tax', 'action'));
    }

    public function delete($tax)
    {
        $this->user = $this->getUser();
        if ((!$this->user->hasAccess(['products.delete'])) && 'staff' == $this->user->orgRole) {
            return redirect('dashboard');
        }
        $tax = $this->taxRepository->find($tax);
        $title = trans('tax.delete');

        return view('user.local_tax.delete', compact('title', 'tax'));
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

    public function data()
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
                    'tax_type' => $tax->tax_type,
                    'percentage' => $tax->percentage,
                    'factor_type' => $tax->factor_type,
                    'orgRole' => $orgRole,

                ];
            });

        return DataTables::of($taxes)
            ->addColumn('actions', '@if(Sentinel::getUser()->hasAccess([\'taxes.write\']) || $orgRole=="admin")
                                        <a class="btn btn-sm btn-outline-primary" href="{{ url(\'local_tax/\' . $id . \'/edit\' ) }}"  title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil"></i> </a>
                                     @endif
                                     <a class="btn btn-sm btn-outline-primary" href="{{ url(\'local_tax/\' . $id . \'/show\' ) }}" title="{{ trans(\'table.details\') }}">
                                            <i class="fa fa-fw fa-eye"></i></a>
                                     @if((Sentinel::getUser()->hasAccess([\'taxes.delete\']) || $orgRole=="admin"))
                                        <a class="btn btn-sm btn-outline-danger" href="{{ url(\'local_tax/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash"></i> </a>
                                     @endif')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
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
