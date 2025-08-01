<template>
    <!-- timeline time label -->
    <div class="time-label">
        <span class="bg-yellow">{{ new Date(data.created_at).toLocaleDateString('en-GB', {
            day: '2-digit', month: 'short',
            year: 'numeric'
        }) }}
        </span>
    </div>
    <!-- /.timeline-label -->
    <!-- timeline item -->
    <div>
        <i class="fas fa-envelope bg-blue"></i>
        <div class="timeline-item">
            <span class="time"><i class="fas fa-clock"></i> {{ new Date(data.created_at).toLocaleTimeString() }}</span>
            <h3 class="timeline-header text-bold" :class="class_index_color">{{ classname }}</h3>

            <div class="timeline-body">
                <div class="col-12 d-flex align-items-stretch flex-column">
                    <div class="card bg-light d-flex flex-fill">
                        <div class="card-header text-muted border-bottom-0">
                        </div>
                        <div class="card-body pt-0">
                            <div class="row">
                                <div class="col-7">
                                    <div class="card-header text-muted border-bottom-0">
                                        <h4><b>Description: </b> {{ data.optional }}</h4>
                                    </div>
                                    <ul v-if="prevData != null" class="ml-4 mb-0 fa-ul text-muted">
                                        <!-- Button to trigger modal -->
                                        <button class="btn btn-primary" @click="showRecommendation">AI
                                            Recommendation</button>

                                        <!-- Modal (basic inline modal, can be replaced with Bootstrap or another library) -->
                                        <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
                                            <div class="modal-content">
                                                <div class="text-center text-warning">
                                                    <h3>Recommendation</h3>
                                                </div>
                                                <div v-if="isLoading">
                                                    <h4>Loading....</h4>
                                                </div>
                                                <div v-else>
                                                    <p>{{ recommendation.ssim_text }}</p>
                                                    <div class="suggestion-scroll-container">
                                                        <ol>
                                                            <li v-for="(step, index) in suggestionSteps" :key="index">
                                                                {{ step }}
                                                            </li>
                                                        </ol>
                                                    </div>

                                                </div>
                                                <button class="btn btn-danger mt-4"
                                                    @click="showModal = false">Close</button>
                                            </div>
                                        </div>

                                    </ul>
                                </div>
                                <div class="col-5 text-center">
                                    <img :src="'/images/overlay_images/' + data.heatmap_file + '.png'" alt="user-avatar"
                                        class="rounded img-fluid heatmap-image" />

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END timeline item -->
    <!-- timeline item -->
    <CompareTImelineItem :data="props.data" :prevData="props.prevData" />

</template>

<script setup>
import { ref, computed } from 'vue';
import CompareTImelineItem from './CompareTImelineItem.vue';
import useRecommendation from '../../stores/useRecommendation.js'

const props = defineProps(['data', 'prevData']);
const class_index_color = ref('');
const classname = ref('');
const showModal = ref(false); // <-- modal control

const recommendation = useRecommendation();

const query = ref('');
const response = ref('');
const isLoading = ref(false);

const showRecommendation = async () => {
    showModal.value = true;
    isLoading.value = true;
    await aiRecommendation(2, 3, 0.61, 'eye etching');
    isLoading.value = false;
}

const aiRecommendation = async (prevClass, currClass, ssim, description = '') => {

    if (!recommendation.ssim_text || !recommendation.suggestion_text) {
        query.value = `
        My diabetic retinopathy (DR) classification has changed from class ${prevClass} to class ${currClass}.
        The SSIM (Structural Similarity Index Measure) between the previous and current retinal images is ${ssim}.
        ${description ? 'Additional notes: ' + description : ''}

        Please generate two sections:

        1. **SSIM_TEXT**: In 1–2 sentences, explain what this SSIM value means. Keep the explanation simple and use easy language. Say whether the images look very different or not, and what that might mean.

        2. **SUGGESTION_TEXT**: Give a short list of easy next steps for me to follow. Use simple language and keep each step short. Use a numbered list (e.g., 1. Do this, 2. Do that...).

        Format the output like this:

        SSIM_TEXT: <your explanation here>
        SUGGESTION_TEXT: <your step-by-step recommendation here>
        `.trim();
        try {
            const res = await axios.post('/api/gemini', {
                query: query.value,
            });

            const fullResponse = res.data.candidates[0]?.content?.parts[0]?.text || 'No response';
            response.value = fullResponse;

            // Extract multi-line SSIM and suggestion text
            const ssimMatch = fullResponse.match(/SSIM_TEXT\s*[:\-]?\s*([\s\S]*?)SUGGESTION_TEXT/i);
            const suggestionMatch = fullResponse.match(/SUGGESTION_TEXT\s*[:\-]?\s*([\s\S]*)/i);

            recommendation.ssim_text = ssimMatch ? ssimMatch[1].trim() : 'No SSIM explanation found.';
            recommendation.suggestion_text = suggestionMatch ? suggestionMatch[1].trim() : 'No suggestion found.';
        } catch (error) {
            console.error('Gemini API error:', error);
            response.value = 'Error fetching AI response.';
            recommendation.ssim_text = 'Error';
            recommendation.suggestion_text = 'Error';
        }
    }
};

const suggestionSteps = computed(() => {
    if (!recommendation.suggestion_text) return [];

    // Split by numbered steps: "1. ...", "2. ..."
    const splitSteps = recommendation.suggestion_text.split(/\d\.\s+/).filter(Boolean);
    return splitSteps;
});

if (props.data.class_index == 0) {
    class_index_color.value = 'text-success';
    classname.value = 'No Diabetic Retinopathy';
} else if (props.data.class_index == 1) {
    class_index_color.value = 'text-info';
    classname.value = 'Mild Diabetic Retinopathy';
} else if (props.data.class_index == 2) {
    class_index_color.value = 'text-warning';
    classname.value = 'Moderate Diabetic Retinopathy';
} else if (props.data.class_index == 3) {
    class_index_color.value = 'text-dark';
    classname.value = 'Severe Diabetic Retinopathy';
} else if (props.data.class_index == 4) {
    class_index_color.value = 'text-danger';
    classname.value = 'Proliferative Diabetic Retinopathy';
}

</script>

<style scoped>
.heatmap-image {
    width: 100%;
    /* or set max-width if needed */
    max-width: 300px;
    filter: grayscale(30%);
    transition: transform 0.3s ease, filter 0.3s ease;
}

.heatmap-image:hover {
    transform: scale(1.1);
    filter: grayscale(0%);
}

.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    padding: 1.5rem;
    border-radius: 10px;
    max-width: 500px;
    width: 90%;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}
</style>