<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Layer;
use App\Models\LayerGroup;
use App\Models\LayerGroupMember;
use App\Models\Style;
use App\Support\Response\ApiResponse;
use Illuminate\Support\Facades\Cache;

trait Map
{
    protected function styles()
    {
        return Cache::remember('style.all', now()->addMinutes(3), function () {
            return Style::orderBy('name')->get();
        });
    }

    protected function layers()
    {
        return Cache::remember('layer.all', now()->addMinutes(3), function () {
            return Layer::orderBy('name')->get();
        });
    }

    public function groupFn(?LayerGroup $group)
    {
        $value = $group;
        $child = LayerGroup::with('children.layer')->where('parent_layer_group_id', $value->id)->get();

        return ($child->count() > 0) ? $this->subFn($value, $child) : $this->itemFn($value);
    }

    public function subFn(?LayerGroup $group, $sub)
    {
        $value = collect($group);
        $child = $sub->map([$this, 'groupFn']);
        $value['children'] = $value->has('children') ? collect($value->get('children'))?->merge($child) : $child;

        return $value;
    }

    public function itemFn(?LayerGroup $group)
    {
        $value = collect($group);
        $child = $group->children->transform(fn (?LayerGroupMember $m) => $m->layer);

        return $value->merge(['children' => $child]);
    }

    public function transformFn(?LayerGroup $group)
    {
        $value = collect($group);
        $child = $group->children->transform(fn (?LayerGroupMember $m) => $m->layer);
        $group = $value->has('groups') ? $value->pull('groups')->all() : [];

        return $value->merge([
            'children' => collect(array_merge($child->all(), $group))->sortBy('name'),
        ]);
    }

    protected function tree()
    {
        return Cache::remember('layer.tree', now()->addMinutes(3), function () {
            // layers without group
            $sole = Layer::doesntHave('group')->orderBy('name')->get();
            // layers (group and members)
            $group = LayerGroup::with('children.layer')->whereNull('parent_layer_group_id')->orderBy('name')->get()
                ->map([$this, 'groupFn']);

            return collect($group)->merge($sole);
        });
    }

    protected function providers()
    {
        return Cache::remember('setting.map.providers', now()->addMinutes(3), function () {
            return [
                'google' => [
                    'enabled' => config('setting.map.google.enabled'),
                    'token' => config('setting.map.google.token'),
                ],
                'microsoft' => [
                    'enabled' => config('setting.map.microsoft.enabled'),
                    'token' => config('setting.map.microsoft.token'),
                ],
                'mapbox' => [
                    'enabled' => config('setting.map.mapbox.enabled'),
                    'token' => config('setting.map.mapbox.token'),
                ],
            ];
        });
    }

    protected function data()
    {
        return [
            'styles' => $this->styles(),
            'layers' => $this->layers(),
            'tree' => $this->tree(),
            'providers' => $this->providers(),
        ];
    }

    public function init()
    {
        return ApiResponse::data($this->data());
    }
}
