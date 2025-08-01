<script>
import axios from "axios";
import { onMounted, ref } from "vue";
import { useToastr } from "../../toastr.js";

export default {
    props: ["id"], // Automatically receives the route param

    setup(props) {
        const toastr = useToastr();
        const prediction_id = props.id;
        const query = ref('');
        const response = ref('');
        const formattedResponse = ref([]);
        const prediction_details = ref(null);
        const suggestion_list = ref(null);
        const get_prediction_data = async () => {
            try {
                const response = await axios.get(
                    `/api/_user/get-prediction-data/${prediction_id}`
                );
                prediction_details.value = response.data["prediction_details"] || {};
                suggestion_list.value = response.data['suggestion_list'] || [];
                toastr.success(response.data.message);
                console.log(suggestion_list.value);
            } catch (error) {
                toastr.error("Failed to retrieve prediction details.");
                console.error("Error:", error);
            }
        };

        const sendQuery = async () => {
            try {
                const temp_query = "Ans should not be greater than 250 words and use proper headline notation:" + query.value;
                console.log(temp_query);
                const res = await axios.post('/api/gemini', { query: temp_query });
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
                .post(`/api/_user/save-query`, { 'query': query.value, 'response': response.value, 'prediction_id': prediction_id })
                .then((response) => {
                    toastr.success(response.data.message);
                    get_prediction_data();
                })
                .catch((error) => {
                    toastr.error("Failed to store query.");
                    console.error("Error storing query data:", error);
                });
        }

        onMounted(() => {
            get_prediction_data();
        });

        const delete_suggestion = (suggestion_id)=>{
            axios
                .get(`/api/_user/delete-suggestion/${suggestion_id}`)
                .then((response) => {
                    get_prediction_data();
                    toastr.success(response.data.message);
                })
                .catch((error) => {
                    toastr.error("Failed to retrieve records.");
                    console.error("Error:", error);
                });
        };
        return {
            prediction_details,
            query,
            response,
            formattedResponse,
            sendQuery,
            saveQuery,
            suggestion_list,
            delete_suggestion,
        };
    },
};
</script>

<template>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card" style="background-color: grey;">
                        <div class="card-header">
                            <div class="row">
                                <div class="col-4">
                                    <router-link to="/_user/deeptest" active-class="active" class="nav-link">
                                        <button class="btn btn-danger">
                                            <i class="fa-solid fa-backward"> Back</i>
                                        </button>
                                    </router-link>
                                </div>
                                <div class="col-8">
                                    <h1 class="card-title dark-transparent">Archive</h1>
                                </div>
                            </div>

                            <div class="card">
                                <div class="card-body" style="background-color: darkslategray;">
                                    <!-- Add a loading state or fallback for null data -->
                                    <div v-if="prediction_details">
                                        <div class="row dark-transparent">
                                            <div class="col-6">
                                                <h5>{{ prediction_details.predicted_disease }}</h5>
                                            </div>
                                            <div class="col-3">
                                                <h5>{{ prediction_details.percentage }}%</h5>
                                            </div>
                                            <div class="col-3">
                                                <h5>{{ new Date(prediction_details.updated_at).toLocaleString() }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <p>Loading prediction details...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">

                            <div class="row dark-transparent">
                                <div class="col-6">
                                    <div class="modal-body" id="image_box">
                                        <div class="text-center mb-1">
                                            <h2 class="" style="color:red;"><i class="fa-brands fa-rocketchat"></i></h2>
                                        </div>

                                        <textarea v-model="query" placeholder="Enter your query"
                                            class="form-control dark-transparent" rows="3">
                                        </textarea>

                                        <div v-if="response" class="mt-4">
                                            <h3 class="text-secondary">Response:</h3>
                                            <div class="p-3 bg-light rounded border response-container">
                                                <div v-html="response"></div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-primary" @click="sendQuery">Ask</button>
                                        <button v-if="response" type="button" class="btn btn-success"
                                            @click="saveQuery">Save</button>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center mb-1">
                                        <h1 class="" style="color:darkorchid;"><i class="fa-brands fa-bots"></i></h1>
                                    </div>
                                    <div class="chat-container">
                                        <div v-for="suggestion in suggestion_list" :key="suggestion.id"
                                            class="chat-message">
                                            <div class="trash-icon">
                                                <button class="btn btn-danger" @click="delete_suggestion(suggestion.id)"><i class="fa-solid fa-trash"></i></button>
                                            </div>
                                            <!-- Query -->
                                            <div class="query">
                                                <p class="query-text"><i class="fa-solid fa-user"></i><br /> {{
                                                    suggestion.query }}</p>
                                                <div class="updated-at-box">
                                                    <p class="updated-at">{{ new
                                                        Date(suggestion.updated_at).toLocaleString() }}</p>
                                                </div>
                                            </div>

                                            <!-- Response -->
                                            <div class="response">
                                                <p class="response-text">
                                                    <i class="fa-solid fa-robot"></i><br /><span
                                                        v-html="suggestion.response"></span>
                                                </p>
                                                <div class="updated-at-box">
                                                    <p class="updated-at">{{ new
                                                        Date(suggestion.updated_at).toLocaleString() }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.dark-transparent {
    background-color: rgba(0, 0, 0, 0.6);
    /* Dark background with 60% opacity */
    padding: 10px;
    /* Add some padding for spacing */
    border-radius: 8px;
    /* Rounded corners */
    color: #ffffff;
    /* White text for contrast */
}

.dark-transparent h5 {
    margin: 0;
    /* Remove default margin for headings */
    font-weight: bold;
    /* Make the text bold for better visibility */
}

.chat-container {
    max-height: 490px;
    /* Set the max height for scrollability */
    overflow-y: auto;
    /* Make the container scrollable */
    background-color: rgba(0, 0, 0, 0.8);
    /* Dark transparent background */
    border: 1px solid rgba(255, 255, 255, .75);
    /* Optional: Border for visibility */
    padding: 15px;
    border-radius: 8px;
    color: #ffffff;
    /* White text for contrast */
}

.chat-message {
    display: flex;
    flex-direction: column;
    /* Stack query and response vertically */
    align-items: flex-start;
    /* Align items to the start */
    margin-bottom: 15px;
    /* Space between messages */
}

.query {
    align-self: flex-end;
    /* Align query to the right */
    background-color: rgba(33, 150, 243, 0.8);
    /* Semi-transparent blue for query */
    border-radius: 12px;
    padding: 10px;
    max-width: 70%;
    margin-bottom: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    color: #ffffff;
    /* White text for query */
}

.response {
    align-self: flex-start;
    /* Align response to the left */
    background-color: rgba(76, 175, 80, 0.8);
    /* Semi-transparent green for response */
    border-radius: 12px;
    padding: 10px;
    max-width: 70%;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
    color: #ffffff;
    /* White text for response */
}

.query-text,
.response-text {
    margin: 0;
    /* Remove margin from paragraphs */
}

.updated-at-box {
    background-color: rgba(255, 255, 255, 0.1);
    /* Light transparent background */
    border: 1px solid rgba(255, 255, 255, 0.2);
    /* Subtle border color */
    border-radius: 5px;
    padding: 5px 10px;
    margin: 5px;
    text-align: right;
}

.updated-at {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.7);
    /* Subtle text color */
    margin: 0;
}

.response-container {
    max-height: 300px;
    /* Set a maximum height */
    overflow-y: auto;
    /* Enable vertical scrolling */
    border: 1px solid #ddd;
    /* Optional: Add a border for visual separation */
    padding: 10px;
    /* Add some padding for better readability */
    background-color: #f8f9fa;
    /* Light background color */
}
</style>
