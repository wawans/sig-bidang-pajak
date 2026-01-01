<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthenticatedSocialiteController extends Controller
{
    public function create($provider)
    {
        return match ($provider) {
            'google' => Socialite::driver($provider)->redirect(),
            default => fn () => abort(404),
        };
    }

    public function store(UserRepository $repository, $provider)
    {
        try {
            $user = match ($provider) {
                'google' => Socialite::driver($provider)->user(),
                default => fn () => abort(404),
            };
        } catch (\Exception $e) {
            if (app()->environment(['local', 'testing'])) {
                throw $e;
            }

            return to_route('login');
        }

        $local = $repository->firstOrRegister($provider, $user);

        Auth::login($local, true);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
