<script setup>
import VueJsonPretty from 'vue-json-pretty';
import 'vue-json-pretty/lib/styles.css';

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <div>
        <vue-json-pretty :show-icon="true" :data="data" />
    </div>
</template>
