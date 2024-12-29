<?php


add_action('admin_init', 'bs_load_custom_acf');

function bs_load_custom_acf() {
    $acf_dir = WP_PLUGIN_DIR . '/advanced-custom-fields-pro';
    $acf_file = $acf_dir . '/acf.php';

    // If ACF Pro doesn't exist, copy it from your plugin directory
    if ( ! file_exists($acf_file) ) {
        $source = plugin_dir_path(__FILE__) . 'plugins/advanced-custom-fields-pro';
        bs_recurse_copy($source, $acf_dir);
    }

    // If ACF Pro exists but isn't active, activate it
    if ( file_exists($acf_file) && ! class_exists('ACF') ) {
        include_once $acf_file;

        if ( ! is_plugin_active('advanced-custom-fields-pro/acf.php') ) {
            activate_plugin('advanced-custom-fields-pro/acf.php');
        }
    }
}

/**
 * Recursively copy files and directories.
 */
function bs_recurse_copy($source, $destination) {
    $dir = opendir($source);
    @mkdir($destination);

    while ( false !== ($file = readdir($dir)) ) {
        if ( $file != '.' && $file != '..' ) {
            if ( is_dir($source . '/' . $file) ) {
                bs_recurse_copy($source . '/' . $file, $destination . '/' . $file);
            } else {
                copy($source . '/' . $file, $destination . '/' . $file);
            }
        }
    }

    closedir($dir);
}

add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
        'key' => 'group_6751e04076976',
        'title' => 'Maršrutas',
        'fields' => array(
            array(
                'key' => 'field_67700ee023e41',
                'label' => 'Tvarkaraščiai',
                'name' => 'schedules_repeater',
                'aria-label' => '',
                'type' => 'repeater',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array(
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'layout' => 'block',
                'pagination' => 0,
                'min' => 0,
                'max' => 0,
                'collapsed' => '',
                'button_label' => 'Pridėti maršruto laikus',
                'rows_per_page' => 20,
                'sub_fields' => array(
                    array(
                        'key' => 'field_677010919abc8',
                        'label' => 'Maršruto važiavimo dienos',
                        'name' => 'schedule_timeline',
                        'aria-label' => '',
                        'type' => 'select',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'choices' => array(
                            'Pirmadienis' => 'Pirmadienis',
                            'Antradienis' => 'Antradienis',
                            'Trečiadienis' => 'Trečiadienis',
                            'Ketvirtadienis' => 'Ketvirtadienis',
                            'Penktadienis' => 'Penktadienis',
                            'Šeštadienis' => 'Šeštadienis',
                            'Sekmadienis' => 'Sekmadienis',
                            'Darbo dienos' => 'Darbo dienos',
                            'Savaitgalis' => 'Savaitgalis',
                        ),
                        'default_value' => array(
                        ),
                        'return_format' => 'value',
                        'multiple' => 1,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'ui' => 1,
                        'ajax' => 0,
                        'placeholder' => '',
                        'parent_repeater' => 'field_67700ee023e41',
                    ),
                    array(
                        'key' => 'field_6751e040ae081',
                        'label' => 'Pirminė stotelė',
                        'name' => 'start_point',
                        'aria-label' => '',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '70',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array(
                            0 => 'stoteles',
                        ),
                        'post_status' => array(
                            0 => 'publish',
                        ),
                        'taxonomy' => '',
                        'return_format' => 'id',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'bidirectional' => 0,
                        'ui' => 1,
                        'bidirectional_target' => array(
                        ),
                        'parent_repeater' => 'field_67700ee023e41',
                    ),
                    array(
                        'key' => 'field_6751e1651dec0',
                        'label' => 'Išvykimo laikas',
                        'name' => 'start_point_time',
                        'aria-label' => '',
                        'type' => 'time_picker',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '30',
                            'class' => '',
                            'id' => '',
                        ),
                        'display_format' => 'H:i',
                        'return_format' => 'H:i',
                        'allow_in_bindings' => 0,
                        'parent_repeater' => 'field_67700ee023e41',
                    ),
                    array(
                        'key' => 'field_6751e094ae082',
                        'label' => 'Stotelės',
                        'name' => 'stops_repeater',
                        'aria-label' => '',
                        'type' => 'repeater',
                        'instructions' => '',
                        'required' => 0,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '',
                            'class' => '',
                            'id' => '',
                        ),
                        'layout' => 'block',
                        'pagination' => 0,
                        'min' => 0,
                        'max' => 0,
                        'collapsed' => '',
                        'button_label' => 'Pridėti stotelę',
                        'rows_per_page' => 20,
                        'sub_fields' => array(
                            array(
                                'key' => 'field_6751e0d01debe',
                                'label' => 'Stotelė',
                                'name' => 'stop_point',
                                'aria-label' => '',
                                'type' => 'post_object',
                                'instructions' => '',
                                'required' => 1,
                                'conditional_logic' => 0,
                                'wrapper' => array(
                                    'width' => '70',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'post_type' => array(
                                    0 => 'stoteles',
                                ),
                                'post_status' => array(
                                    0 => 'publish',
                                ),
                                'taxonomy' => '',
                                'return_format' => 'id',
                                'multiple' => 0,
                                'allow_null' => 0,
                                'allow_in_bindings' => 0,
                                'bidirectional' => 0,
                                'ui' => 1,
                                'bidirectional_target' => array(
                                ),
                                'parent_repeater' => 'field_6751e094ae082',
                            ),
                            array(
                                'key' => 'field_6751e11d1debf',
                                'label' => 'Sustojimo laikas',
                                'name' => 'stop_time',
                                'aria-label' => '',
                                'type' => 'time_picker',
                                'instructions' => '',
                                'required' => 1,
                                'conditional_logic' => 0,
                                'wrapper' => array(
                                    'width' => '30',
                                    'class' => '',
                                    'id' => '',
                                ),
                                'display_format' => 'H:i',
                                'return_format' => 'H:i',
                                'allow_in_bindings' => 0,
                                'parent_repeater' => 'field_6751e094ae082',
                            ),
                        ),
                        'parent_repeater' => 'field_67700ee023e41',
                    ),
                    array(
                        'key' => 'field_6751e18f1dec1',
                        'label' => 'Galutinės stotelė',
                        'name' => 'end_point',
                        'aria-label' => '',
                        'type' => 'post_object',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '70',
                            'class' => '',
                            'id' => '',
                        ),
                        'post_type' => array(
                            0 => 'stoteles',
                        ),
                        'post_status' => array(
                            0 => 'publish',
                        ),
                        'taxonomy' => '',
                        'return_format' => 'id',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'allow_in_bindings' => 0,
                        'bidirectional' => 0,
                        'ui' => 1,
                        'bidirectional_target' => array(
                        ),
                        'parent_repeater' => 'field_67700ee023e41',
                    ),
                    array(
                        'key' => 'field_6751e1a31dec2',
                        'label' => 'Atvykimo laikas',
                        'name' => 'end_point_time',
                        'aria-label' => '',
                        'type' => 'time_picker',
                        'instructions' => '',
                        'required' => 1,
                        'conditional_logic' => 0,
                        'wrapper' => array(
                            'width' => '30',
                            'class' => '',
                            'id' => '',
                        ),
                        'display_format' => 'H:i',
                        'return_format' => 'H:i',
                        'allow_in_bindings' => 0,
                        'parent_repeater' => 'field_67700ee023e41',
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'marsrutai',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
        'show_in_rest' => 0,
    ) );

        acf_add_local_field_group( array(
            'key' => 'group_675eee3b95d0b',
            'title' => 'Stotelė',
            'fields' => array(
                array(
                    'key' => 'field_675eee3b23cd1',
                    'label' => 'Lat',
                    'name' => 'stop_lat',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
                array(
                    'key' => 'field_675eee5123cd2',
                    'label' => 'Lang',
                    'name' => 'stop_lang',
                    'aria-label' => '',
                    'type' => 'text',
                    'instructions' => '',
                    'required' => 0,
                    'conditional_logic' => 0,
                    'wrapper' => array(
                        'width' => '50',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'maxlength' => '',
                    'allow_in_bindings' => 0,
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'stoteles',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
            'active' => true,
            'description' => '',
            'show_in_rest' => 0,
            ) 
        );
    } 
);

// Activate ACF PRO Automatically
// add_action('acf/init', 'activate_acf_pro_license');
// function activate_acf_pro_license() {
//     if ( function_exists('acf_pro_update_license') ) {
//         acf_pro_update_license('YOUR_LICENSE_KEY_HERE');
//     }
// }
