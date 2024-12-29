<?php
// Register "Stotelės" CPT
function bs__register_stops_cpt() {
    register_post_type('stoteles', [
        'labels' => [
            'name'              => 'Stotelės',
            'singular_name'     => 'Stotelė',
        ],
        'public'                => true,
        'supports'              => ['title', 'custom-fields'],
        'rewrite'               => ['slug' => 'stoteles'],
        'menu_icon'             => 'dashicons-location'
    ]);
}
add_action('init', 'bs__register_stops_cpt');

// TAXONOMIES
function bs_register_stops_taxonomy() {
    // Registruojame naują taksonomiją
    register_taxonomy(
        'stoteles_tipas', // Slug
        'stoteles',       // CPT
        array(
            'labels' => array(
                'name'              => 'Stotelės tipai',
                'singular_name'     => 'Stotelės tipas',
                'search_items'      => 'Ieškoti tipų',
                'all_items'         => 'Visi tipai',
                'edit_item'         => 'Redaguoti tipą',
                'update_item'       => 'Atnaujinti tipą',
                'add_new_item'      => 'Pridėti naują tipą',
                'new_item_name'     => 'Naujo tipo pavadinimas',
                'menu_name'         => 'Stotelės tipai',
            ),
            'hierarchical' => true, 
            'show_ui'      => true,
            'show_admin_column' => true,
            'query_var'    => true,
            'rewrite'      => array('slug' => 'stoteles-tipas'),
        )
    );
}
add_action('init', 'bs_register_stops_taxonomy');

function bs_disable_stops_taxonomy_creation() {
    if (is_admin()) {
        add_action('admin_menu', function() {
            // If taxonomy exists, we can remove it from the menu
            if (taxonomy_exists('stoteles_tipas')) {
                remove_submenu_page('edit.php?post_type=stoteles', 'edit-tags.php?taxonomy=stoteles_tipas&amp;post_type=stoteles');
                remove_menu_page('edit-tags.php?taxonomy=stoteles_tipas');
            }
        }, 9999);  // Make sure it runs after the default WordPress menu rendering
    }
}
add_action('init', 'bs_disable_stops_taxonomy_creation');

// TERMS
function bs_create_stops_terms() {

    if (taxonomy_exists('stoteles_tipas')) {
        // Create term „Galutinė stotelė“
        if (!term_exists('Galutinė stotelė', 'stoteles_tipas')) {
            wp_insert_term(
                'Galutinė stotelė', // Title
                'stoteles_tipas',   // Slug
                array(
                    'slug' => 'galutine-stotele' // Rewrite slug
                )
            );
        }

        // Create term „Tarpinė stotelė“
        if (!term_exists('Tarpinė stotelė', 'stoteles_tipas')) {
            wp_insert_term(
                'Tarpinė stotelė',  // Title
                'stoteles_tipas',   // Slug
                array(
                    'slug' => 'tarpine-stotele' // Rewrite slug
                )
            );
        }
    }
}
add_action('init', 'bs_create_stops_terms');

