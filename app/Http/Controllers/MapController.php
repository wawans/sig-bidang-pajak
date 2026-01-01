<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class MapController extends Controller
{
    use Concerns\Map;

    public function index()
    {
        return Inertia::render('map/viewer', [
            'mode' => 'viewer',
        ]);
    }

    public function edit()
    {
        return Inertia::render('map/editor', [
            'mode' => 'editor',
        ]);
    }
}
