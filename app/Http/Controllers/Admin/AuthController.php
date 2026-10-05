<?php

namespace AppHttpControllersAdmin;

use AppHttpControllersController;
use IlluminateHttpRedirectResponse;
use IlluminateHttpRequest;
use IlluminateSupportFacadesAuth;
use IlluminateViewView;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('admin_auth_login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        if (!Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            (bool) ($credentials['remember'] ?? false)
        )) {
            return back()
                ->withErrors(['email' => 'ایمیل یا رمز عبور صحیح نیست.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $restaurant = Auth::user()->activeRestaurants()->orderBy('restaurants.id')->first();

        if (!$restaurant) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors(['email' => 'این حساب به هیچ رستوران فعالی دسترسی ندارد.']);
        }

        return redirect()->route('admin.dashboard', ['restaurant' => $restaurant]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
