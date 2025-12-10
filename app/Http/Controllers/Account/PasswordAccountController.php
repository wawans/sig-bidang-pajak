<?php

namespace App\Http\Controllers\Account;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PasswordAccountController extends GeneralAccountController
{
    public function index()
    {
        return Inertia::render('account/password', [
            'mustVerifyEmail' => request()->user() instanceof MustVerifyEmail,
            'status' => request()->session()->get('status'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Informasi akun berhasil diperbarui.');
    }
}
