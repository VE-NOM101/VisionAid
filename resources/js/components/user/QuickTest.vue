<script>
import axios from "axios";
import { onMounted, ref, computed } from "vue";
import { useToastr } from "../../toastr.js";

export default {
    setup() {
        const records = ref([]); // Initialize as an empty array
        const disease_list = ref([]);
        const toastr = useToastr();

        // Pagination state
        const currentPage = ref(1);
        const itemsPerPage = 5; // Items per page

        const getQuicktest = () => {
            axios
                .get("/api/_user/getQuicktest")
                .then((response) => {
                    records.value = response.data["disease_percentiles"] || [];
                    disease_list.value = response.data["disease_list"] || [];
                })
                .catch((error) => {
                    toastr.error("Failed to retrieve records.");
                    console.error("Error:", error);
                });
        };

        // Computed property for paginated records
        const paginatedRecords = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage;
            return records.value.slice(start, start + itemsPerPage);
        });

        // Total pages
        const totalPages = computed(() =>
            Math.ceil(records.value.length / itemsPerPage)
        );

        const changePage = (page) => {
            if (page >= 1 && page <= totalPages.value) {
                currentPage.value = page;
            }
        };

        // Format the date in 12-hour system
        const formatDate = (isoString) => {
            if (!isoString) return "N/A";
            const date = new Date(isoString);

            // Format date parts
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");

            // Format time parts
            let hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, "0");
            const seconds = String(date.getSeconds()).padStart(2, "0");
            const period = hours >= 12 ? "PM" : "AM";

            // Convert to 12-hour format
            hours = hours % 12 || 12; // Convert "0" hour to "12"

            // Return formatted date-time string
            return `${hours}:${minutes}:${seconds} ${period} || ${year}-${month}-${day}`;
        };

        onMounted(() => {
            getQuicktest();
        });

        return {
            records,
            disease_list,
            currentPage,
            itemsPerPage,
            paginatedRecords,
            totalPages,
            changePage,
            formatDate,
        };
    },
};
</script>

<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                            <h3 class="card-title mb-2">Records</h3>
                        </div>
                        <div class="card-body">
                            <!-- Responsive table wrapper -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th v-for="(disease, index) in disease_list" :key="index">
                                                {{ disease.disease_name }}
                                            </th>
                                            <th class="text-center">Time</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="paginatedRecords.length > 0">
                                        <tr v-for="(record, index) in paginatedRecords" :key="index">
                                            <td class="text-center">
                                                {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                                            </td>
                                            <td class="text-center">{{ record.percentage_disease_1 }}%</td>
                                            <td class="text-center">{{ record.percentage_disease_2 }}%</td>
                                            <td class="text-center">{{ record.percentage_disease_3 }}%</td>
                                            <td class="text-center">{{ formatDate(record.updated_at) }}</td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td :colspan="disease_list.length + 2" class="text-center">
                                                No user data found
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="thead-dark">
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th v-for="(disease, index) in disease_list" :key="index">
                                                {{ disease.disease_name }}
                                            </th>
                                            <th class="text-center">Time</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <!-- Pagination Controls -->
                            <div class="d-flex justify-content-center align-items-center flex-wrap mt-3">
                                <button class="btn btn-dark mx-2 mb-2" @click="changePage(currentPage - 1)"
                                    :disabled="currentPage === 1">
                                    <i class="fas fa-arrow-left"></i>
                                </button>
                                <span class="mx-2 mb-2">Page {{ currentPage }} of {{ totalPages }}</span>
                                <button class="btn btn-dark mx-2 mb-2" @click="changePage(currentPage + 1)"
                                    :disabled="currentPage === totalPages">
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</template>
