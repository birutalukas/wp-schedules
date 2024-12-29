<?php

// Register "Maršrutai" CPT
function bs_register_routes_cpt() {
    register_post_type('marsrutai', [
        'labels' => [
            'name'              => 'Maršrutai',
            'singular_name'     => 'Maršrutas',
        ],
        'public'                => true,
        'has_archive'           => true,
        'supports'              => ['title', 'custom-fields'],
        'rewrite'               => ['slug' => 'marsrutai'],
        'menu_icon'             => 'dashicons-location-alt',
    ]);
}
add_action('init', 'bs_register_routes_cpt');

// TAXONOMIES
function bs_register_routes_taxonomy() {

    register_taxonomy(
        'marsruto_tipas', // Slug
        'marsrutai',       // CPT
        array(
            'labels' => array(
                'name'              => 'Maršrutų tipai',
                'singular_name'     => 'Maršrutų tipas',
                'search_items'      => 'Ieškoti tipų',
                'all_items'         => 'Visi tipai',
                'edit_item'         => 'Redaguoti tipą',
                'update_item'       => 'Atnaujinti tipą',
                'add_new_item'      => 'Pridėti naują tipą',
                'new_item_name'     => 'Naujo tipo pavadinimas',
                'menu_name'         => 'Maršrutų tipai',
            ),
            'hierarchical' => true, 
            'show_ui'      => true,
            'show_admin_column' => true,
            'query_var'    => true,
            'rewrite'      => array('slug' => 'marsruto-tipas'),
        )
    );

    register_taxonomy(
        'marsruto_kryptis', // Slug
        'marsrutai',       // CPT
        array(
            'labels' => array(
                'name'              => 'Maršrutų kryptys',
                'singular_name'     => 'Maršrutų kryptis',
                'search_items'      => 'Ieškoti krypčių',
                'all_items'         => 'Visos kryptys',
                'edit_item'         => 'Redaguoti kryptį',
                'update_item'       => 'Atnaujinti kryptį',
                'add_new_item'      => 'Pridėti naują kryptį',
                'new_item_name'     => 'Naujos krypties pavadinimas',
                'menu_name'         => 'Maršrutų kryptys',
            ),
            'hierarchical' => true, 
            'show_ui'      => true,
            'show_admin_column' => true,
            'query_var'    => true,
            'rewrite'      => array('slug' => 'marsruto-kryptis'),
        )
    );

    
}
add_action('init', 'bs_register_routes_taxonomy');

function bs_disable_routes_taxonomy_creation() {
    if (is_admin()) {
        add_action('admin_menu', function() {
            // Only target the taxonomies related to 'marsrutai'
            $taxonomies = ['marsruto_tipas', 'marsruto_kryptis'];

            foreach ($taxonomies as $taxonomy) {
                // If taxonomy exists, we can remove it from the menu
                if (taxonomy_exists($taxonomy)) {
                    remove_submenu_page('edit.php?post_type=marsrutai', 'edit-tags.php?taxonomy=' . $taxonomy .'&amp;post_type=marsrutai');
                    remove_menu_page('edit-tags.php?taxonomy=' . $taxonomy);
                }
            }
        }, 9999);  // Make sure it runs after the default WordPress menu rendering
    }
}
add_action('init', 'bs_disable_routes_taxonomy_creation');

// TERMS
function bs_create_routes_terms() {

    if (taxonomy_exists('marsruto_tipas')) {
        // Create term „Miesto maršrutas“
        if (!term_exists('Miesto maršrutas', 'marsruto_tipas')) {
            wp_insert_term(
                'Miesto maršrutas', // Title
                'marsruto_tipas',   // Slug
                array(
                    'slug' => 'miesto-marsrutas' // Rewrite slug
                )
            );
        }

        // Create term „Užmiesčio maršrutas“
        if (!term_exists('Užmiesčio maršrutas', 'marsruto_tipas')) {
            wp_insert_term(
                'Užmiesčio maršrutas',  // Title
                'marsruto_tipas',   // Slug
                array(
                    'slug' => 'uzmiescio-marsrutas' // Rewrite slug
                )
            );
        }
    }

    if (taxonomy_exists('marsruto_kryptis')) {
        if (!term_exists('Pirmyn', 'marsruto_kryptis')) {
            wp_insert_term(
                'Pirmyn', // Title
                'marsruto_kryptis',   // Slug
                array(
                    'slug' => 'pirmyn' // Rewrite slug
                )
            );
        }
        if (!term_exists('Atgal', 'marsruto_kryptis')) {
            wp_insert_term(
                'Atgal', // Title
                'marsruto_kryptis',   // Slug
                array(
                    'slug' => 'atgal' // Rewrite slug
                )
            );
        }        
    }
}
add_action('init', 'bs_create_routes_terms');

function bs_add_taxonomy_filters_to_admin() {
    global $typenow;

    // Patikriname, ar esame teisingame CPT
    if ($typenow == 'marsrutai') {
        // Jūsų taksonomijos slug
        $type_slugs = [
            'marsruto_tipas',
            'marsruto_kryptis',
        ];

        foreach ($type_slugs as $key => $type_slug) {
    
            $terms = get_terms(array(
                'taxonomy' => $type_slug,
                'hide_empty' => false, // Rodyti ir tuščius terminus
            ));

            // Jei yra terminų, sukuriame pasirinkimo laukelį
            if ($terms) {
                echo '<select name="' . $type_slug . '" id="' . $type_slug . '" class="postform">';
                echo '<option value="">' . 'Rodyti viską' . '</option>';
                foreach ($terms as $term) {
                    // Patikriname, ar terminas pasirinktas
                    $selected = (isset($_GET[$type_slug]) && $_GET[$type_slug] == $term->slug) ? ' selected="selected"' : '';
                    echo '<option value="' . $term->slug . '"' . $selected . '>' . $term->name . '</option>';
                }
                echo '</select>';
            }
        }
    }
}
add_action('restrict_manage_posts', 'bs_add_taxonomy_filters_to_admin');

function bs_filter_posts_by_taxonomy($query) {
    global $pagenow;
    global $typenow;

    // Patikriname, ar esame administratoriaus įrašų puslapyje ir teisingame CPT
    if ($pagenow == 'edit.php' && $typenow == 'marsrutai' && isset($_GET['marsruto_tipas']) && $_GET['marsruto_tipas'] != '') {
        // Pridėti filtrą pagal pasirinktą terminą
        $query->query_vars['tax_query'] = array(
            array(
                'taxonomy' => 'marsruto_tipas',
                'field' => 'slug',
                'terms' => $_GET['marsruto_tipas'],
            )
        );
    }
    if ($pagenow == 'edit.php' && $typenow == 'marsrutai' && isset($_GET['marsruto_kryptis']) && $_GET['marsruto_kryptis'] != '') {
        // Pridėti filtrą pagal pasirinktą terminą
        $query->query_vars['tax_query'] = array(
            array(
                'taxonomy' => 'marsruto_kryptis',
                'field' => 'slug',
                'terms' => $_GET['marsruto_kryptis'],
            )
        );
    }
}
add_action('pre_get_posts', 'bs_filter_posts_by_taxonomy');
