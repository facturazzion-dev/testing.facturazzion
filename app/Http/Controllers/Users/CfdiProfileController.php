<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\CfdiProfileRequest;
use App\Repositories\OrganizationSettingsRepository;
use App\Repositories\TaxRepository;
use App\Repositories\CfdiProfileRepository;
use App\Repositories\SettingsRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\Request;
use DataTables;

class CfdiProfileController extends Controller
{
    /**
     * @var CfdiProfileRepository
     */
    private $cfdiProfileRepository;
    /**
     * @var TaxRepository
     */
    private $taxRepository;

    private $settingsRepository;

    private $organizationSettingsRepository;

    private $userRepository;

    public function __construct(CfdiProfileRepository $cfdiProfileRepository,
                                TaxRepository $taxRepository,
                                SettingsRepository $settingsRepository,
                                OrganizationSettingsRepository $organizationSettingsRepository,
                                UserRepository $userRepository)
    {
        parent::__construct();
        $this->cfdiProfileRepository = $cfdiProfileRepository;
        $this->taxRepository = $taxRepository;
        $this->settingsRepository = $settingsRepository;
        $this->organizationSettingsRepository = $organizationSettingsRepository;
        $this->userRepository = $userRepository;

        view()->share('type', 'cfdi_profile');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->generateParams();
        $title = trans('cfdi_profile.cfdi_profiles');

        return view('user.cfdi_profile.index', compact('title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        $title = trans('cfdi_profile.new');

        $this->generateParams();

        return view('user.cfdi_profile.create', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CfdiProfileRequest|Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CfdiProfileRequest $request)
    {
        $this->cfdiProfileRepository->createCfdiProfile($request->all());

        return redirect('cfdi_profile');
    }

    public function edit($cfdi_profile)
    {
        $cfdi_profile = $this->cfdiProfileRepository->find($cfdi_profile);
        $title = trans('cfdi_profile.edit');

        $this->generateParams();

        return view('user.cfdi_profile.edit', compact('title', 'cfdi_profile'));
    }

    public function update(CfdiProfileRequest $request, $cfdi_profile_id)
    {
        $this->cfdiProfileRepository->updateCfdiProfile($request->all(),$cfdi_profile_id);
        return redirect('cfdi_profile');
    }

    public function delete($cfdi_profile)
    {
        $cfdi_profile = $this->cfdiProfileRepository->find($cfdi_profile);
        $title = trans('cfdi_profile.delete');

        return view('user.cfdi_profile.delete', compact('title', 'cfdi_profile'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($cfdi_profile)
    {
        $this->cfdiProfileRepository->deleteCfdiProfile($cfdi_profile);
        return redirect('cfdi_profile');
    }

    /**
     * @return mixed
     */
    public function data()
    {
        $cfdi_profiles = $this->cfdiProfileRepository->getAll()
            ->map(function ($cfdi_profiles) {
                return [
                    'id' => $cfdi_profiles->id,
                    'name' => $cfdi_profiles->name,
                    'cfdi_name' => $cfdi_profiles->cfdi_name,
                    'cfdi_type' => $cfdi_profiles->cfdi_type,
                ];
            });

        return DataTables::of($cfdi_profiles)
            ->addColumn('actions', '<a href="{{ url(\'cfdi_profile/\' . $id . \'/edit\' ) }}" title="{{ trans(\'table.edit\') }}">
                                            <i class="fa fa-fw fa-pencil text-warning "></i>  </a>
                                     <a href="{{ url(\'cfdi_profile/\' . $id . \'/delete\' ) }}" title="{{ trans(\'table.delete\') }}">
                                            <i class="fa fa-fw fa-trash text-danger"></i></a>')
            ->removeColumn('id')
            ->rawColumns(['actions'])
            ->make();
    }

    private function generateParams()
    {
        $taxes = $this->taxRepository->orderBy('id', 'desc')->getAll()
            ->map(function ($tax) {
                return [
                    'id' => $tax->id,
                    'name' => $tax->name . '('.($tax->percentage * 100).'% - '.$tax->tax_type.')',                    
                ];
            })
            ->pluck('name','id');
        view()->share('taxes', $taxes);
        
    }
}
