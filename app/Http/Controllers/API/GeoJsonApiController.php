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
        return ApiResponse::make($this->toFeatureJson($feature));
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
        $inputs = $request->validate([
            'geometry' => 'required|array',
            'properties' => 'required|array',
        ]);

        $feature = $layer->features()->create($inputs);

        return ApiResponse::data([
            'feature' => $this->toFeatureJson($feature),
        ], 'Entri berhasil disimpan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Layer $layer, Feature $feature)
    {
        $inputs = $request->validate([
            'geometry' => 'required|array',
            'properties' => 'required|array',
        ]);

        $properties = array_merge($feature->properties, $inputs['properties']);

        $feature->fill([
            'geometry' => $inputs['geometry'],
            'properties' => $properties,
        ])->save();

        return ApiResponse::data([
            'feature' => $this->toFeatureJson($feature),
        ], 'Ubah Entri berhasil disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Layer $layer, Feature $feature)
    {
        $feature->delete();

        return ApiResponse::data([
            'feature' => $this->toFeatureJson(),
        ], 'Entri berhasil dihapus.');
    }

    protected function toFeatureJson(?Feature $feature = null)
    {
        $features = is_null($feature) ? [] : [$feature];

        return [
            'type' => 'FeatureCollection',
            'features' => $features,
            'totalFeatures' => count($features),
            'numberMatched' => count($features),
            'numberReturned' => count($features),
            'timeStamp' => now()->toISOString(),
            'crs' => [
                'type' => 'name',
                'properties' => [
                    'name' => 'urn:ogc:def:crs:EPSG::3857',
                ],
            ],
        ];
    }
}
