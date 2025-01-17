<?php

// Register "Grafikai" CPT
function bs_register_assembly_cpt() {
    register_post_type('grafikai', [
        'labels' => [
            'name'              => 'Grafikai',
            'singular_name'     => 'Grafikas',
        ],
        'public'                => true,
        'has_archive'           => true,
        'supports'              => ['title', 'custom-fields'],
        'rewrite'               => ['slug' => 'grafikai'],
        'menu_icon'             => 'dashicons-analytics',
    ]);
}
add_action('init', 'bs_register_assembly_cpt');

// TAXONOMIES
function bs_register_assembly_taxonomy() {

    register_taxonomy(
        'atlieku_tipas', // Slug
        'grafikai',       // CPT
        array(
            'labels' => array(
                'name'              => 'Atliekų tipai',
                'singular_name'     => 'Atliekų tipas',
                'search_items'      => 'Ieškoti tipų',
                'all_items'         => 'Visi tipai',
                'edit_item'         => 'Redaguoti tipą',
                'update_item'       => 'Atnaujinti tipą',
                'add_new_item'      => 'Pridėti naują tipą',
                'new_item_name'     => 'Naujo tipo pavadinimas',
                'menu_name'         => 'Atliekų tipai',
            ),
            'hierarchical' => true, 
            'show_ui'      => true,
            'show_admin_column' => true,
            'query_var'    => true,
            'rewrite'      => array('slug' => 'atlieku-tipas'),
        )
    );
    
}
add_action('init', 'bs_register_assembly_taxonomy');