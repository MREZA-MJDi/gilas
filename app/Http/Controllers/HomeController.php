<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $restaurant = $this->primaryRestaurant();

        $honeycombItems = collect([
            [
                'title' => 'منو',
                'text' => 'منوی گیلاس را ببین و انتخابت را شروع کن.',
                'cta' => 'دیدن منو',
                'url' => route('menu.index'),
                'icon' => '☕',
            ],
            [
                'title' => 'سفارش',
                'text' => 'برای سفارش آنلاین از منو وارد شو.',
                'cta' => 'شروع سفارش',
                'url' => route('menu.index'),
                'icon' => '🍒',
            ],
            [
                'title' => 'رزرو',
                'text' => $restaurant?->settings?->reservation_enabled
                    ? 'میزت را برای یک قرار خوب هماهنگ کن.'
                    : 'برای هماهنگی میز با گیلاس تماس بگیر.',
                'cta' => 'رزرو میز',
                'url' => route('public.reservation'),
                'icon' => '◷',
            ],
            [
                'title' => 'تجربه',
                'text' => 'حال‌وهوای گیلاس را کشف کن.',
                'cta' => 'کشف تجربه',
                'url' => route('public.experience'),
                'icon' => '✦',
            ],
            [
                'title' => 'مسیریابی',
                'text' => $restaurant?->address ?: 'مسیر رسیدن به گیلاس را پیدا کن.',
                'cta' => 'پیدا کردن گیلاس',
                'url' => route('public.location'),
                'icon' => '⌖',
            ],
            [
                'title' => 'داستان',
                'text' => 'گیلاس را از نگاه خودش بشناس.',
                'cta' => 'داستان گیلاس',
                'url' => route('public.story'),
                'icon' => '♡',
            ],
        ]);

        return view('welcome', compact('restaurant', 'honeycombItems'));
    }

    private function primaryRestaurant(): ?Restaurant
    {
        $query = Restaurant::query()
            ->where('status', 'active')
            ->with('settings');

        $configuredSlug = config('gilas.primary_restaurant_slug');

        if (is_string($configuredSlug) && trim($configuredSlug) !== '') {
            $configured = (clone $query)->where('slug', trim($configuredSlug))->first();

            if ($configured) {
                return $configured;
            }
        }

        return $query->orderBy('id')->first();
    }
}
