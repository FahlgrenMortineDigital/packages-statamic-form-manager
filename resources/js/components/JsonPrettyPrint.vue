<script>
import VueJsonPretty from 'vue-json-pretty';
import 'vue-json-pretty/lib/styles.css';
export default {
    name: "JsonPrettyPrint",
    components: {
        VueJsonPretty,
    },
    props: {
        data: {
            type: Object,
            required: true,
        },
    }
}
</script>

<template>
    <div>
        <vue-json-pretty :data="data" />
    </div>
</template>
