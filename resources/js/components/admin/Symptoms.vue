<script>
import axios from "axios";
import { ref, onMounted, onUpdated } from "vue";
import { useToastr } from "../../toastr.js"; // Assuming you have Toastr set up for notifications

export default {
    setup() {
        const diseases = ref([
            { disease_name: "", id: 1, no_of_symptoms: 0 },
            { disease_name: "", id: 2, no_of_symptoms: 0 },
            { disease_name: "", id: 3, no_of_symptoms: 0 },
        ]); // Define `diseases` as a reactive reference
        const toastr = useToastr();

        const symptoms_1 = ref([
            { id: 1, sym: "", image: null, imagePreview: null },
            { id: 2, sym: "", image: null, imagePreview: null },
            { id: 3, sym: "", image: null, imagePreview: null },
            { id: 4, sym: "", image: null, imagePreview: null },
            { id: 5, sym: "", image: null, imagePreview: null },
        ]);
        const symptoms_2 = ref([
            { id: 6, sym: "", image: null, imagePreview: null },
            { id: 7, sym: "", image: null, imagePreview: null },
            { id: 8, sym: "", image: null, imagePreview: null },
            { id: 9, sym: "", image: null, imagePreview: null },
            { id: 10, sym: "", image: null, imagePreview: null },
        ]);
        const symptoms_3 = ref([
            { id: 11, sym: "", image: null, imagePreview: null },
            { id: 12, sym: "", image: null, imagePreview: null },
            { id: 13, sym: "", image: null, imagePreview: null },
            { id: 14, sym: "", image: null, imagePreview: null },
            { id: 15, sym: "", image: null, imagePreview: null },
        ]);

        // Open File Input for a Specific Index
        const openFileInput = (index, id) => {
            const input = document.getElementById(`image${id}${index + 1}`);
            if (input) input.click();
        };

        // Handle File Change
        const handleFileChange = (index, event, id) => {
            const file = event.target.files[0];
            if (file) {
                if (id === 1) {
                    // Assign the file to the respective symptom's image
                    symptoms_1.value[index].image = file;

                    // Create a temporary URL for preview
                    symptoms_1.value[index].imagePreview = URL.createObjectURL(file);
                } else if (id === 2) {
                    // Assign the file to the respective symptom's image
                    symptoms_2.value[index].image = file;

                    // Create a temporary URL for preview
                    symptoms_2.value[index].imagePreview = URL.createObjectURL(file);
                } else if (id === 3) {
                    // Assign the file to the respective symptom's image
                    symptoms_3.value[index].image = file;

                    // Create a temporary URL for preview
                    symptoms_3.value[index].imagePreview = URL.createObjectURL(file);
                }
            }
        };

        const symptoms_upload = (id) => {
            let isValid = null;
            let data = null;

            if (id === 1) {
                isValid = symptoms_1.value.every(
                    (symptom) => symptom.sym.trim() !== "" && symptom.image !== null
                );
                data = symptoms_1.value;
            } else if (id === 2) {
                isValid = symptoms_2.value.every(
                    (symptom) => symptom.sym.trim() !== "" && symptom.image !== null
                );
                data = symptoms_2.value;
            } else if (id === 3) {
                isValid = symptoms_3.value.every(
                    (symptom) => symptom.sym.trim() !== "" && symptom.image !== null
                );
                data = symptoms_3.value;
            }

            if (!isValid) {
                toastr.error("Please fill out all fields (symptoms and images) before submitting.");
                return; // Stop execution if validation fails
            }

            const formData = new FormData();

            // Append each symptom to the formData
            data.forEach((symptom, index) => {
                formData.append(`symptoms[${index}][id]`, symptom.id);
                formData.append(`symptoms[${index}][sym]`, symptom.sym);
                if (symptom.image instanceof File) {
                    formData.append(`symptoms[${index}][image]`, symptom.image);
                    // Image should be a File object
                } else {
                    formData.append(`symptoms[${index}][image]`, null);
                }
            });

            axios
                .post(`/api/_admin/upload_symptom/${id}`, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                })
                .then((response) => {
                    toastr.success("Symptoms uploaded successfully.");
                    fetchSymptoms(id);
                })
                .catch((error) => {
                    toastr.error("Failed to upload symptoms.");
                    console.error("Error uploading symptoms:", error);
                });
        };

        const fetchSymptoms = (diseaseId) => {
            axios
                .get(`/api/_admin/get_symptoms/${diseaseId}`)
                .then((response) => {
                    // Update symptoms_1 with data from response
                    const data = response.data; // Assuming data is an array
                    if (diseaseId === 1) {
                        symptoms_1.value = data.map((item, index) => ({
                            id: item.id, // Retain or reset IDs
                            sym: item.symptom || "", // Assuming 'sym' is a key in response items

                            imagePreview: item.image ? `/${item.image}` : null, // Adjust the URL based on your storage path
                        }));
                    } else if (diseaseId === 2) {
                        symptoms_2.value = data.map((item, index) => ({
                            id: item.id, // Retain or reset IDs
                            sym: item.symptom || "", // Assuming 'sym' is a key in response items

                            imagePreview: item.image ? `/${item.image}` : null, // Adjust the URL based on your storage path
                        }));
                    } else if (diseaseId === 3) {
                        symptoms_3.value = data.map((item, index) => ({
                            id: item.id, // Retain or reset IDs
                            sym: item.symptom || "", // Assuming 'sym' is a key in response items

                            imagePreview: item.image ? `/${item.image}` : null, // Adjust the URL based on your storage path
                        }));
                    }

                })
                .catch((error) => {
                    console.error("Error fetching symptoms:", error);
                    toastr.error("Failed to fetch symptoms.");
                });
        };




        // Fetch diseases when the component is mounted
        const getDisease = () => {
            axios
                .get("/api/_admin/getDisease")
                .then((response) => {
                    diseases.value = response.data; // Update the `value` of the ref

                })
                .catch((error) => {
                    console.error("Failed to fetch diseases:", error);
                });
        };

        const handleSubmit = () => {
            // Validate that no field is empty
            if (
                !diseases.value[0].disease_name ||
                !diseases.value[1].disease_name ||
                !diseases.value[2].disease_name ||
                !diseases.value[0].no_of_symptoms ||
                !diseases.value[1].no_of_symptoms ||
                !diseases.value[2].no_of_symptoms
            ) {
                toastr.error("Please fill in all fields.");
                return; // Prevent submission if validation fails
            }

            // API call to update data in the database
            axios
                .post('/api/_admin/updateDiseases', {
                    disease1: diseases.value[0].disease_name,
                    disease2: diseases.value[1].disease_name,
                    disease3: diseases.value[2].disease_name,
                    no_of_symptoms: parseInt(diseases.value[0].no_of_symptoms),
                })
                .then((response) => {
                    toastr.success(response.data.message);
                    getDisease();
                })
                .catch((error) => {
                    toastr.error("Failed to update diseases.");
                    console.error("Error:", error);
                });
        };

        // Fetch diseases when the component is mounted
        onMounted(() => {
            getDisease();
            fetchSymptoms(1);
            fetchSymptoms(2);
            fetchSymptoms(3);
        });

        return {
            diseases,
            handleSubmit,
            openFileInput,
            handleFileChange,
            symptoms_1,
            symptoms_2,
            symptoms_3,
            symptoms_upload,
        };
    },
};
</script>




<template>
    <div class="container">
        <div class="row d-flex align-items-center justify-content-center">
            <div class="col-10 col-sm-12">
                <div class="row">
                    <div class="col-12 col-sm-12">
                        <div class="card card-primary card-outline card-outline-tabs">
                            <div class="card-header p-0 border-bottom-0">
                                <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link active" id="custom-tabs-four-home-tab" data-toggle="pill"
                                            href="#custom-tabs-four-home" role="tab"
                                            aria-controls="custom-tabs-four-home" aria-selected="true">Home</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-four-profile-tab" data-toggle="pill"
                                            href="#custom-tabs-four-profile" role="tab"
                                            aria-controls="custom-tabs-four-profile" aria-selected="false">Disease 1</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-four-messages-tab" data-toggle="pill"
                                            href="#custom-tabs-four-messages" role="tab"
                                            aria-controls="custom-tabs-four-messages" aria-selected="false">Disease
                                            2</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="custom-tabs-four-settings-tab" data-toggle="pill"
                                            href="#custom-tabs-four-settings" role="tab"
                                            aria-controls="custom-tabs-four-settings" aria-selected="false">Disease
                                            3</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="custom-tabs-four-tabContent">
                                    <div class="tab-pane fade show active" id="custom-tabs-four-home" role="tabpanel"
                                        aria-labelledby="custom-tabs-four-home-tab">
                                        <div class="card-body">
                                            <div class="tab-content" id="custom-tabs-four-tabContent">
                                                <div class="tab-pane fade show active" id="custom-tabs-four-home"
                                                    role="tabpanel" aria-labelledby="custom-tabs-four-home-tab">
                                                    <div class="card card-primary">
                                                        <!-- Form start -->
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="form-group col-sm-4">
                                                                    <label for="firstDisease">First Disease
                                                                        Name:</label>
                                                                    <input v-model="diseases[0].disease_name"
                                                                        type="text" class="form-control"
                                                                        id="firstDisease"
                                                                        placeholder="Enter first disease name">
                                                                </div>
                                                                <div class="form-group col-sm-4">
                                                                    <label for="secondDisease">Second Disease
                                                                        Name:</label>
                                                                    <input v-model="diseases[1].disease_name"
                                                                        type="text" class="form-control"
                                                                        id="secondDisease"
                                                                        placeholder="Enter second disease name">
                                                                </div>
                                                                <div class="form-group col-sm-4">
                                                                    <label for="thirdDisease">Third Disease
                                                                        Name:</label>
                                                                    <input v-model="diseases[2].disease_name"
                                                                        type="text" class="form-control"
                                                                        id="thirdDisease"
                                                                        placeholder="Enter third disease name">
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-sm-3"></div>
                                                                <div class="form-group col-sm-6">
                                                                    <label for="">No of Symptoms per disease:</label>
                                                                    <input disabled v-model="diseases[0].no_of_symptoms"
                                                                        type="number" class="form-control"
                                                                        id="symptomNumber"
                                                                        placeholder="Enter no of Symptoms per disease">
                                                                </div>
                                                                <div class="col-sm-3"></div>
                                                            </div>
                                                        </div>
                                                        <div class="card-footer">
                                                            <button @click="handleSubmit" type="submit"
                                                                class="btn btn-primary">Submit</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Additional Tabs -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="tab-pane fade" id="custom-tabs-four-profile" role="tabpanel"
                                        aria-labelledby="custom-tabs-four-profile-tab">
                                        <div class="card card-primary">
                                            <div class="card-body">
                                                <!-- Rows for Symptoms -->
                                                <div v-for="(symptom, index) in symptoms_1" :key="symptom.id"
                                                    class="row mt-3">
                                                    <div class="form-group col-sm-7">
                                                        <label :for="`symptom1${index + 1}`">Symptom {{ index + 1
                                                            }}</label>
                                                        <input v-model="symptom.sym" type="text" class="form-control"
                                                            :id="`symptom1${index + 1}`"
                                                            :placeholder="`Enter Symptom ${index + 1}`" />
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                    <div class="form-group col-sm-4">
                                                        <label :for="`image1${index + 1}`">Image</label>
                                                        <div>
                                                            <input type="file" class="d-none" :id="`image1${index + 1}`"
                                                                @change="handleFileChange(index, $event, 1)" />
                                                            <img @click="openFileInput(index, 1)"
                                                                class="profile-user-img img-fluid rounded mt-2"
                                                                style="width: 200px; height: 128px; cursor: pointer;"
                                                                :src="symptom.imagePreview || '/dashboard/dist/img/user4-128x128.jpg'"
                                                                :alt="`Symptom ${index + 1} Image`" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="card-footer">
                                                <button @click="symptoms_upload(1)" type="submit"
                                                    class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-four-messages" role="tabpanel"
                                        aria-labelledby="custom-tabs-four-messages-tab">
                                        <div class="card card-primary">
                                            <div class="card-body">
                                                <!-- Rows for Symptoms -->
                                                <div v-for="(symptom, index) in symptoms_2" :key="symptom.id"
                                                    class="row mt-3">
                                                    <div class="form-group col-sm-7">
                                                        <label :for="`symptom2${index + 1}`">Symptom {{ index + 1
                                                            }}</label>
                                                        <input v-model="symptom.sym" type="text" class="form-control"
                                                            :id="`symptom2${index + 1}`"
                                                            :placeholder="`Enter Symptom ${index + 1}`" />
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                    <div class="form-group col-sm-4">
                                                        <label :for="`image2${index + 1}`">Image</label>
                                                        <div>
                                                            <input type="file" class="d-none" :id="`image2${index + 1}`"
                                                                @change="handleFileChange(index, $event, 2)" />
                                                            <img @click="openFileInput(index, 2)"
                                                                class="profile-user-img img-fluid rounded mt-2"
                                                                style="width: 200px; height: 128px; cursor: pointer;"
                                                                :src="symptom.imagePreview || '/dashboard/dist/img/user4-128x128.jpg'"
                                                                :alt="`Symptom ${index + 1} Image`" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="card-footer">
                                                <button @click="symptoms_upload(2)" type="submit"
                                                    class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="custom-tabs-four-settings" role="tabpanel"
                                        aria-labelledby="custom-tabs-four-settings-tab">
                                        <div class="card card-primary">
                                            <div class="card-body">
                                                <!-- Rows for Symptoms -->
                                                <div v-for="(symptom, index) in symptoms_3" :key="symptom.id"
                                                    class="row mt-3">
                                                    <div class="form-group col-sm-7">
                                                        <label :for="`symptom3${index + 1}`">Symptom {{ index + 1
                                                            }}</label>
                                                        <input v-model="symptom.sym" type="text" class="form-control"
                                                            :id="`symptom3${index + 1}`"
                                                            :placeholder="`Enter Symptom ${index + 1}`" />
                                                    </div>
                                                    <div class="col-sm-1"></div>
                                                    <div class="form-group col-sm-4">
                                                        <label :for="`image3${index + 1}`">Image</label>
                                                        <div>
                                                            <input type="file" class="d-none" :id="`image3${index + 1}`"
                                                                @change="handleFileChange(index, $event, 3)" />
                                                            <img @click="openFileInput(index, 3)"
                                                                class="profile-user-img img-fluid rounded mt-2"
                                                                style="width: 200px; height: 128px; cursor: pointer;"
                                                                :src="symptom.imagePreview || '/dashboard/dist/img/user4-128x128.jpg'"
                                                                :alt="`Symptom ${index + 1} Image`" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Submit Button -->
                                            <div class="card-footer">
                                                <button @click="symptoms_upload(3)" type="submit"
                                                    class="btn btn-primary">Submit</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
.profile-user-img:hover {
    background-color: yellow;
    cursor: pointer;
}
</style>