<?php

namespace App\Http\Controllers;

use App\Services\RestaurantAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request, RestaurantAccessService $access): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $remember = (bool) ($credentials['remember'] ?? false);
        unset($credentials['remember']);

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'ایمیل یا رمز عبور صحیح نیست.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = $request->user();
        $restaurant = $user?->activeRestaurants()->orderBy('restaurants.id')->get()
            ->first(fn ($restaurant) => $access->can($user, $restaurant, 'dashboard.view'));

        if ($restaurant) {
            return redirect()->intended(route('admin.dashboard', $restaurant->slug));
        }

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
