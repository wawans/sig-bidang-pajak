<?php

namespace App\Http\Controllers;

use App\Repositories\SettingRepository;
use App\Support\Str\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class MapProviderController extends Controller
{
    use Str;

    public function __construct(protected SettingRepository $repository) {}

    private function getProviderSetting($name)
    {
        return [
            'enabled' => $this->strtobool($this->repository->getByEnv('map_'.$name.'_enabled', config("setting.map.$name.enabled"))),
            'token' => $this->repository->getByEnv('map_'.$name.'_token', config("setting.map.$name.token")),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $providers = ['google', 'microsoft', 'mapbox'];

        return Inertia::render('map-provider/index', [
            'model' => collect($providers)->mapWithKeys(function ($provider) {
                return [$provider => $this->getProviderSetting($provider)];
            }),
            'form' => [
                'providers' => $providers,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, $provider)
    {
        $input = $request->validate([
            'enabled' => 'required|boolean',
            'token' => 'required_if:enabled,true|string',
        ]);

        foreach ($input as $key => $value) {
            $this->repository->createOrUpdate([
                'key' => strtoupper('map_'.$provider.'_'.$key),
                'name' => "setting.map.$provider.$key",
                'value' => ($key == 'enabled') ? $this->booltostr($value) : $value,
                'env' => false,
            ]);
        }

        Cache::forget('setting.map.providers');

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }
}
