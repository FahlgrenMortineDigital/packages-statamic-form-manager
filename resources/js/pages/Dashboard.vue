<script setup>
import { Head } from '@statamic/cms/inertia';
import { Header, DocsCallout } from '@statamic/cms/ui';
import ExportsListing from '../components/ExportsListing.vue';

defineProps({
    columns: { type: Array, required: true },
    filters: { type: Array, required: true },
    actionUrl: { type: String, required: true },
})
</script>

<template>
    <Head :title="[__('Exports')]" />

    <ExportsListing :columns="columns" :filters="filters" :action-url="actionUrl" />

    <DocsCallout
        topic="Statamic Formidable"
        url="https://packagist.org/packages/fahlgrendigital/packages-statamic-form-manager"
    />
</template>