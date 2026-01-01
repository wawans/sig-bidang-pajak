<?php

namespace App\Http\Controllers;

use App\Models\Layer;
use App\Models\LayerGroup;
use App\Models\Style;
use App\Models\User;
use App\Repositories\LayerGroupRepository;
use App\Repositories\LayerRepository;
use App\Repositories\StyleRepository;
use App\Support\Response\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('dashboard/index', [
            'canMapEditor' => request()->user()->can('MAP-EDITOR-INDEX'),
            'canMapViewer' => request()->user()->can('MAP-VIEWER-INDEX'),
        ]);
    }

    public function stats()
    {
        $data = Cache::remember('dashboard.stats', now()->addMinutes(3), function () {
            return [
                'layer' => Layer::count(),
                'group' => LayerGroup::count(),
                'style' => Style::count(),
                'user' => User::count(),
            ];
        });

        return ApiResponse::data($data);
    }

    public function layers(LayerRepository $repository)
    {
        return ApiResponse::make($repository->table(request()));
    }

    public function groups(LayerGroupRepository $repository)
    {
        return ApiResponse::make($repository->table(request()));
    }

    public function styles(StyleRepository $repository)
    {
        return ApiResponse::make($repository->table(request()));
    }
}
