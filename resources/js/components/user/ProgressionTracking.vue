<template>

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Track Your Disease</h1>
                </div>
                <div class="col-sm-6">
                    <button @click="gotoTimeline" class="btn btn-block bg-gradient-primary btn-lg">
                        <i class="fa-solid fa-timeline"></i> Goto Timeline <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- general form elements -->
        <div class="row m-1">
            <div class="card card-warning col-6">
                <div class="card-header">
                    <h3 class="card-title">Start Tracking</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="exampleInputFile">Upload retina image</label>

                            <div class="form-group d-flex flex-column align-items-center">
                                <input type="file" class="d-none" id="retina_image_id" @change="selectImage" />
                                <img @click="openFileInput('retina_image_id')" class="profile-user-img img-fluid  mt-2"
                                    style="width: 200px; height: 200px; cursor: pointer;"
                                    :src="inputData.imgURL || '/dashboard/dist/img/user4-128x128.jpg'"
                                    alt="Retina Image Preview" />
                            </div>

                        </div>
                        <div class="form-group">
                            <label for="exampleInputEmail1">Optional</label>
                            <input v-model="inputData.optional" type="text" class="form-control" id="exampleInputEmail1"
                                placeholder="Any symptoms?">
                        </div>
                    </div>

                    <div class="card-footer">
                        <div @click="apiGradCam" class="btn btn-info col-12">Done</div>
                    </div>
                </form>

            </div>
            <div class="card card-success col-6">
                <div class="card-header">
                    <h3 class="card-title">Quick Result</h3>
                </div>
                <div class="card-body">
                    <div v-if="isLoading" class="form-group d-flex flex-column align-items-center">
                        <VueSpinnerFacebook size="100" />
                    </div>
                    <div v-else class="form-group d-flex flex-column align-items-center">
                        <img class="profile-user-img img-fluid mt-2"
                            style="width: 300px; height: 300px; cursor: pointer;"
                            :src="resultData.heatmapURL || '/dashboard/dist/img/user4-128x128.jpg'"
                            alt="Retina Image Preview" />
                        <h3 class="text-danger mt-2">{{ resultData.predicted_class }}</h3>
                    </div>

                </div>
            </div>
        </div>

    </section>

</template>

<script setup>
import { onMounted, ref } from 'vue';
import axios from 'axios';
import { useToastr } from '../../toastr';
import { VueSpinnerFacebook } from 'vue3-spinners';
import { useRouter } from 'vue-router';
const router = useRouter();
const toaster = useToastr();
const inputData = ref({
    'optional': '',
    'imgURL': '',
});
const resultData = ref({
    'predicted_class': 'Predicted Class',
    'heatmapURL': '',
})
const selectedImage = ref(null);
const openFileInput = (id) => {
    const input = document.getElementById(`${id}`);
    if (input) input.click();
};
const selectImage = (e) => {
    const file = e.target.files[0];
    if (file) {
        selectedImage.value = file;
        inputData.value.imgURL = URL.createObjectURL(file)
    }
};

const isLoading = ref(false);


const apiGradCam = async () => {
    isLoading.value = true;
    if (selectedImage.value) {
        const formData = new FormData();
        formData.append('image', selectedImage.value); // Assuming selectedImage is a File object

        try {
            const response = await axios.post('http://127.0.0.1:5000/gradCam', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            if (response.data.success) {
                const getRes = await axios.post('/api/_user/store-track-data', {
                    'heatmap_file': response.data.heatmap_file,
                    'class_index': response.data.prediction,
                    'optional': inputData.value.optional,
                });
                if (getRes.data.success) {
                    showQuickResult(response.data);
                    toaster.success('Grad-CAM generated and tracked successfully!');
                } else {
                    toaster.success('Something went wrong while generating Grad-CAM');
                }

            } else {
                toaster.error(response.data.error);
            }

        } catch (error) {
            console.error('Error:', error.message);
            toaster.error('Error processing image');
        }
    } else {
        toaster.error('Please upload this month\'s retina image');
    }
    isLoading.value = false;
}

const showQuickResult = (data) => {
    resultData.value.heatmapURL = '/images/overlay_images/' + data.heatmap_file + '.png';
    if (data.prediction == 0) {
        resultData.value.predicted_class = 'No Diabetic Retinopathy';
    } else if (data.prediction == 1) {
        resultData.value.predicted_class = 'Mild Diabetic Retinopathy';
    } else if (data.prediction == 2) {
        resultData.value.predicted_class = 'Moderate Diabetic Retinopathy';
    } else if (data.prediction == 3) {
        resultData.value.predicted_class = 'Severe Diabetic Retinopathy';
    } else if (data.prediction == 4) {
        resultData.value.predicted_class = 'Proliferative Diabetic Retinopathy';
    }
}

const gotoTimeline = () => {
    router.push('/_user/progression-timeline');
}



</script>