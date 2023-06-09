<template>
    <AppLayout title="On en parle | Émissions (Liste)">
        <div class="programs-header">
            <h1 id="programs-title">Émissions</h1>
            <dropdownFilter
                :categories="categories"
                @filter-applied="handleFilterApplied"
            ></dropdownFilter>
        </div>
        <div class="programs-wrapper">
            <div
                class="row-program"
                v-for="program in filteredPrograms"
                :key="program.id"
            >
                <AdminProgram class="item-program" :program="program" />
            </div>
        </div>
    </AppLayout>
</template>

<script>
/**
 *
 * Component: AppLayout:
 * Description: Layout component for the admin section.
 * Component: AdminProgram:
 * Description: A reusable component for displaying an admin program item.
 * Component: dropdownFilter:
 * Description: A reusable dropdown component for selecting filter options.
 */

import AppLayout from "@/Layouts/AppLayoutAdmin.vue";
import AdminProgram from "@/Pages/MyComponents/admin-program.vue";
import axios from "axios";
import dropdownFilter from "@/Pages/MyComponents/dropdownFilter.vue";

export default {
    components: {
        AppLayout,
        AdminProgram,
        dropdownFilter,
    },
    data() {
        return {
            programs: [],
            filteredPrograms: [],
            filterBy: "",
            isSelectOpen: false,
            categories: [
                {
                    name: "Date de diffusion (décroissante)",
                    value: "date",
                },
                {
                    name: "Nombre de messages",
                    value: "interactions",
                },
            ],
        };
    },
    methods: {
        /**
         * Handles the filter applied event.
         * @param {string} filterData - The filter data to apply.
         */
        handleFilterApplied(filterData) {
            this.filterBy = filterData;
        },
        /**
         * Toggles the select dropdown.
         */
        toggleSelect() {
            this.isSelectOpen = !this.isSelectOpen;
        },
        /**
         * Handles the filter change event.
         * @param {Event} event - The filter change event.
         */
        handleFilterChange(event) {
            this.filterBy = event.target.value;
        },
        /**
         * Filters the programs based on the selected filter option.
         */
        filterPrograms() {
            if (this.filterBy === "interactions") {
                this.filteredPrograms = [...this.programs].sort(
                    (a, b) => b.messages_count - a.messages_count
                );
            } else if (this.filterBy === "date") {
                this.filteredPrograms = [...this.programs];
            } else {
                this.filteredPrograms = [...this.programs];
            }
        },
        /**
         * Retrieves the list of chat rooms from the server.
         */
        getRooms() {
            axios.get("/chat/rooms-list").then((response) => {
                this.programs = response.data;
                this.filteredPrograms = [...this.programs];
            });
        },
    },
    watch: {
        filterBy() {
            this.filterPrograms();
        },
    },
    created() {
        this.getRooms();

        const chatChannel = Echo.channel("rooms.update");
        chatChannel.listen(".event.on.rooms", (e) => {
            this.getRooms();
        });
    },
};
</script>
