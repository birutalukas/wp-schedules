<?php

// Register "Gatvės" CPT
function bs_register_streets_cpt() {
    register_post_type('gatves', [
        'labels' => [
            'name'              => 'Gatvės',
            'singular_name'     => 'Gatvė',
        ],
        'public'                => true,
        'has_archive'           => true,
        'supports'              => ['title', 'custom-fields'],
        'rewrite'               => ['slug' => 'gatves'],
        'menu_icon'             => 'dashicons-location',
    ]);
}
add_action('init', 'bs_register_streets_cpt');