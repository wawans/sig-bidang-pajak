<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Layer;
use App\Support\Response\ApiResponse;
use Illuminate\Http\Request;

class GeoJsonApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function feature(Request $request, Feature $feature)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function layer(Request $request, Layer $layer)
    {
        $features = Feature::where('layer_id', $layer->id)
            ->get();

        $data = [
            'type' => 'FeatureCollection',
            'features' => $features,
            'totalFeatures' => $features->count(),
            'numberMatched' => $features->count(),
            'numberReturned' => $features->count(),
            'timeStamp' => now()->toISOString(),
            'crs' => [
                'type' => 'name',
                'properties' => [
                    'name' => 'urn:ogc:def:crs:EPSG::3857',
                ],
            ],
        ];

        return ApiResponse::make($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Layer $layer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Layer $layer, Feature $feature)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Layer $layer, Feature $feature)
    {
        //
    }
}
