@extends('layouts.customer')

@section('title', $content['title'] . ' — ' . $restaurant->name)
@section('description', $content['copy'])

@section('content')
<main class="gilas-editorial-page gilas-page-wrap">
    <a href="{{ route('home') }}" class="gilas-breadcrumb">{{ $restaurant->name }} <span aria-hidden="true">/</span> برگشت به خانه</a>

    <section class="gilas-editorial gilas-editorial--{{ $page }}">
        <div class="gilas-editorial__copy">
            <span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> {{ $content['eyebrow'] }}</span>
            <h1>{{ $content['title'] }}</h1>
            <p>{{ $content['copy'] }}</p>
            <div class="gilas-editorial__actions">
                <a class="gilas-button gilas-button--dark" href="{{ $content['action'] }}"
                   @if(str_starts_with($content['action'], 'http') || str_starts_with($content['action'], 'tel:')) @if(str_starts_with($content['action'], 'http')) target="_blank" rel="noopener noreferrer" @endif @endif>
                    {{ $content['action_label'] }} <span aria-hidden="true">←</span>
                </a>
                @if($page !== 'location')
                    <a class="gilas-button gilas-button--line" href="{{ route('public.location') }}">دیدن موقعیت</a>
                @endif
            </div>

            @if($page === 'reservation' && session('reservation_confirmation'))
                <div class="gilas-reservation-success" role="status">
                    <span class="gilas-reservation-success__mark" aria-hidden="true">✓</span>
                    <div>
                        <strong>درخواست رزرو ثبت شد.</strong>
                        <p>درخواست برای {{ session('reservation_confirmation.guests') }} نفر در تاریخ {{ session('reservation_confirmation.date') }} ساعت {{ session('reservation_confirmation.time') }} برای بررسی تیم رستوران ثبت شده است.</p>
                        <small>رزرو پس از تأیید مجموعه نهایی می‌شود.</small>
                    </div>
                </div>
            @endif
        </div>

        @if($page === 'reservation' && $restaurant->settings?->reservation_enabled)
            <aside class="gilas-reservation-panel">
                <div class="gilas-reservation-panel__head">
                    <span class="gilas-reservation-panel__icon" aria-hidden="true">✳</span>
                    <div><span>قرار بعدی</span><h2>درخواست رزرو میز</h2></div>
                </div>
                <p class="gilas-reservation-panel__intro">زمان و تعداد مهمان‌ها را ثبت کن؛ درخواستت برای تأیید به تیم رستوران می‌رسد.</p>
                <form class="gilas-reservation-form" method="POST" action="{{ route('public.reservations.store') }}">
                    @csrf
                    <div class="gilas-field">
                        <label for="reservation-name">نام و نام خانوادگی</label>
                        <input id="reservation-name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" maxlength="120" required>
                        @error('name')<small class="gilas-field__error">{{ $message }}</small>@enderror
                    </div>
                    <div class="gilas-field">
                        <label for="reservation-phone">شماره تماس</label>
                        <input id="reservation-phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" maxlength="40" placeholder="مثلاً 0912..." required>
                        @error('phone')<small class="gilas-field__error">{{ $message }}</small>@enderror
                    </div>
                    <div class="gilas-reservation-form__split">
                        <div class="gilas-field">
                            <label for="reservation-date">تاریخ مراجعه</label>
                            <input id="reservation-date" name="reservation_date" type="date"
                                   min="{{ now($restaurant->timezone ?: config('app.timezone'))->format('Y-m-d') }}"
                                   max="{{ now($restaurant->timezone ?: config('app.timezone'))->addDays(60)->format('Y-m-d') }}"
                                   value="{{ old('reservation_date') }}" required>
                            @error('reservation_date')<small class="gilas-field__error">{{ $message }}</small>@enderror
                        </div>
                        <div class="gilas-field">
                            <label for="reservation-time">ساعت مراجعه</label>
                            <input id="reservation-time" name="start_time" type="time" value="{{ old('start_time') }}" required>
                            @error('start_time')<small class="gilas-field__error">{{ $message }}</small>@enderror
                        </div>
                    </div>
                    <div class="gilas-field">
                        <label for="reservation-guests">تعداد مهمان‌ها</label>
                        <select id="reservation-guests" name="guest_count" required>
                            <option value="">تعداد نفرات را انتخاب کن</option>
                            @foreach(range(1, 20) as $guestCount)
                                <option value="{{ $guestCount }}" @selected((string) old('guest_count') === (string) $guestCount)>{{ $guestCount }} نفر</option>
                            @endforeach
                        </select>
                        @error('guest_count')<small class="gilas-field__error">{{ $message }}</small>@enderror
                    </div>
                    <div class="gilas-field">
                        <label for="reservation-note">توضیح برای میزبان <span>اختیاری</span></label>
                        <textarea id="reservation-note" name="customer_note" rows="3" maxlength="1000" placeholder="ترجیح میز، مناسبت یا نکته‌ای که بهتر است بدانیم…">{{ old('customer_note') }}</textarea>
                        @error('customer_note')<small class="gilas-field__error">{{ $message }}</small>@enderror
                    </div>
                    @error('reservation')<p class="gilas-field__error">{{ $message }}</p>@enderror
                    <button class="gilas-button gilas-button--dark gilas-reservation-submit" type="submit">ثبت درخواست رزرو <span aria-hidden="true">←</span></button>
                    <small class="gilas-reservation-disclaimer">ارسال فرم به معنی درخواست رزرو است؛ تأیید نهایی توسط رستوران انجام می‌شود.</small>
                </form>
            </aside>
        @elseif($restaurant->cover_image_path)
            <div class="gilas-editorial__visual">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) }}" alt="{{ $restaurant->name }}" width="1200" height="1000" loading="eager" decoding="async">
                <span>{{ $restaurant->name }}</span>
            </div>
        @else
            <div class="gilas-editorial__visual gilas-editorial__visual--empty">
                <span>{{ mb_substr($restaurant->name, 0, 1) }}</span>
                <small>تصویر از تنظیمات رستوران نمایش داده می‌شود</small>
            </div>
        @endif
    </section>

    @if($page === 'reservation' && !$restaurant->settings?->reservation_enabled)
        <section class="gilas-reservation-disabled">
            <span class="gilas-reservation-disabled__mark">i</span>
            <div><strong>رزرو آنلاین فعلاً فعال نیست.</strong><p>برای هماهنگی مراجعه، از اطلاعات تماس و موقعیت رستوران استفاده کن.</p></div>
            @if($restaurant->phone)
                <a class="gilas-button gilas-button--dark" href="tel:{{ preg_replace('/[^0-9+]/', '', $restaurant->phone) }}">تماس با رستوران</a>
            @endif
        </section>
    @endif

    <section class="gilas-editorial-next">
        <div><span class="gilas-eyebrow"><span class="gilas-eyebrow__line"></span> ادامهٔ تجربه</span><h2>از اینجا کجا برویم؟</h2></div>
        <div class="gilas-editorial-next__links">
            <a href="{{ route('menu.index') }}">منوی غذا و نوشیدنی <span aria-hidden="true">↗</span></a>
            <a href="{{ route('public.story') }}">داستان خانه <span aria-hidden="true">↗</span></a>
            <a href="{{ route('public.location') }}">آدرس و مسیر <span aria-hidden="true">↗</span></a>
        </div>
    </section>
</main>
@endsection
