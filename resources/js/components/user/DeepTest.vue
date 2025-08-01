<script>
import axios from "axios";
import { onMounted, ref, computed, nextTick } from "vue";
import { useToastr } from "../../toastr.js";
import { useRouter } from 'vue-router';

export default {
    setup() {
        const router = useRouter(); // Use Vue Router instance
        const toastr = useToastr();
        const records = ref([]); // Initialize as an empty array
        const currentPage = ref(1); // Track the current page
        const itemsPerPage = 5; // Max rows per page

        const query = ref('');
        const response = ref('');
        const formattedResponse = ref([]);

        const currentRow = ref(null);

        const getDeeptest = () => {
            axios
                .get("/api/_user/getDeeptest")
                .then((response) => {
                    records.value = response.data["deeptest"] || [];
                    toastr.success(response.data.message);
                })
                .catch((error) => {
                    toastr.error("Failed to retrieve records.");
                    console.error("Error:", error);
                });
        };

        const paginatedRecords = computed(() => {
            const start = (currentPage.value - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            return records.value.slice(start, end);
        });

        const totalPages = computed(() => Math.ceil(records.value.length / itemsPerPage));

        const changePage = (page) => {
            if (page > 0 && page <= totalPages.value) {
                currentPage.value = page;
            }
        };



        const previousActiveElement = ref(null);
        const toggleInert = (enable) => {
            const content = document.querySelector('.content');
            if (content) {
                if (enable) {
                    content.setAttribute('inert', 'true');
                } else {
                    content.removeAttribute('inert');
                }
            }
        };

        const showModal = (modal) => {
            previousActiveElement.value = document.activeElement; // Store the current focused element
            $(`#${modal}`).modal("show"); // Show the modal
            toggleInert(true); // Disable main content (optional, add functionality for it if needed)
            nextTick(() => document.getElementById(modal).focus()); // Focus on the modal
        };

        const hideModal = (modal) => {
            $(`#${modal}`).modal("hide"); // Hide the modal
            toggleInert(false); // Re-enable main content (optional, add functionality for it if needed)
            nextTick(() => previousActiveElement.value?.focus()); // Restore focus to the triggering element
        };

        const askme = (id) => {
            currentRow.value = id;
            console.log(`Row clicked with ID: ${id}`); // Debugging log
            showModal("modal-deeptest"); // Trigger the modal
        };

        const fromArchive = (id) => {
            currentRow.value = id;
            console.log('Navigating with ID:', currentRow.value);
            router.push({
                name: 'archive',
                params: { id: currentRow.value },
            });
        };






        const sendQuery = async () => {
            try {
                const res = await axios.post('/api/gemini', { query: query.value });
                const candidates = res.data.candidates || [];

                // Format the response for display
                formattedResponse.value = candidates.map(candidate => {
                    const contentParts = candidate.content?.parts?.map(part => part.text) || [];
                    return contentParts
                        .map(part => {
                            // Detect and format titles as bold
                            return part.replace(/^(.*?:)/gm, (match) => `</br><strong>${match}</strong></br>`);
                        })
                        .join('<br>'); // Join parts with line breaks for better readability
                });

                // Combine all formatted responses into a single string
                response.value = formattedResponse.value.join('<hr>'); // Separate candidates with a horizontal rule
            } catch (error) {
                console.error(error);
                response.value = 'Error fetching response';
                formattedResponse.value = [];
            }
        };

        const saveQuery = () => {
            axios
                .post(`/api/_user/save-query`, { 'query': query.value, 'response': response.value, 'prediction_id': currentRow.value })
                .then((response) => {
                    toastr.success(response.data.message);
                })
                .catch((error) => {
                    toastr.error("Failed to store query.");
                    console.error("Error storing query data:", error);
                });
        }

        onMounted(() => {
            getDeeptest();
        });

        return {
            records,
            paginatedRecords,
            currentPage,
            totalPages,
            changePage,
            itemsPerPage,
            askme,
            showModal,
            hideModal,
            query,
            response,
            sendQuery,
            saveQuery,
            formattedResponse,
            fromArchive,
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
                        <h3 class="card-title mb-2">Records Deeptest</h3>
                    </div>
                    <div class="card-body">
                        <!-- Make table responsive -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th class="text-center">Predicted Disease</th>
                                        <th class="text-center">Percentage</th>
                                        <th class="text-center">Updated At</th>
                                        <th class="text-center">Ask Me</th>
                                        <th class="text-center">Archive</th>
                                    </tr>
                                </thead>
                                <tbody v-if="paginatedRecords.length > 0">
                                    <tr v-for="(record, index) in paginatedRecords" :key="index">
                                        <td class="text-center">
                                            {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                                        </td>
                                        <td class="text-center">{{ record.predicted_disease }}</td>
                                        <td class="text-center">{{ record.percentage }}%</td>
                                        <td class="text-center">{{ new Date(record.updated_at).toLocaleString() }}</td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm" @click="askme(record.id)">
                                                <i class="fa-solid fa-list"></i> Suggestions
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-warning btn-sm" @click="fromArchive(record.id)">
                                                <i class="fa-solid fa-box-archive"></i> Archive
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tbody v-else>
                                    <tr>
                                        <td colspan="6" class="text-center">No user data found</td>
                                    </tr>
                                </tbody>
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

<!-- Modal -->
<div class="modal fade" id="modal-deeptest">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header flex-wrap">
                <h4 class="modal-title text-center w-100" style="color:firebrick;">Suggestions</h4>
                <button type="button" class="close" @click="hideModal('modal-deeptest')" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body text-center">
                <h5 class="text-danger">Ask Me!!!</h5>
                <textarea v-model="query" placeholder="Enter your query" class="form-control mb-3" rows="3"></textarea>

                <div v-if="response" class="mt-4 text-start">
                    <h5 class="text-secondary">Response:</h5>
                    <div class="p-3 bg-light rounded border">
                        <div v-html="response"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer justify-content-between flex-wrap">
                <button type="button" class="btn btn-danger mb-2" @click="hideModal('modal-deeptest')">Close</button>
                <button type="button" class="btn btn-primary mb-2" @click="sendQuery">Ask</button>
                <button type="button" class="btn btn-success mb-2" @click="saveQuery">Save</button>
            </div>
        </div>
    </div>
</div>

</template>
