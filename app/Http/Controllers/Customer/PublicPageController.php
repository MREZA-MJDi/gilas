<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\View\View;

class PublicPageController extends Controller
{
    public function show(string $page): View
    {
        abort_unless(in_array($page, [
            'experience',
            'reservation',
            'story',
            'location',
            'club',
        ], true), 404);

        $restaurant = $this->primaryRestaurantOrFail();

        $content = match ($page) {
            'experience' => [
                'eyebrow' => 'تجربه خانه',
                'title' => 'اینجا فقط برای خوردن نیست.',
                'copy' => 'فضایی برای مکث، گفت‌وگو و کشف یک جزئیات تازه در هر بار برگشتن.',
                'action' => route('menu.index'),
                'action_label' => 'رفتن به منو',
            ],
            'reservation' => [
                'eyebrow' => 'رزرو',
                'title' => $restaurant->settings?->reservation_enabled
                    ? 'میزت را از قبل هماهنگ کن.'
                    : 'برای هماهنگی میز با ما در تماس باش.',
                'copy' => $restaurant->phone
                    ? 'برای رزرو یا هماهنگی ظرفیت با این شماره تماس بگیر: ' . $restaurant->phone
                    : 'اطلاعات تماس و ظرفیت میز از طریق خانه گیلاسی در دسترس است.',
                'action' => $restaurant->phone ? 'tel:' . $restaurant->phone : route('public.location'),
                'action_label' => $restaurant->phone ? 'تماس با خانه گیلاسی' : 'دیدن موقعیت',
            ],
            'story' => [
                'eyebrow' => 'داستان',
                'title' => 'خانه گیلاسی، از نگاه خودش.',
                'copy' => $restaurant->description ?: 'یک خانه کوچک با طعم‌های جدی، حال خوب و جزئیات دوست‌داشتنی.',
                'action' => route('menu.index'),
                'action_label' => 'کشف منو',
            ],
            'location' => [
                'eyebrow' => 'مسیریابی',
                'title' => 'پیدا کردن خانه گیلاسی.',
                'copy' => $restaurant->address ?: 'آدرس خانه گیلاسی هنوز در تنظیمات ثبت نشده است.',
                'action' => $restaurant->latitude && $restaurant->longitude
                    ? 'https://www.google.com/maps/search/?api=1&query=' . $restaurant->latitude . ',' . $restaurant->longitude
                    : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($restaurant->address ?? $restaurant->name),
                'action_label' => 'باز کردن نقشه',
            ],
            'club' => [
                'eyebrow' => 'باشگاه گیلاس',
                'title' => 'قرار است هر بار، چیزی تازه پیدا کنی.',
                'copy' => 'این صفحه فعلاً ویترین باشگاه است؛ اتصال ثبت‌نام و امتیازدهی را روی همین foundation می‌سازیم.',
                'action' => route('menu.index'),
                'action_label' => 'برو به منو',
            ],
        };

        return view('customer.public-page', compact('restaurant', 'content', 'page'));
    }

    private function primaryRestaurantOrFail(): Restaurant
    {
        $query = Restaurant::query()
            ->where('status', 'active')
            ->with('settings');

        $configuredSlug = config('gilas.primary_restaurant_slug');

        if (is_string($configuredSlug) && trim($configuredSlug) !== '') {
            $restaurant = (clone $query)->where('slug', trim($configuredSlug))->first();
            if ($restaurant) {
                return $restaurant;
            }
        }

        return $query->orderBy('id')->firstOrFail();
    }
}
