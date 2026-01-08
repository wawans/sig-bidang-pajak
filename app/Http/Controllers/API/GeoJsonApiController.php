<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\Layer;
use App\Support\Response\ApiResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

        return ApiResponse::make($this->toFeaturesJson($features));
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

    public function stores(Request $request, Layer $layer)
    {
        $inputs = $request->validate([
            'inserts.*.geometry' => Rule::forEach(function ($value, string $attribute) use ($request) {
                return [
                    Rule::requiredIf(function () use ($request) {
                        return ! blank($request->input('inserts'));
                    }),
                    'array',
                ];
            }),
            'inserts.*.properties' => Rule::forEach(function ($value, string $attribute) {
                return [
                    'nullable',
                    'array',
                ];
            }),
            'updates.*.id' => Rule::forEach(function ($value, string $attribute) use ($request) {
                return [
                    Rule::requiredIf(function () use ($request) {
                        return ! blank($request->input('updates'));
                    }),
                    Rule::exists(Feature::class, 'id'),
                ];
            }),
            'updates.*.geometry' => Rule::forEach(function ($value, string $attribute) use ($request) {
                return [
                    Rule::requiredIf(function () use ($request) {
                        return ! blank($request->input('updates'));
                    }),
                    'array',
                ];
            }),
            'updates.*.properties' => Rule::forEach(function ($value, string $attribute) use ($request) {
                return [
                    Rule::requiredIf(function () use ($request) {
                        return ! blank($request->input('updates'));
                    }),
                    'array',
                ];
            }),
            'deletes.*.id' => Rule::forEach(function ($value, string $attribute) use ($request) {
                return [
                    Rule::requiredIf(function () use ($request) {
                        return ! blank($request->input('deletes'));
                    }),
                    Rule::exists(Feature::class, 'id'),
                ];
            }),
        ]);

        DB::beginTransaction();
        try {
            $inserts = collect($inputs['inserts'] ?? []);
            $updates = collect($inputs['updates'] ?? []);
            $deletes = collect($inputs['deletes'] ?? []);
            $ids = [];

            $deletes->each(function ($delete) {
                Feature::destroy($delete['id']);
            });
            $updates->each(function ($update) use (&$ids) {
                $feature = Feature::whereId($update['id']);
                $properties = array_merge($feature->properties, $update['properties']);
                $feature->fill([
                    'geometry' => $update['geometry'],
                    'properties' => $properties,
                ])->save();

                $ids[] = $feature->id;
            });
            $inserts->each(function ($insert) use ($layer, &$ids) {
                $feature = Feature::create(array_merge($insert, ['layer_id' => $layer->id]));
                $ids[] = $feature->id;
            });

            DB::commit();

            $features = Feature::whereIn('id', $ids)->get();

            return ApiResponse::data(['features' => $this->toFeaturesJson($features)], 'Entri berhasil disimpan.');
        } catch (\Exception $exception) {
            DB::rollBack();

            return throw $exception;
        }
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

    /**
     * @param  Feature[]|Collection  $features
     * @return array
     */
    protected function toFeaturesJson($features)
    {
        return [
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
    }
}
