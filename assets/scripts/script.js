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
            console.log("route", route);
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
            fetch(autobusaiAjax.ajax_url + "?action=bs_get_schedules_data")
                .then((response) => response.json())
                .then((data) => {
                    this.stops = data.stops;
                    this.routes = data.routes;
                })
                .then(() => console.log(this.routes))
                .catch((error) =>
                    console.error("Failed to fetch data:", error)
                );

            this.datePicker = flatpickr(this.$refs.datePicker, {
                dateFormat: "Y-m-d",
                defaultDate: now,
                minDate: "today",
                locale: "lt",
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
            console.log("filter", routes);
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
                console.log("Tikrinamas maršrutas:", route);

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

            console.log("Atfiltruoti maršrutai:", this.filteredRoutes);
        },

        computedRoutes() {
            let result = this.filteredRoutes.flatMap((route) => {
                // Check if the route matches the type passed via the shortcode
                let isCityRoute =
                    this.routeType === "city_routes" &&
                    route.type.includes("Miesto maršrutas");

                console.log("isCityRoute", isCityRoute);
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

            console.log(this.endObj);
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
                            `<strong>${stop.stopName}</strong><br>Time: ${stop.stopTime}`
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
