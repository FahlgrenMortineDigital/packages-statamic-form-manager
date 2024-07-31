<template>
    <div>
        <div v-if="initializing" class="card loading">
            <loading-graphic/>
        </div>

        <data-list
            v-if="!initializing"
            ref="datalist"
            :rows="items"
            :columns="columns"
            :sort="false"
            :sort-column="sortColumn"
            :sort-direction="sortDirection"
            @visible-columns-updated="visibleColumns = $event"
        >
            <template v-slot="{hasSelections}">
                <div>
                    <div class="card overflow-hidden p-0 relative">
                        <div class="flex flex-wrap items-center justify-between px-2 pb-2 text-sm border-b">
                            <data-list-filter-presets
                                ref="presets"
                                :active-preset="activePreset"
                                :active-preset-payload="activePresetPayload"
                                :active-filters="activeFilters"
                                :has-active-filters="hasActiveFilters"
                                :preferences-prefix="preferencesPrefix"
                                :search-query="searchQuery"
                                @selected="selectPreset"
                                @reset="filtersReset"
                            />

                            <div class="w-full flex-1">
                                <data-list-search class="h-8 mt-2 min-w-[240px] w-full" ref="search"
                                                  v-model="searchQuery" :placeholder="searchPlaceholder"/>
                            </div>

                            <div class="flex space-x-2 mt-2">
                                <button class="btn btn-sm ml-2" v-text="__('Reset')" v-show="isDirty"
                                        @click="$refs.presets.refreshPreset()"/>
                                <button class="btn btn-sm ml-2" v-text="__('Save')" v-show="isDirty"
                                        @click="$refs.presets.savePreset()"/>
                                <data-list-column-picker :preferences-key="preferencesKey('columns')"/>
                            </div>
                        </div>
                        <div>
                            <data-list-filters
                                ref="filters"
                                :filters="filters"
                                :active-preset="activePreset"
                                :active-preset-payload="activePresetPayload"
                                :active-filters="activeFilters"
                                :active-filter-badges="activeFilterBadges"
                                :active-count="activeFilterCount"
                                :search-query="searchQuery"
                                :is-searching="true"
                                :saves-presets="true"
                                :preferences-prefix="preferencesPrefix"
                                @changed="filterChanged"
                                @saved="$refs.presets.setPreset($event)"
                                @deleted="$refs.presets.refreshPresets()"
                            />
                        </div>

                        <div v-show="items.length === 0" class="p-6 text-center text-gray-500"
                             v-text="__('No results')"/>

                        <div class="overflow-x-auto overflow-y-hidden">
                            <data-list-table
                                v-show="items.length"
                                :allow-bulk-actions="false"
                                :loading="loading"
                                :reorderable="false"
                                :sortable="true"
                                :toggle-selection-on-row-click="false"
                                :allow-column-picker="true"
                                :column-preferences-key="preferencesKey('columns')"
                                @sorted="sorted"
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
                                <template v-slot:cell-exported_count="{ row: record }">
                                    <span>{{record.exported_count}}</span>
                                </template>
                                <template v-slot:cell-failed_count="{ row: record }">
                                    <span>{{record.failed_count}}</span>
                                </template>
                            </data-list-table>
                        </div>
                    </div>
                    <data-list-pagination
                        class="mt-3"
                        :resource-meta="meta"
                        :per-page="perPage"
                        @page-selected="selectPage"
                        @per-page-changed="changePerPage"
                    />
                </div>
            </template>
        </data-list>
    </div>
</template>

<script>
import Listing from "../../../vendor/statamic/cms/resources/js/components/Listing.vue";

export default {
    mixins: [Listing],

    components: {
        Listing,
    },

    data() {
        return {
            listingKey: "formidable.exports",
            preferencesPrefix: `formidable.exports`,
            requestUrl: cp_url(`formidable/api/exports`),
            columns: this.columns,
        };
    }
};
</script>