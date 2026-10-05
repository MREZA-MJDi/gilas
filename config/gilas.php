<?php

return [
    'primary_restaurant_slug' => env('GILAS_PRIMARY_RESTAURANT_SLUG'),
    'hero_items_limit' => min(25, max(1, (int) env('GILAS_HERO_ITEMS_LIMIT', 15))),
];
