<?php

// Register "Seniūnijos" CPT
function bs_register_elderships_cpt() {
    register_post_type('seniunijos', [
        'labels' => [
            'name'              => 'Seniūnijos',
            'singular_name'     => 'Seniūnija',
        ],
        'public'                => true,
        'has_archive'           => true,
        'supports'              => ['title', 'custom-fields'],
        'rewrite'               => ['slug' => 'seniunijos'],
        'menu_icon'             => 'dashicons-admin-multisite',
    ]);
}
add_action('init', 'bs_register_elderships_cpt');