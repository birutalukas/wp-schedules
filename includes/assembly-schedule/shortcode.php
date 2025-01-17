<?php
function assembly_schedule_shortcode( $atts ) {


    ob_start(); ?>

    <div id="assembly-schedule" x-data="assemblySchedules" x-init="initAssemblySchedules();">

        <p class="as-categories-header">Pasirinkite dominančių atliekų tipą:</p>

        <div class="as-categories-wrapper">
            <template x-for="cat in categories" :key="cat.term_id">
                <button class="as-category-button" @click="selectedCategory === cat.name ? selectedCategory = '' : selectedCategory = cat.name" :style="selectedCategory === cat.name ? 'background-color: #df7a5e;' : '';" x-text="cat.name"></button>
            </template>
        </div>

        <div class="as-search-wrapper">

            <!-- Seniūnijos pasirinkimas -->
            <div class="bs-input-wrapper"  x-model="eldershipObj">
                <div class="bs-search-dropdown">
                    
                    <label for="eldership" class="bs-label">Seniūnija</label>
                    <input
                        x-model="eldershipQuery"
                        @input="showEldershipDropdown = true"
                        @focus="showEldershipDropdown = true"
                        @blur="hideDropdown('eldership')"
                        class="bs-input"
                        placeholder="Ieškoti..."
                        autocomplete="off"
                        id="eldership"    
                        name="eldership"
                    />        
                </div>
                            
                <div x-show="showEldershipDropdown" class="bs-search-dropdown-content">
                    <ul class="">
                        <template x-for="eldership in filteredEldership" :key="eldership.id">                                
                            <li
                                @click="selectEldership(eldership)"
                                class="group flex w-full cursor-default items-center rounded-md px-2 py-1.5 transition-colors"
                                x-text="eldership.title"
                            >
                            </li>
                        </template>
                        <li x-show="filteredEldership?.length == 0">Nerasta...</li>
                    </ul>
        
                    
                </div>
            </div>

            <!-- Gatvės pasirinkimas -->
            <div class="bs-input-wrapper" x-model="streetObj">
                
                <div class="bs-search-dropdown">
                    
                    <label for="street" class="bs-label">Gatvė</label>                        
                    <input
                        x-model="streetQuery"
                        @input="showStreetDropdown = true"
                        @focus="showStreetDropdown = true"
                        @blur="hideDropdown('street')"
                        class="bs-input"
                        placeholder="Ieškoti..."
                        autocomplete="off"
                        id="street"
                        name="street"
                    />
                </div>
        
                
                <div  x-show="showStreetDropdown" class="bs-search-dropdown-content">
                    <ul class="">
                        <template x-for="street in filteredStreet" :key="street.id">
                            
                            <li
                                @click="selectStreet(street)"
                                class="group flex w-full cursor-default items-center rounded-md px-2 py-1.5 transition-colors"                                
                                x-text="street.title"
                            >
                            </li>
                        </template>
                        <li x-show="filteredStreet?.length == 0">Nerasta...</li>
                    </ul>                                    
                </div>
            </div>   
          
        </div>

        <div class="bs-cta-wrapper">
            <button class="bs-button" @click="findAssemblySchedule">Ieškoti grafiko</button>    
        </div>

        <!-- Tvarkaraštis -->
        <template x-if="computedAssemblies().length">
            <div class="bs-results-wrapper">
                <h3 class="bs-results-title">
                    Nurodytu adresu rasta viso objektų: 
                    <span x-text="computedAssemblies().length"></span>
                </h3>

                <template x-for="(assemblySchedule, index) in computedAssemblies()" :key="index">
                    <div class="bs-results-item">
                        <div class="bs-results-item--left as-results-item--left">
                            <h3 class="as-assempbly-type-title" x-text="assemblySchedule?.type"></h3>
                            <span x-text="assemblySchedule?.timeline[0]?.pdf?.filename || ''"></span>
                        </div>
                        <div class="bs-results-item--middle as-results-item--middle">

                        <p class="as-modal-address">
                            <span x-text="streetObj?.title ? streetObj.title + ' ' : ''"></span>                          
                            <template x-if="streetObj?.title">
                                <span>, </span>
                            </template>
                            <span x-text="eldershipObj?.title"></span>
                        </p>    

                            <p class="as-address" x-text="assemblySchedule.title"></p>
                        </div>
                        <div class="bs-results-item--right as-results-item--right">
                            <div class="as-button-wrapper">
                                <button 
                                    class="bs-button as-button" 
                                    @click="openModal(assemblySchedule)"
                                >
                                    Peržiūrėti grafiką
                                </button>
                                <template x-if="assemblySchedule?.timeline[0]?.pdf">
                                    <a :href="assemblySchedule?.timeline[0].pdf?.link" target="_blank" class="bs-button as-button as-button--secondary" >
                                        Atsisiųsti
                                        <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5.8 9.5L9 12.7M9 12.7L12.2 9.5M9 12.7V6.3M17 9.5C17 13.9183 13.4183 17.5 9 17.5C4.58172 17.5 1 13.9183 1 9.5C1 5.08172 4.58172 1.5 9 1.5C13.4183 1.5 17 5.08172 17 9.5Z" stroke="#B75B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                </template>                                
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <!-- Modal -->
        <div 
            x-show="isModalOpen" 
            x-cloak
            @keydown.escape.window="closeModal()" 
            class="bs-modal-wrapper"            
        >
            <div class="bs-modal-overlay" @click="closeModal()"></div>
            <div class="bs-modal-content as-modal-content">

                <div class="bs-modal-content--left as-modal-content--left">

                    <div class="as-modal-head">

                        <template x-for="(type, index) in modalData?.type">
                            <h2 class="as-modal-title" x-text="type"></h2>
                        </template>
                        
                        <p class="as-modal-subtitle">
                            MA-16985526_1.6 TO_DOOO
                        </p>
                        
                    </div>

                    <div class="as-modal-body">      
                        <p class="as-modal-address">
                            <span x-text="streetObj?.title ? streetObj.title + ' ' : ''"></span>                     
                            <template x-if="streetObj?.title">
                                <span>, </span>
                            </template>
                            <span x-text="eldershipObj?.title"></span>
                        </p>          
                        <template x-if="modalData.timeline && modalData.timeline.length && modalData.timeline[0]?.pdf">
                            <a :href="modalData?.timeline[0]?.pdf?.link" target="_blank" class="bs-button as-button as-button--secondary" >
                                Atsisiųsti
                                <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5.8 9.5L9 12.7M9 12.7L12.2 9.5M9 12.7V6.3M17 9.5C17 13.9183 13.4183 17.5 9 17.5C4.58172 17.5 1 13.9183 1 9.5C1 5.08172 4.58172 1.5 9 1.5C13.4183 1.5 17 5.08172 17 9.5Z" stroke="#B75B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                        </template>                       
                    </div>
                </div>
                <div class="bs-modal-content--right as-modal-content--right">
                    <div id="as-calendar" class="as-calendar"></div>
                </div>
                <button @click="closeModal()" class="bs-button-icon">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.4 6.6L6.6 11.4M6.6 6.6L11.4 11.4M17 9C17 13.4183 13.4183 17 9 17C4.58172 17 1 13.4183 1 9C1 4.58172 4.58172 1 9 1C13.4183 1 17 4.58172 17 9Z" stroke="#222E2B" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
        
    </div>

    <?php return ob_get_clean();
}
add_shortcode('assembly_schedules', 'assembly_schedule_shortcode');
