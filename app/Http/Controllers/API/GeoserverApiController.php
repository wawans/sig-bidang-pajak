<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Layer;
use App\Services\Geoserver\Geoserver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GeoserverApiController extends Controller
{
    public function __construct(protected Geoserver $service) {}

    public function index(Request $request)
    {
        $method = $request->get('request', 'GetFeature');

        return match ($method) {
            'DescribeFeatureType' => $this->service->getDescribeFeatureType($request->query()),
            default => $this->service->getFeature($request->query()),
        };
    }

    public function store(Request $request)
    {
        return $this->service->transaction($request->input('data'), $request->query(), true);
    }

    public function describe(Request $request, $layer)
    {
        return Cache::remember('describe.'.Str::slug($layer).http_build_query($request->query()), now()->addMinutes(3), function () use ($request, $layer) {
            return $this->service->getDescribeFeatureType(array_merge($request->query(), [
                'typeNames' => $layer,
            ]));
        });
    }

    public function feature(Request $request, string $layer)
    {
        return $this->service->getFeature(array_merge($request->query(), [
            'typeNames' => $layer,
        ]));
    }

    public function featureId(Request $request, string $layer, string $id)
    {
        return $this->service->getFeatureId($id, array_merge($request->query(), [
            'typeNames' => $layer,
        ]));
    }

    public function featureNop(Request $request, string $layer, string $id)
    {
        return $this->service->getFeatureNop($id, array_merge($request->query(), [
            'typeNames' => $layer,
        ]));
    }

    public function layer(Request $request, Layer $layer)
    {
        return Cache::remember('layer.'.$layer->uuid.$layer->updated_at->unix().http_build_query($request->query()), now()->addMinutes(3), function () use ($request, $layer) {
            return $this->feature($request, $layer->namespace);
        });
    }
}
