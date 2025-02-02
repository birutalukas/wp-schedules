<?php
function bs_get_assembly_data() {
    $assemblies = get_posts(['post_type' => 'grafikai', 'numberposts' => -1]);
    $eldership = get_posts(['post_type' => 'seniunijos', 'numberposts' => -1]);
    $streets = get_posts(['post_type' => 'gatves', 'numberposts' => -1]);

    // Fetch all terms of the 'atlieku_tipas' taxonomy
    $categories = get_terms([
        'taxonomy' => 'atlieku_tipas',
        'hide_empty' => true, // Only fetch terms assigned to at least one post
    ]);

    $assembly_data = array_map(function($assembly) {

        $assembly_title = get_the_title($assembly);
    
        $typesArr = [];
        $types = get_the_terms($assembly->ID, 'atlieku_tipas');
        if ($types && !is_wp_error($types)) {
            $typesArr = array_map(function($type) {
                return $type->name;
            }, $types);
        }
    
        $assemblies_field = get_field('assemblies', $assembly);
        if (!is_array($assemblies_field)) {
            $assemblies_field = []; // Ensure it's an array
        }
    
        $timelineArr = array_map(function($timeline) {
            $eldership_ids = $timeline['eldership']; // Should be an array of IDs
            if (!is_array($eldership_ids)) {
                $eldership_ids = []; // Ensure it's an array
            }
            $pdf = $timeline['assembly_pdf'];
        
            $timeline_schedule = array_map(function($schedule) {
                $days = array_map(function($day) {
                    return $day['assembly_day'];
                }, $schedule['assembly_days']);     
    
                return [
                    'days' => $days,
                    'street_id' => $schedule['street_id'],
                ];
            }, $timeline['assembly_timeline']);
    
            $elderships = array_map(function($eldership_id) use ($timeline_schedule, $pdf) {
                return [
                    'eldership_id' => $eldership_id,
                    'eldership_title' => get_the_title($eldership_id),
                    'schedule' => $timeline_schedule,
                    'pdf' => $pdf,
                ];
            }, $eldership_ids);
    
            return $elderships; // Return data for all elderships
        }, $assemblies_field);
    
        return [
            'id' => $assembly->ID,
            'title' => $assembly_title,
            'timeline' => $timelineArr,
            'type' => $typesArr,
        ];
    
    }, $assemblies);

    $eldership_data = array_map(function($eld) {
                
        return [
            'id' => $eld->ID,
            'title' => get_the_title($eld->ID),
        ];

    }, $eldership);

    $street_data = array_map(function($street) {
                       
        return [
            'id' => $street->ID,
            'title' => get_the_title($street->ID),
            'eldership_id' => get_field('eldership_id', $street->ID),
        ];

    }, $streets);

    wp_send_json(['assembly' => $assembly_data, 'eldership' => $eldership_data, 'streets' => $street_data, 'categories' => $categories]);
}
add_action('wp_ajax_bs_get_assembly_data', 'bs_get_assembly_data');
add_action('wp_ajax_nopriv_bs_get_assembly_data', 'bs_get_assembly_data');

