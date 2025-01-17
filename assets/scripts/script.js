document.addEventListener("alpine:init", () => {
    Alpine.data("busSchedules", () => ({
        routeType: document
            .getElementById("bus-schedule")
            .getAttribute("data-type"),
        map: null,
        stops: [],
        routes: [],
        schedules: [],
        startObj: {},
        endObj: {},
        filteredRoutes: [],
        notFoundMessage: "",
        startQuery: "",
        endQuery: "",
        selectedDate: "",
        selectedTime: "",
        datePicker: null,
        timePicker: null,
        showStartDropdown: false,
        showEndDropdown: false,
        isModalOpen: false,
        modalData: {},
        openModal(route) {
            this.modalData = route;
            this.isModalOpen = true;
            this.initMap(route);
        },

        closeModal() {
            this.isModalOpen = false;
            this.modalData = {};
            this.map = null;
        },

        initBusSchedules() {
            const now = new Date();
            const currentHour = now.getHours();
            const currentMinute = now.getMinutes();
            const defaultTime = `${String(currentHour).padStart(
                2,
                "0"
            )}:${String(currentMinute).padStart(2, "0")}`;

            this.selectedDate = now;
            this.selectedTime = defaultTime;
            fetch(
                localizedData.scheduleAjax.ajax_url +
                    "?action=bs_get_schedules_data"
            )
                .then((response) => response.json())
                .then((data) => {
                    this.stops = data.stops;
                    this.routes = data.routes;
                })
                .catch((error) =>
                    console.error("Failed to fetch data:", error)
                );

            this.datePicker = flatpickr(this.$refs.datePicker, {
                dateFormat: "Y-m-d",
                defaultDate: now,
                minDate: "today",
                locale: "lt",
                disableMobile: "true",
                onChange: (date, dateString) => {
                    this.selectedDate = dateString;
                },
            });

            this.timePicker = flatpickr(this.$refs.timePicker, {
                enableTime: true,
                noCalendar: true,
                dateFormat: "H:i",
                defaultDate: defaultTime,
                time_24hr: true,
                inline: true,

                onChange: (time, timeString) => {
                    this.selectedTime = timeString;
                },
            });
        },

        findRoutesBetweenStops(routes, startID, endID) {
            if (!startID || !endID) {
                console.warn("Pradžios ar pabaigos ID nėra pasirinktas.");
                return;
            }

            // Map for translating driving days
            const daysMapping = {
                Pirmadienis: 1,
                Antradienis: 2,
                Trečiadienis: 3,
                Ketvirtadienis: 4,
                Penktadienis: 5,
                Šeštadienis: 6,
                Sekmadienis: 0,
                "Darbo dienos": [1, 2, 3, 4, 5],
                Savaitgalis: [6, 0],
            };

            const selectedDay = new Date(this.selectedDate).getDay();

            // Filtruoti maršrutus pagal pradžios ir pabaigos stoteles
            this.filteredRoutes = routes.filter((route) => {
                // Patikrinti, ar maršrute yra stotelės su startID ir endID
                return route.schedule.some((schedule) => {
                    const startIndex = schedule.stops.findIndex(
                        (stop) => Number(stop.stopID) === Number(startID)
                    );
                    const endIndex = schedule.stops.findIndex(
                        (stop) => Number(stop.stopID) === Number(endID)
                    );

                    if (
                        startIndex === -1 ||
                        endIndex === -1 ||
                        startIndex >= endIndex
                    ) {
                        return false;
                    }

                    return schedule.schedule_timeline.some((day) => {
                        const mappedDay = daysMapping[day];
                        if (Array.isArray(mappedDay)) {
                            return mappedDay.includes(selectedDay);
                        }
                        return mappedDay === selectedDay;
                    });
                });
            });
        },

        computedRoutes() {
            let result = this.filteredRoutes.flatMap((route) => {
                // Check if the route matches the type passed via the shortcode
                let isCityRoute =
                    this.routeType === "city_routes" &&
                    route.type.includes("Miesto maršrutas");

                let isIntercityRoute =
                    this.routeType === "intercity_routes" &&
                    route.type.includes("Užmiesčio maršrutas");

                // Default to showing all routes if no specific type filter is applied
                if (this.routeType && !isCityRoute && !isIntercityRoute) {
                    return []; // Skip this route if it doesn't match the type filter
                }

                return route.schedule
                    .filter(
                        (schedule) =>
                            schedule.start.stopTime >= this.selectedTime
                    ) // Filter by selectedTime
                    .map((schedule) => ({
                        id: route.id,
                        title: route.title,
                        type: route.type,
                        direction: route.direction,
                        start: schedule.start,
                        end: schedule.end,
                        stops: schedule.stops,
                        duration: this.calculateDuration(
                            schedule.start.stopTime,
                            schedule.end.stopTime
                        ),
                        time: `${schedule.start.stopTime} - ${schedule.end.stopTime}`,
                    }));
            });

            if (
                this.startObj.id &&
                this.endObj.id &&
                this.selectedDate &&
                this.selectedTime &&
                result.length === 0
            ) {
                this.notFoundMessage =
                    "Nėra maršrutų, atitinkančių pasirinktus kriterijus.";
            } else {
                this.notFoundMessage = "";
            }
            return result;
        },

        calculateDuration(startTime, endTime) {
            // Utility to calculate duration in hours or minutes
            const [startHour, startMin] = startTime.split(":").map(Number);
            const [endHour, endMin] = endTime.split(":").map(Number);

            // Calculate total minutes
            const totalMinutes =
                endHour * 60 + endMin - (startHour * 60 + startMin);

            // Convert minutes to hours and remaining minutes
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;

            // Format the result
            return `${hours > 0 ? hours + "val." : ""} ${
                minutes > 0 ? minutes + "min." : ""
            }`;
        },
        normalizeString(str) {
            return str
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLowerCase();
        },

        get filteredStart() {
            return this.startQuery === ""
                ? this.stops
                : this.stops.filter((stop) => {
                      return this.normalizeString(stop.name).includes(
                          this.normalizeString(this.startQuery)
                      );
                  });
        },

        get filteredEnd() {
            return this.endQuery === ""
                ? this.stops
                : this.stops.filter((stop) => {
                      return this.normalizeString(stop.name).includes(
                          this.normalizeString(this.endQuery)
                      );
                  });
        },

        selectStart(stop) {
            this.startObj = stop;
            this.startQuery = stop.name;
            this.showStartDropdown = false;
        },

        selectEnd(stop) {
            this.endObj = stop;
            this.endQuery = stop.name;
            this.showEndDropdown = false;
        },

        hideDropdown(type) {
            setTimeout(() => {
                if (type === "start") this.showStartDropdown = false;
                if (type === "end") this.showEndDropdown = false;
            }, 200); // Delay for click to register
        },
        initMap(route) {
            const initialLat = parseFloat(route.stops[0]?.stopLat);
            const initialLng = parseFloat(route.stops[0]?.stopLang);

            if (isNaN(initialLat) || isNaN(initialLng)) {
                console.error("Initial coordinates are invalid.");
                return; // Exit the function if the initial coordinates are not valid
            }

            this.map = L.map("bs-routeMap").setView(
                [initialLat, initialLng],
                13
            );

            L.tileLayer(
                "https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png",
                {
                    maxZoom: 14,
                }
            ).addTo(this.map);

            const routeCoordinates = route.stops
                .filter(
                    (stop) =>
                        !isNaN(parseFloat(stop?.stopLat)) &&
                        !isNaN(parseFloat(stop?.stopLang))
                ) // Ensure valid coordinates
                .map((stop) => [
                    parseFloat(stop.stopLat),
                    parseFloat(stop.stopLang),
                ]);

            route.stops.forEach((stop) => {
                const lat = parseFloat(stop?.stopLat);
                const lng = parseFloat(stop?.stopLang);

                if (!isNaN(lat) && !isNaN(lng)) {
                    L.marker([lat, lng])
                        .addTo(this.map)
                        .bindPopup(
                            `<strong>${stop.stopName}</strong><br>Laikas: ${stop.stopTime}`
                        );
                }
            });

            if (routeCoordinates.length === 1) {
                this.map.setView(routeCoordinates[0], 13); // Default zoom level
            } else {
                const bounds = L.latLngBounds(routeCoordinates).pad(0.1);
                this.map.fitBounds(bounds, { padding: [50, 50] });
            }

            setTimeout(() => {
                this.map.invalidateSize();
            }, 200);
        },
    }));
});

document.addEventListener("alpine:init", () => {
    Alpine.data("assemblySchedules", () => ({
        assemblies: [],
        elderships: [],
        streets: [],
        schedules: [],
        eldershipObj: {},
        streetObj: {},
        categories: [],
        selectedCategory: "",
        filteredEldership: [],
        filteredAssemblies: [],
        eldershipQuery: "",
        streetQuery: "",
        showEldershipDropdown: false,
        showStreetDropdown: false,
        isModalOpen: false,
        modalData: {},
        formattedDates: [],

        openModal(assemblySchedule) {
            if (!assemblySchedule || !assemblySchedule.timeline) {
                console.error(
                    "Invalid data passed to openModal:",
                    assemblySchedule
                );
                return;
            }

            // Store the modal data for use in the modal template
            this.modalData = assemblySchedule;

            this.formattedDates = [];

            this.formattedDates = assemblySchedule.timeline.flatMap(
                (timelineItem) =>
                    (timelineItem.schedules || []).flatMap((schedules) => {
                        const monthMapping = {
                            Sausis: "01",
                            Vasaris: "02",
                            Kovas: "03",
                            Balandis: "04",
                            Gegužė: "05",
                            Birželis: "06",
                            Liepa: "07",
                            Rugpjūtis: "08",
                            Rugsėjis: "09",
                            Spalis: "10",
                            Lapkritis: "11",
                            Gruodis: "12",
                        };

                        const month = monthMapping[schedules.month];
                        if (!month) return [];

                        return schedules.days.map(
                            (day) =>
                                `${new Date().getFullYear()}-${month}-${String(
                                    day
                                ).padStart(2, "0")}`
                        );
                    })
            );

            // Open the modal
            this.isModalOpen = true;

            this.$nextTick(() => {
                this.initializeFlatpickr();
            });
        },

        initializeFlatpickr() {
            // Ensure Flatpickr is initialized only when modal is open
            if (!this.isModalOpen || !this.formattedDates.length) return;

            // Destroy any existing instance (if applicable)
            if (this.flatpickrInstance) {
                this.flatpickrInstance.destroy();
            }

            // Initialize Flatpickr
            this.flatpickrInstance = flatpickr("#as-calendar", {
                inline: true,
                dateFormat: "Y-m-d",
                defaultDate: this.formattedDates,
                locale: "lt",
            });
        },

        closeModal() {
            this.isModalOpen = false;
            this.modalData = null;
            this.flatpickrInstance.destroy();
        },
        closeModal() {
            this.isModalOpen = false;
            this.modalData = {};
        },

        initAssemblySchedules() {
            fetch(
                localizedData.assemblyAjax.ajax_url +
                    "?action=bs_get_assembly_data"
            )
                .then((response) => response.json())
                .then((data) => {
                    this.assemblies = data.assembly;
                    this.elderships = data.eldership;
                    this.streets = data.streets;
                    this.categories = data.categories;
                })
                .catch((error) =>
                    console.error("Failed to fetch data:", error)
                );
        },

        findAssemblySchedule() {
            // Filter assemblies based on eldership and optional criteria
            this.filteredAssemblies = this.assemblies
                .map((assembly) => {
                    const filteredTimeline = assembly.timeline.filter(
                        (timelineItem) => {
                            const matchesEldership =
                                this.eldershipObj?.id ===
                                timelineItem.eldership_id;

                            const matchesStreet =
                                !this.streetObj?.id || // If no street is selected, match all
                                timelineItem.schedule.some((scheduleItem) => {
                                    return (
                                        Array.isArray(scheduleItem.street_id) &&
                                        scheduleItem.street_id.includes(
                                            this.streetObj?.id
                                        )
                                    );
                                });

                            return matchesEldership && matchesStreet;
                        }
                    );

                    // Return the assembly only if there is a matching timeline
                    return filteredTimeline.length > 0
                        ? { ...assembly, timeline: filteredTimeline }
                        : null;
                })
                .filter(Boolean); // Remove null entries
        },

        computedAssemblies() {
            // Map the filtered assemblies into a single aggregated object
            const result = this.filteredAssemblies.map((assembly) => {
                // Aggregate all timeline data into one item
                const timelineData = assembly.timeline.map((timelineItem) => {
                    // Process schedules data
                    const schedules = timelineItem.schedule.map((schedule) => ({
                        month: schedule.month,
                        days: schedule.days.map((day) => parseInt(day, 10)), // Convert days to numbers
                        streetID: schedule.street_id,
                    }));

                    // Filter schedules by streetID if applicable
                    const filteredSchedules = this.streetObj?.id
                        ? schedules.filter(
                              (sched) =>
                                  sched.streetID &&
                                  sched.streetID.includes(this.streetObj.id)
                          )
                        : schedules;

                    // Return full timeline item if no filtering is applied
                    return {
                        eldershipID: timelineItem.eldership_id,
                        eldershipTitle: timelineItem.eldership_title,
                        schedules: filteredSchedules, // Use filtered or unfiltered schedules
                        pdf: timelineItem.pdf,
                    };
                });

                // Return the aggregated assembly data
                return {
                    assemblyID: assembly.id,
                    assemblyTitle: assembly.title,
                    type: assembly.type,
                    timeline: timelineData, // Contains aggregated schedule data
                };
            });

            // Filter the result based on the selected category
            if (this.selectedCategory) {
                return result.filter((assembly) =>
                    assembly.type.includes(this.selectedCategory)
                );
            }

            // Return the first assembly or an empty object if none are filtered
            return result;
        },

        normalizeString(str) {
            return str
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLowerCase();
        },

        get filteredEldership() {
            return this.eldershipQuery === ""
                ? this.elderships
                : this.elderships.filter((eldership) => {
                      return this.normalizeString(eldership.title).includes(
                          this.normalizeString(this.eldershipQuery)
                      );
                  });
        },

        get filteredStreet() {
            return this.streetQuery === ""
                ? this.streets
                : this.streets.filter((street) => {
                      return this.normalizeString(street.title).includes(
                          this.normalizeString(this.streetQuery)
                      );
                  });
        },

        selectEldership(eldership) {
            this.eldershipObj = eldership;
            this.eldershipQuery = eldership.title;
            this.showEldershipDropdown = false;
        },

        selectStreet(street) {
            this.streetObj = street;
            this.streetQuery = street.title;
            this.showStreetDropdown = false;
        },

        hideDropdown(type) {
            setTimeout(() => {
                if (type === "eldership") this.showEldershipDropdown = false;
                if (type === "street") this.showStreetDropdown = false;
            }, 200); // Delay for click to register
        },
    }));
});
