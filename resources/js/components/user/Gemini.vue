<template>
    <div class="container my-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="text-center mb-4">Ask Gemini AI</h2>

                <textarea v-model="query" placeholder="Enter your query" class="form-control mb-3" rows="3">
                </textarea>

                <div class="text-center">
                    <button @click="sendQuery" class="btn btn-primary">
                        Ask Gemini
                    </button>
                </div>

                <div v-if="response" class="mt-4">
                    <h3 class="text-secondary">Response:</h3>
                    <div class="p-3 bg-light rounded border">
                        <ul v-for="(item, index) in formattedResponse" :key="index">
                            <li>
                                <strong>Candidate {{ index + 1 }}:</strong>
                                <p>{{ item }}</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
    setup() {
        const query = ref('');
        const response = ref('');

        const sendQuery = async () => {
            try {
                const res = await axios.post('/api/gemini', { query: query.value });
                response.value = res.data.candidates[0]?.content?.parts[0]?.text || 'No response';
            } catch (error) {
                console.error(error);
                response.value = 'Error fetching response';
            }
        };

        return { query, response, sendQuery };
    },
};
</script>

<style scoped>
/* Custom CSS for improved aesthetics */
textarea {
    resize: none;
    font-size: 1rem;
}

button {
    width: 150px;
    font-size: 1rem;
}

.card {
    max-width: 600px;
    margin: auto;
    border-radius: 10px;
}

.card-body {
    padding: 2rem;
}

h2 {
    color: #007bff;
}

.bg-light {
    background-color: #f8f9fa !important;
}

.text-secondary {
    color: #6c757d !important;
}
</style>
