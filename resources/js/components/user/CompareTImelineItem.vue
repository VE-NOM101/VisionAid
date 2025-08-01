<template>
    <div v-if="prevData != null && result != null">
        <i class="fa fa-pie-chart hover:scale-160 transition-all duration-200" :class="result.progression_color"></i>

        <div class="timeline-item text-center result" :class="result.progression_color">
            <h3 class="timeline-header text-white no-border">{{ result.progression_text }} || {{ result.ssim_text }}
            </h3>
        </div>
        <div class="timeline-item text-center result" :class="result.recommendation_color">
            <h3 class="timeline-header text-white no-border">{{ result.recommendation }}</h3>
        </div>


    </div>
</template>

<script setup>
import axios from 'axios';
import { useToastr } from '../../toastr';
import { onMounted, ref } from 'vue';

const toastr = useToastr();
const props = defineProps(['data', 'prevData']);
const result = ref(null);

async function fetchSsimScore() {
    const res = await axios.post('http://127.0.0.1:5000/compare-heatmap-ssim', {
        'hm1': props.data.heatmap_file,
        'hm2': props.prevData.heatmap_file
    });

    if (res.data.success) {
        return res.data.ssim_score;
    } else {
        toastr.error(res.data.error);
        return null;
    }
}
onMounted(async () => {
    if (props.prevData != null) {
        const ssim = await fetchSsimScore();
        if (ssim !== null) {
            result.value = analyzeProgression(props.prevData.class_index, props.data.class_index, ssim);
        }
    }
});

function analyzeProgression(prevClass, currClass, ssim) {
    const classes = ["No DR", "Mild NPDR", "Moderate NPDR", "Severe NPDR", "Proliferative DR"];
    const progression = currClass - prevClass;

    let status = '';
    let status_color = '';
    if (progression > 0) {
        status = 'worsened';
        status_color = 'bg-danger';
    } else if (progression < 0) {
        status = 'improved';
        status_color = 'bg-success';
    } else {
        status = 'remained stable';
        status_color = 'bg-info';
    }

    let ssimNote = '';
    if (ssim >= 0.85) {
        ssimNote = 'no significant visual change';
    } else if (ssim >= 0.65) {
        ssimNote = 'moderate visual change';
    } else {
        ssimNote = 'significant visual change';
    }

    let suggestion = '';
    let suggestion_color = '';

    if (progression > 0 && ssim < 0.65) {
        suggestion = 'Immediate consultation is strongly advised.';
        suggestion_color = 'bg-gradient-danger'; // Red
    } else if (progression > 0) {
        suggestion = 'Close monitoring recommended.';
        suggestion_color = 'bg-gradient-orange'; // Orange
    } else if (progression < 0 && ssim < 0.65) {
        suggestion = 'Improvement noted; continue prescribed care.';
        suggestion_color = 'bg-gradient-warning'; // Yellow
    } else if (progression < 0) {
        suggestion = 'DR improved with stable visuals; continue regular checkups.';
        suggestion_color = 'bg-gradient-success'; // Green
    } else if (progression === 0 && ssim < 0.65) {
        suggestion = 'DR stable but noticeable visual change; consider rechecking.';
        suggestion_color = 'bg-gradient-purple'; // Purple
    } else {
        suggestion = 'No progression or visual change; continue routine care.';
        suggestion_color = 'bg-gradient-info'; // Blue
    }



    return {
        prev_stage: classes[prevClass],
        curr_stage: classes[currClass],
        progression_text: `DR has ${status}`,
        progression_color: status_color,
        ssim_text: `${ssim.toFixed(2)} (${ssimNote})`,
        recommendation: suggestion,
        recommendation_color: suggestion_color

    };
}


</script>

<style scoped>
.result {
    opacity: 90%;
}

.result:hover {
    opacity: 100%;
    transform: scaleX(1.02);
    transition-duration: 300ms;
}
</style>