import { defineStore } from "pinia";
import { ref } from "vue";

const useRecommendation = defineStore("recommendation", () => {
    const ssim_text = ref(null);
    const suggestion_text = ref(null);

    const set_ssim_text = (text) => {
        ssim_text.value = text;
    };

    const set_suggestion_text = (text) => {
        suggestion_text.value = text;
    };

    return { ssim_text, suggestion_text, set_ssim_text, set_suggestion_text };
});

export default useRecommendation;
