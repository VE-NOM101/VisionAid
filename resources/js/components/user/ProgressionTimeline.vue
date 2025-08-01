<template>
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Your Timeline</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            <!-- Timelime example  -->
            <div class="row">
                <div class="col-md-12">
                    <!-- The time line -->
                    <div class="timeline">
                        <SingleTimelineItem v-for="(item, index) in trackingData" :key="index"
                            :data="item"
                            :prevData="index > 0 ? trackingData[index - 1] : null" />
                    </div>
                </div>
                <!-- /.col -->
            </div>
        </div>
        <!-- /.timeline -->

    </section>
    <!-- /.content -->
</template>

<script setup>
import { onMounted, ref } from 'vue';
import SingleTimelineItem from './SingleTimelineItem.vue';

const trackingData = ref(null);

onMounted(async () => {
    const res = await axios.get('/api/_user/get-track-data');
    trackingData.value = res.data.data;
})
</script>