@extends('layouts.customer')

@section('title', $content['title'] . ' — ' . $restaurant->name)
@section('description', $content['copy'])

@section('content')
<main class="ui-shell public-page-shell">
    <a href="{{ route('home') }}" class="public-page-back">← خانه گیلاسی</a>

    <section class="public-page-card">
        <div class="public-page-card__glow"></div>
        <div class="public-page-card__copy">
            <span class="eyebrow">{{ $content['eyebrow'] }}</span>
            <p class="public-page-card__brand">{{ $restaurant->name }}</p>
            <h1>{{ $content['title'] }}</h1>
            <p class="public-page-card__text">{{ $content['copy'] }}</p>
            <a class="public-page-card__action"
               href="{{ $content['action'] }}"
               @if(str_starts_with($content['action'], 'http')) target="_blank" rel="noopener" @endif>
                {{ $content['action_label'] }}
            </a>
        </div>

        @if($restaurant->cover_image_path)
            <div class="public-page-card__media">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($restaurant->cover_image_path) }}"
                     alt="{{ $restaurant->name }}"
                     loading="eager"
                     decoding="async"
                     width="1200"
                     height="900">
            </div>
        @endif
    </section>
</main>
@endsection
