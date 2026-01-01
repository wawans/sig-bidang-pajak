<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $impersonated = session()->has('impersonated_by') ? ['impersonatedBy' => session('impersonated_by')] : [];
        $recaptcha = config('services.recaptcha.enabled', false) && ! blank(config('services.recaptcha.site_key')) ? ['recaptchaSiteKey' => config('services.recaptcha.site_key')] : [];

        return [
            ...parent::share($request),
            ...$impersonated,
            ...$recaptcha,
            'name' => config('app.name'),
            'auth' => fn (): array => [
                'user' => \Auth::check() ? $request->user()->toAuth() : null,
            ],
            'flash' => fn (): array => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
            'ziggy' => fn (): array => [
                'location' => $request->url(),
            ],
        ];
    }
}
