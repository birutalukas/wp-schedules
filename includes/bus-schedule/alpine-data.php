<?php
function bs_get_schedules_data() {
    $stops = get_posts(['post_type' => 'stoteles', 'numberposts' => -1]);
    $routes = get_posts(['post_type' => 'marsrutai', 'numberposts' => -1]);

    $stops_data = array_map(function($stop) {
        return ['id' => $stop->ID, 'name' => $stop->post_title];
    }, $stops);

    $routes_data = array_map(function($route) {
                
        $typesArr = [];
        $types = get_the_terms($route->ID, 'marsruto_tipas');
        if ($types && !is_wp_error($types)) {
            $typesArr = array_map(function($type) {
                return $type->name;
            }, $types);
        }
            
        $directionsArr = [];
        $directions = get_the_terms($route->ID, 'marsruto_kryptis');
        if ($directions && !is_wp_error($directions)) {
            $directionsArr = array_map(function($direction) {
                return $direction->name;
            }, $directions);
        }

        $schedulesArr = [];

        if ( $schedules_repeater = get_field('schedules_repeater', $route->ID) ) {

            foreach ($schedules_repeater as $key => $schedule) {
                // Collect schedule details
                $scheduleData = $schedule['schedule_timeline'];

                $start_point_data = [
                    'stopID'   => $schedule['start_point'],
                    'stopName' => get_the_title($schedule['start_point']),
                    'stopNumber' => get_field('stop_number', $schedule['start_point']),
                    'stopTime' => $schedule['start_point_time'],
                    'stopLat'  => get_field('stop_lat', $schedule['start_point']),
                    'stopLang' => get_field('stop_lang', $schedule['start_point']),
                ];

                $end_point_data = [
                    'stopID'   => $schedule['end_point'],
                    'stopName' => get_the_title($schedule['end_point']),
                    'stopNumber' => get_field('stop_number', $schedule['start_point']),
                    'stopTime' => $schedule['end_point_time'],
                    'stopLat'  => get_field('stop_lat', $schedule['end_point']),
                    'stopLang' => get_field('stop_lang', $schedule['end_point']),
                ];


                // Add start point
                $startArray = [$start_point_data];
                $middleStops = [];
                if ( $stops = $schedule['stops_repeater'] ) {
                    // Map the middle stops
                    $middleStops = array_map(function($stop) {
                        return [
                            'stopID'   => $stop['stop_point'],
                            'stopName' => get_the_title($stop['stop_point']),
                            'stopNumber' => get_field('stop_number', $schedule['start_point']),
                            'stopTime' => $stop['stop_time'],
                            'stopLat'  => get_field('stop_lat', $stop['stop_point']),
                            'stopLang' => get_field('stop_lang', $stop['stop_point']),
                        ];
                    }, $stops);                    
                }    
                     
                // Add end point
                $endArray = [$end_point_data];      

                // Merge all into a single flat array
                $scheduleStops = array_merge($startArray, $middleStops, $endArray);

                // Append this schedule's data
                $schedulesArr[] = [
                    'schedule_timeline' => $scheduleData,
                    'start'             => $start_point_data,
                    'stops'             => $scheduleStops,
                    'end'               => $end_point_data,
                ];
            }
        }

        return [
            'id' => $route->ID,
            'title' => get_the_title($route->ID),
            'type' => $typesArr,
            'direction' => $directionsArr,
            'schedule' => $schedulesArr,
        ];

    }, $routes);

    wp_send_json(['stops' => $stops_data, 'routes' => $routes_data]);
}
add_action('wp_ajax_bs_get_schedules_data', 'bs_get_schedules_data');
add_action('wp_ajax_nopriv_bs_get_schedules_data', 'bs_get_schedules_data');
