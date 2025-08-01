<script>
import axios from "axios";
import { ref, onMounted, onUnmounted, computed, nextTick } from "vue";
import { useToastr } from "../../toastr.js";

export default {
    setup() {
        const toastr = useToastr();

        // Questions array fetched from API
        const questions = ref([]);

        // Answers object to store Yes/No answers
        const answers = ref({
            yes: [],
            no: [],
        });

        // Current index tracker
        const currentIndex = ref(0);

        // Track the previously focused element
        const previousActiveElement = ref(null);

        const disease_list = ref([]);
        const disease_percentiles = ref([]);

        const retina_image = ref(
            { image: null, imagePreview: null }
        );

        const prediction = ref({
            received: false,
            data: null,
        });


        const handleFileChange = (event) => {
            const file = event.target.files[0];
            if (file) {
                retina_image.value.image = file;
                retina_image.value.imagePreview = URL.createObjectURL(file);
            }
        };

        const retina_image_upload = () => {
            if (retina_image.value.image == null) {
                toastr.error("No image found");
                return; // Stop execution if validation fails
            }

            const formData = new FormData();
            if (retina_image.value.image instanceof File) {
                formData.append('retina_image', retina_image.value.image);
                // Image should be a File object
            }

            axios
                .post(`/api/_user/upload-retina-image`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })
                .then((response) => {
                    toastr.success(response.data.message);
                    prediction.value = {
                        received: true,
                        data: response.data.response,
                    };
                    console.log(prediction.value);
                })
                .catch((error) => {
                    toastr.error("Failed to upload Retina image.");
                    console.error("Error uploading retina image:", error);
                });
        };

        const predictionText = computed(() => {
            const labels = [
                "No Diabetic Retinopathy",
                "Mild Diabetic Retinopathy",
                "Moderate Diabetic Retinopathy",
                "Severe Diabetic Retinopathy",
                "Proliferative Diabetic Retinopathy",
            ];
            return labels[prediction.value.data.prediction] || "Unknown Prediction";
        });


        const upload_prediction_data = () => {
            axios
                .post(`/api/_user/upload-prediction-data`, { 'prediction': prediction.value.data })
                .then((response) => {
                    toastr.success(response.data.message);
                    console.log(response.data.prediction_id);
                })
                .catch((error) => {
                    toastr.error("Failed to upload prediction.");
                    console.error("Error uploading prediction data:", error);
                });
        };

        // Check if the current question is the last one
        const isLastQuestion = computed(() => currentIndex.value === questions.value.length - 1);

        // Toggle `inert` attribute for main content
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

        // Handle "Yes" answer
        const answerYes = () => {
            const currentQuestionId = questions.value[currentIndex.value].id;
            const currentQuestionDisease_id = questions.value[currentIndex.value].disease_id;
            answers.value.yes.push({ 'id': currentQuestionId, 'disease_id': currentQuestionDisease_id }); // Add to yes array
            goToNextQuestion();
        };

        // Handle "No" answer
        const answerNo = () => {
            const currentQuestionId = questions.value[currentIndex.value].id;
            const currentQuestionDisease_id = questions.value[currentIndex.value].disease_id;
            answers.value.no.push({ 'id': currentQuestionId, 'disease_id': currentQuestionDisease_id }); // Add to no array
            goToNextQuestion();
        };

        // Go to the next question or finish the test
        const goToNextQuestion = () => {
            if (!isLastQuestion.value) {
                currentIndex.value++;
            } else {
                finishTest();
            }
        };

        // Reset the question index and answers
        const resetQuestion = () => {
            currentIndex.value = 0;
            answers.value.yes = [];
            answers.value.no = [];
        };

        const highestPercentileIndex = ref(null);

        const getHighestPercentileIndex = () => {
            const val1 = disease_percentiles.value[1]; // Value for disease ID 1
            const val2 = disease_percentiles.value[2]; // Value for disease ID 2
            const val3 = disease_percentiles.value[3]; // Value for disease ID 3

            // Check if all values are equal
            if (val1 === val2 && val2 === val3) {
                highestPercentileIndex.value = -1; // All values are equal
                return;
            }

            // Find the maximum value and its index
            const maxValue = Math.max(val1, val2, val3);

            // Find the disease ID with the maximum value
            if (maxValue === val1 && maxValue === val2) {
                highestPercentileIndex.value = 1; // If val1 and val2 are max, select disease ID 1
            } else if (maxValue === val2 && maxValue === val3) {
                highestPercentileIndex.value = 2; // If val2 and val3 are max, select disease ID 2
            } else if (maxValue === val1 && maxValue === val3) {
                highestPercentileIndex.value = 1; // If val1 and val3 are max, select disease ID 1
            } else if (maxValue === val1) {
                highestPercentileIndex.value = 1; // Disease ID 1 has the max value
            } else if (maxValue === val2) {
                highestPercentileIndex.value = 2; // Disease ID 2 has the max value
            } else if (maxValue === val3) {
                highestPercentileIndex.value = 3; // Disease ID 3 has the max value
            }
        };





        // Finish the test
        const finishTest = () => {


            axios
                .post("/api/_user/textProcessing", {
                    'answers': answers.value,
                })
                .then((response) => {
                    toastr.success(response.data.message);
                    disease_list.value = response.data.disease_list;
                    disease_percentiles.value = response.data.disease_percentiles;
                    console.log(disease_list.value);
                    console.log(disease_percentiles.value);
                    getHighestPercentileIndex();
                    showModal('modal-report');
                    resetQuestion();
                })
                .catch((error) => {
                    toastr.error("Failed to retrieve questions.");
                    console.error("Error:", error);
                });

            hideModal('modal-question');
        };

        // Show modal
        const showModal = (modal) => {
            previousActiveElement.value = document.activeElement; // Store the current focused element
            $(`#${modal}`).modal('show');
            toggleInert(true); // Disable main content
            nextTick(() => document.getElementById('modal-question').focus()); // Focus the modal

        };

        // Hide modal
        const hideModal = (modal) => {
            $(`#${modal}`).modal('hide');
            toggleInert(false); // Re-enable main content
            nextTick(() => previousActiveElement.value?.focus()); // Restore focus to the triggering element
            prediction.value.received = false;
            prediction.value.data = null;
        };

        // API call to fetch questions
        const getQuestions = () => {
            axios
                .get("/api/_user/getQuestions")
                .then((response) => {
                    questions.value = response.data.questions; // Load API data into questions array
                })
                .catch((error) => {
                    toastr.error("Failed to retrieve questions.");
                    console.error("Error:", error);
                });
        };

        // Fetch questions when the component is mounted
        onMounted(() => {
            getQuestions();
            const modalElement = document.getElementById('modal-question');
            modalElement.addEventListener('hidden.bs.modal', () => toggleInert(false));
        });

        const openFileInput = (id) => {
            const input = document.getElementById(`${id}`);
            if (input) input.click();
        };

        const handleDeepTest = () => {
            hideModal("modal-report");
            showModal("modal-deeptest");
        };

        // Clean up when the component is unmounted
        onUnmounted(() => {
            toggleInert(false);
        });

        return {
            questions,
            currentIndex,
            isLastQuestion,
            answerYes,
            answerNo,
            resetQuestion,
            showModal,
            hideModal,
            disease_list,
            disease_percentiles,
            highestPercentileIndex,
            openFileInput,
            handleDeepTest,
            retina_image,
            handleFileChange,
            retina_image_upload,
            prediction,
            predictionText,
            upload_prediction_data,
        };
    },
};
</script>

<template>
    <section class="content">
        <div class="container-fluid">
            <h5 class="mb-2 text-center bg-dark text-white rounded p-3">Quick Navigator</h5>
            <div class="row">
                <div class="col-lg-3 col-6">
                    <!-- small card -->
                    <div class="small-box bg-info d-flex flex-column justify-content-between" style="height: 100%;">
                        <div class="inner">
                            <h3>QuickTest</h3>
                            <p>Easy and Fast</p>
                        </div>
                        <div class="icon">
                            <i class="fa-regular fa-eye"></i>
                        </div>
                        <!-- Button Container -->
                        <div class="d-flex align-items-center justify-content-center" style="flex-grow: 1;">
                            <button type="button" class="btn btn-white" @click="showModal('modal-question')">
                                <b>Click Here</b> <i class="fas fa-arrow-circle-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger d-flex flex-column justify-content-between" style="height: 100%;">
                        <div class="inner">
                            <h3>DeepTest</h3>

                            <p>More Reliable</p>
                        </div>
                        <div class="icon">
                            <i class="fa-solid fa-hexagon-nodes"></i>
                        </div>
                        <div class="d-flex align-items-center justify-content-center" style="flex-grow: 1;">
                            <button type="button" class="btn btn-white" @click="showModal('modal-deeptest')">
                                <b>Click Here</b> <i class="fas fa-arrow-circle-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal for Questions -->
    <div class="modal fade" id="modal-question" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalLabel">New Test</h4>
                    <button type="button" class="close" @click="hideModal('modal-question')" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" v-if="questions.length > 0">
                    <!-- Display current question -->
                    <p class="h5 mb-3">{{ questions[currentIndex].symptom }}</p>

                    <!-- Display image -->
                    <div style="height: 450px; overflow: hidden;" class="rounded border">
                        <img :src="`/${questions[currentIndex].image}`" alt="Symptom Image"
                            style="width: 100%; height: 100%; object-fit: contain; background-color: #f8f8f8;"
                            v-if="questions[currentIndex].image" />
                        <p v-else>No image available</p>
                    </div>
                </div>

                <!-- Footer with Yes/No buttons -->
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" @click="answerNo">
                        No
                    </button>
                    <button type="button" class="btn btn-success" @click="answerYes">
                        Yes
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-report">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Result Analysis</h4>
                    <div v-if="highestPercentileIndex == 1" class="callout callout-success">
                        <h5 style="color:firebrick;">You can take treatment from us!!</h5>
                    </div>
                    <button type="button" class="close" @click="hideModal('modal-report')" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h5 class="text-dark">Disease Analysis:</h5>
                    <ul class="list-group">
                        <li v-for="(disease, index) in disease_list" :key="disease.id" class="list-group-item">
                            <strong :style="{ color: highestPercentileIndex == disease.id ? 'red' : 'inherit' }">
                                {{ disease.disease_name }}
                            </strong><br />
                            <span
                                :style="{ color: highestPercentileIndex == disease.id ? 'red' : 'inherit' }">Percentile:
                                {{ disease_percentiles[index + 1].toFixed(2) }}</span>
                        </li>
                    </ul>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" @click="hideModal('modal-report')">Close</button>
                    <button v-if="highestPercentileIndex == 1" type="button" class="btn btn-primary"
                        @click="handleDeepTest">Approach Next
                        ?</button>
                    <button v-else type="button" class="btn btn-primary">Suggestions for you</button>

                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>


    <div class="modal fade" id="modal-deeptest">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" style="color:green;">DeepTest!</h4>
                    <button type="button" class="close" @click="hideModal('modal-deeptest')" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body text-center" id="image_box" v-if="!prediction.received">
                    <h5 class="" style="color:red;">Efficient and Trustable</h5>

                    <input type="file" class="d-none" id="retina_image_id" @change="handleFileChange($event)" />
                    <img @click="openFileInput('retina_image_id')" class="profile-user-img img-fluid rounded mt-2"
                        style="width: 320px; height: 320px; cursor: pointer;"
                        :src="retina_image.imagePreview || '/dashboard/dist/img/user4-128x128.jpg'"
                        alt="'Retina Image Preview'" />
                </div>
                <div class="modal-body text-center" id="result_box" v-if="prediction.received">
                    <h1 class="text-success">Prediction Results</h1>
                    <h2 style="color: firebrick;">Prediction: {{ predictionText }}</h2>

                </div>


                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger" @click="hideModal('modal-deeptest')">Close</button>
                    <button type="button" class="btn btn-primary" v-if="!prediction.received"
                        @click="retina_image_upload">Next</button>
                    <button type="button" class="btn btn-success" v-if="prediction.received"
                        @click="upload_prediction_data">Suggestions?</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>


</template>
<style scoped>
.profile-user-img:hover {
    background-color: rgb(218, 1, 102);
    cursor: pointer;
}
</style>