<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-6">
                <p>Hello world Admin</p>

                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title">Disease Frequency</h3>
                    </div>
                    <div class="card-body">
                        <!-- Canvas for Pie Chart -->
                        <canvas id="myPieChart" style="max-height: 400px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Chart from 'chart.js/auto'; // Import Chart.js
import { onMounted, ref } from "vue";
import axios from "axios";
import { useToastr } from "../../toastr.js";

export default {
    setup() {
        const disease_list = ref([]); // Holds disease data
        const frequencies = ref(null); // Holds frequency data
        const toastr = useToastr();

        const renderPieChart = () => {
            // Map disease names and frequencies into chart data
            const labels = disease_list.value.map(disease => disease.disease_name);
            const data = [frequencies.value.disease_1, frequencies.value.disease_2, frequencies.value.disease_3];

            // Get the canvas element
            const ctx = document.getElementById('myPieChart');
            if (ctx) {
                // Create a new Chart instance
                new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: labels, // Disease names as labels
                        datasets: [
                            {
                                label: 'Disease Frequencies',
                                data: data, // Frequencies as data
                                backgroundColor: [
                                    'rgba(255, 99, 132, 0.7)',
                                    'rgba(54, 162, 235, 0.7)',
                                    'rgba(255, 206, 86, 0.7)',
                                ],
                                borderColor: [
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(255, 206, 86, 1)',
                                ],
                                borderWidth: 1,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            },
                        },
                    },
                });
            } else {
                console.error('Canvas element not found for Pie Chart.');
            }
        };

        const getInfo = () => {
            axios
                .get("/api/_admin/get-info")
                .then((response) => {
                    // Populate data
                    disease_list.value = response.data.disease_list;
                    frequencies.value = response.data.frequencies;
                    console.log(frequencies.value);

                    // Render the chart
                    renderPieChart();
                })
                .catch((error) => {
                    toastr.error("Failed to retrieve data.");
                    console.error("Error:", error);
                });
        };

        // Use the onMounted lifecycle hook
        onMounted(() => {
            getInfo();
        });
    },
};
</script>
