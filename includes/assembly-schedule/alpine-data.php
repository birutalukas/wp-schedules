<?php
function bs_get_assembly_data() {
    $assemblies = get_posts(['post_type' => 'grafikai', 'numberposts' => -1]);
    $eldership = get_posts(['post_type' => 'seniunijos', 'numberposts' => -1]);
    $streets = get_posts(['post_type' => 'gatves', 'numberposts' => -1]);

    // Fetch all terms of the 'atlieku_tipas' taxonomy
    $categories = get_terms([
        'taxonomy' => 'atlieku_tipas',
        'hide_empty' => true // Only fetch terms assigned to at least one post
    ]);

    $assembly_data = array_map(function($assembly) {
              
        $assembly_title = get_the_title($assembly->ID);

        $typesArr = [];

        $types = get_the_terms($assembly->ID, 'atlieku_tipas');
        if ($types && !is_wp_error($types)) {
            $typesArr = array_map(function($type) {
                return $type->name;
            }, $types);
        }

        $timelineArr = array_map(function($timeline) {

            $eldership_post_obj = $timeline['eldership'];
            $pdf = $timeline['assembly_pdf'];

            $timeline_schedule = array_map(function($schedule) {
            
                $days = array_map(function($day) {
                    return $day['assembly_day'];
                }, $schedule['assembly_days']);                

                return [
                    'month' => $schedule['assembly_month'],
                    'days' => $days,
                    'street_id' => $schedule['street_id'],
                ];
            }, $timeline['assembly_timeline']);

            return [
                'eldership_id' => $eldership_post_obj->ID,
                'eldership_title' => get_the_title($eldership_post_obj->ID),
                'schedule' => $timeline_schedule,
                'pdf' => $pdf,
            ];
        }, get_field('assemblies', $assembly->ID));

        return [
            'id' => $assembly->ID,
            'title' => $assembly_title,
            'timeline' => $timelineArr,
            'type' => $typesArr,
        ];

    }, $assemblies);

    $eldership_data = array_map(function($eld) {
                
        $typesArr = [];
        $types = get_the_terms($eld->ID, 'atlieku_tipas');
        if ($types && !is_wp_error($types)) {
            $typesArr = array_map(function($type) {
                return $type->name;
            }, $types);
        }              

        return [
            'id' => $eld->ID,
            'title' => get_the_title($eld->ID),
            'type' => $typesArr,
        ];

    }, $eldership);

    $street_data = array_map(function($street) {
                       
        return [
            'id' => $street->ID,
            'title' => get_the_title($street->ID),
        ];

    }, $streets);

    wp_send_json(['assembly' => $assembly_data, 'eldership' => $eldership_data, 'streets' => $street_data, 'categories' => $categories]);
}
add_action('wp_ajax_bs_get_assembly_data', 'bs_get_assembly_data');
add_action('wp_ajax_nopriv_bs_get_assembly_data', 'bs_get_assembly_data');
