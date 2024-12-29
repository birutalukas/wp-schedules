<?php
function bus_schedule_shortcode( $atts ) {

    // Set default values
    $atts = shortcode_atts(array(
        'type' => 'all_routes', // Default to 'all_routes' if type is not provided
    ), $atts, 'bus_schedules');

    ob_start(); ?>
    <div id="bus-schedule" x-data="busSchedules" x-init="initBusSchedules()" data-type="<?= esc_attr($atts['type']); ?>">
        <div class="bs-search-wrapper">

            <div class="bs-search-column">
                <!-- Išvykimo pasirinkimas -->
                <div class="bs-input-wrapper"  x-model="startObj">
                    <div class="bs-search-dropdown">
                        <!-- Combobox Input -->
                        <label for="start-stop" class="bs-label">Važiuosiu iš</label>
                        <input
                            x-model="startQuery"
                            @input="showStartDropdown = true"
                            @focus="showStartDropdown = true"
                            @blur="hideDropdown('start')"
                            class="bs-input"
                            placeholder="Ieškoti..."
                            autocomplete="off"
                            id="start-stop"    
                            name="start-stop"
                        />        
                    </div>
            
                    <!-- Combobox Options -->
                    <div x-cloak x-show="showStartDropdown" class="bs-search-dropdown-content">
                        <ul class="">
                            <template x-for="stop in filteredStart" :key="stop.id">
                                <!-- Combobox Option -->
                                <li
                                    @click="selectStart(stop)"
                                    class="group flex w-full cursor-default items-center rounded-md px-2 py-1.5 transition-colors"
                                    x-text="stop.name"
                                >
                                </li>
                            </template>
                            <li x-show="filteredStart.length == 0">Nerasta...</li>
                        </ul>
            
                        
                    </div>
                </div>

                <!-- Galutinės stotelės pasirinkimas -->
                <div class="bs-input-wrapper" x-model="endObj">
                    <!-- Combobox -->
                    <div class="bs-search-dropdown">
                        <!-- Combobox Input -->
                        <label for="end-stop" class="bs-label">Važiuosiu į</label>                        
                        <input
                            x-model="endQuery"
                            @input="showEndDropdown = true"
                            @focus="showEndDropdown = true"
                            @blur="hideDropdown('end')"
                            class="bs-input"
                            placeholder="Ieškoti..."
                            autocomplete="off"
                            id="end-stop"
                            name="end-stop"
                        />
                    </div>
            
                    <!-- Combobox Options -->
                    <div x-cloak x-show="showEndDropdown" class="bs-search-dropdown-content">
                        <ul class="">
                            <template x-for="stop in filteredEnd" :key="stop.id">
                                <!-- Combobox Option -->
                                <li
                                    @click="selectEnd(stop)"
                                    class="group flex w-full cursor-default items-center rounded-md px-2 py-1.5 transition-colors"                                
                                    x-text="stop.name"
                                >
                                </li>
                            </template>
                            <li x-show="filteredEnd.length == 0">Nerasta...</li>
                        </ul>
            
                        
                    </div>
                </div>   
            </div>
            <div class="bs-date-column">
                <div class="bs-input-wrapper">
                    <!-- Combobox -->
                    <div class="bs-search-dropdown">
                        <!-- Combobox Input -->
                        <label for="datePicker" class="bs-label">Išvykimo data</label>
                        <div class="bs-input bs-input--icon-wrapper">
                            <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M15.9 7.4H1.5M11.9 1V4.2M5.5 1V4.2M5.34 17H12.06C13.4041 17 14.0762 17 14.5896 16.7384C15.0412 16.5083 15.4083 16.1412 15.6384 15.6896C15.9 15.1762 15.9 14.5041 15.9 13.16V6.44C15.9 5.09587 15.9 4.42381 15.6384 3.91042C15.4083 3.45883 15.0412 3.09168 14.5896 2.86158C14.0762 2.6 13.4041 2.6 12.06 2.6H5.34C3.99587 2.6 3.32381 2.6 2.81042 2.86158C2.35883 3.09168 1.99168 3.45883 1.76158 3.91042C1.5 4.42381 1.5 5.09587 1.5 6.44V13.16C1.5 14.5041 1.5 15.1762 1.76158 15.6896C1.99168 16.1412 2.35883 16.5083 2.81042 16.7384C3.32381 17 3.99587 17 5.34 17Z" stroke="#818B89" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                            <input
                                class="bs-input--icon__input-el"
                                id="datePicker"
                                name="datePicker"
                                x-ref="datePicker"
                            />
                        </div>
                    </div>                             
                </div>                  
                
                <div class="bs-input-wrapper">
                    <!-- Combobox -->
                    <div class="bs-search-dropdown">
                        <!-- Combobox Input -->
                        <label for="timePicker" class="bs-label">Išvykimo laikas</label>
                        <div class="bs-input bs-input--icon-wrapper">
                            <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.7 4.2V9L11.58 10.6M15.9 9C15.9 13.4183 12.6765 17 8.7 17C4.72355 17 1.5 13.4183 1.5 9C1.5 4.58172 4.72355 1 8.7 1C12.6765 1 15.9 4.58172 15.9 9Z" stroke="#818B89" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                            <input
                                class="bs-input--icon__input-time"
                                id="timePicker"
                                name="timePicker"
                                x-ref="timePicker"
                                style="display: none;"
                            />
                        </div>
                    </div>                             
                </div>                  

            </div>
        </div>

        <div class="bs-cta-wrapper">
            <button class="bs-button" @click="findRoutesBetweenStops(routes, startObj.id, endObj.id)" :disabled="!startObj.id || !endObj.id">Ieškoti kelionės</button>    
        </div>

        <!-- Tvarkaraštis -->
        <template x-if="computedRoutes().length">
            <div class="bs-results-wrapper">  
                
                <h3 class="bs-results-title">Maršrutai</h3>
            
                <template x-for="(routeSchedule, index) in computedRoutes()" :key="index">
                    <div class="bs-results-item">
                        <div class="bs-results-item--left">
                            <h3>
                                <span class="bs-time" x-text="routeSchedule.start.stopTime"></span>
                                <span class="bs-time"> - </span>
                                <span class="bs-time" x-text="routeSchedule.end.stopTime"></span>
                            </h3>
                            <span>Trukmė: <span x-text="routeSchedule.duration"></span></span>
                        </div>
                        <div class="bs-results-item--middle">
                            <p class="route-title" x-text="routeSchedule.title"></p>
                        </div>
                        
                        <div class="bs-results-item--right">
                            <button class="bs-button" @click="openModal(routeSchedule)">
                                Peržiūrėti maršrutą
                            </button>
                        </div>                       
                    </div>
                </template>
            </div>
        </template>

        <template x-if="notFoundMessage">
            <p x-text="notFoundMessage"></p>
        </template>

        <!-- Modal -->
        <div
            x-show="isModalOpen"
            x-cloak
            @keydown.escape.window="closeModal()"
            class="bs-modal-wrapper"
        >
            <div class="bs-modal-overlay" @click="closeModal()" ></div>
            <div class="bs-modal-content">
                
                <div class="bs-modal-content--left">
                    
                    <div class="bs-modal-head">
                        <h2 class="bs-modal-title" x-text="modalData.title"></h2>
                    </div>
                    
                    <div class="bs-route-schedule">
                        <template x-for="(stop, index) in modalData.stops" :key="stop.stopID">
                            <div class="bs-route-schedule-item">
                                <div class="bs-route-schedule-item--left">

                                    <template x-if="index === 0">
                                        <div class="bs-route-schedule-item__dot--large" :class="{'bs-user-choice' : startObj.id === stop.stopID || endObj.id === stop.stopID}">A</div>
                                    </template>

                                    <template x-if="index !== 0 && index !== modalData.stops.length - 1">
                                        <div class="bs-route-schedule-item__dot--small" :class="{'bs-user-choice' : startObj.id === stop.stopID || endObj.id === stop.stopID}"></div>
                                    </template>

                                    <template x-if="index === modalData.stops.length - 1">
                                        <div class="bs-route-schedule-item__dot--large" :class="{'bs-user-choice' : startObj.id === stop.stopID || endObj.id === stop.stopID}">B</div>
                                    </template>

                                </div>
                                <span class="bs-route-schedule-time" x-text="stop.stopTime"></span>

                                <span class="bs-route-schedule-stop" x-html="stop.stopName"></span>
                            </div>
                        </template>
                    </div>
                </div>
                
                <div class="bs-modal-content--right" >
                    <div id="bs-routeMap" class="bs-route-map"></div>
                </div>
                <button
                    @click="closeModal()"
                    class="bs-button-icon"
                >
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.4 6.6L6.6 11.4M6.6 6.6L11.4 11.4M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z" stroke="#222E2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <?php return ob_get_clean();
}
add_shortcode('bus_schedules', 'bus_schedule_shortcode');
