<template>
    <div>
        <Header title="Exports" icon="arrow-up-right" />

        <Listing
          ref="listing"
          :url="requestUrl"
          :columns="columns"
          :action-url="actionUrl"
          :preferences-prefix="preferencesPrefix"
          :filters="filters"
          push-query
        >
            <template v-slot:cell-form_handle="{ row: record }">
                <a class="text-blue hover:text-blue-dark"
                    :href="cp_url(`forms/${record.form_handle}`)"
                    style="word-break: break-all">{{ record.form_handle }}</a>
            </template>
            <template v-slot:cell-submission_id="{ row: record }">
                <a class="text-blue hover:text-blue-dark"
                    :href="cp_url(`formidable/submissions/${record.submission_id}`)"
                    style="word-break: break-all">{{ record.submission_id }}</a>
            </template>
      </Listing>
    </div>
</template>

<script setup>
import { Listing, Header } from '@statamic/cms/ui';
import { ref } from 'vue';

const props = defineProps({
    actionUrl: String,
    columns: Array,
    filters: Array
});
const preferencesPrefix = ref(`formidable.exports`);
const requestUrl = ref(cp_url(`formidable/api/exports`));
const items = ref(null);
const page = ref(null);
const perPage = ref(null);

const requestComplete = (response) => {
    items.value = response.data.data;
    page.value = response.data.meta.pagination.page;
    perPage.value = response.data.meta.pagination.per_page;
};
</script>