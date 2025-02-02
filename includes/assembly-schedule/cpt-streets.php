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

add_filter('manage_gatves_posts_columns', function($columns) {
    $columns['seniunija'] = __('Seniūnija', 'textdomain');
    return $columns;
});

add_action('manage_gatves_posts_custom_column', function($column, $post_id) {
    if ($column === 'seniunija') {

        $eldership_id = get_field('eldership_id', $post_id);
        if ( $eldership_id ) {
            echo get_the_title($eldership_id);
        }
    }
}, 10, 2);

add_filter('manage_edit-gatves_sortable_columns', function($columns) {
    $columns['seniunija'] = 'seniunija';
    return $columns;
});

