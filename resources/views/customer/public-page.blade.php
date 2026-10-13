@extends('layouts.customer')

@section('title', $content['title'] . ' — ' . $restaurant->name)
@section('description', $content['copy'])

@section('content')
<main class="ui-shell public-info">
    <a href="{{ route('home') }}" class="public-info__back">← {{ $restaurant->name }}</a>
    <section class="public-info__grid">
        <div class="public-info__copy">
            <span class="ui-kicker">{{ $content['eyebrow'] }}</span>
            <h1>{{ $content['title'] }}</h1>
            <p>{{ $content['copy'] }}</p>
            <a class="ui-button ui-button--primary" href="{{ $content['action'] }}" @if(str_starts_with($content['action'], 'http')) target="_blank" rel="noopener" @endif>
                {{ $content['action_label'] }}
            </a>
        </div>
        @if($restaurant->cover_image_path)
            <div class="public-info__media">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($restaurant->cover_image_path) }}" alt="{{ $restaurant->name }}" width="1200" height="900" loading="eager">
            </div>
        @endif
    </section>
</main>
@endsection
