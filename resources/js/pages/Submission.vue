<template>
    <Head :title="__(title)" />

    <Header :title="title" icon="arrow-up-right">
        <div>
            <span v-if="!completed"
                class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200"
                >
                <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3" />
                </svg>
                Incomplete
            </span>

            <span v-else
                class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200"
            >
                <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                    <circle cx="3" cy="3" r="3" />
                </svg>
                Completed
            </span>
        </div>
    </Header>

    <div class="card p-0">
        <Panel
            v-for="(exportItem, index) in exports"
            :key="exportItem.id ?? index"
            :class="{
                'bg-gray-200 dark:bg-dark-700': index % 2 === 1,
                'dark:border-dark-900 border-t': index !== 0,
            }"
        >
            <PanelHeader>
                <Heading class="flex justify-between items-center">
                    <span>{{ exportItem.destination }}</span>

                    <span v-if="exportItem.is_failed"
                            class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200"
                        >
                            <svg class="h-1.5 w-1.5 fill-red-500" viewBox="0 0 6 6" aria-hidden="true">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Failed
                        </span>

                        <span v-else-if="exportItem.is_pending"
                            class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200"
                        >
                            <svg class="h-1.5 w-1.5 fill-yellow-500" viewBox="0 0 6 6" aria-hidden="true">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Pending
                        </span>

                        <span
                            v-else
                            class="inline-flex items-center bg-white gap-x-1.5 rounded-md px-2 py-1 text-xs font-medium text-gray-900 ring-1 ring-inset ring-gray-200"
                        >
                            <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6" aria-hidden="true">
                                <circle cx="3" cy="3" r="3" />
                            </svg>
                            Completed
                        </span>
                </Heading>
            </PanelHeader>
            <Card>
                <div class="flex justify-between items-center mt-2">
                    <JsonPrettyPrint v-if="exportItem.submission_payload"
                        :data="exportItem.submission_payload"
                        class="text-gray dark:text-dark-150 text-sm my-2"
                    />

                    <Form v-if="!exportItem.is_completed"
                        method="POST"
                        :action="exportItem.run_url"
                    >

                        <ui-button class="btn" type="submit">
                            {{ exportItem.is_failed ? 'Retry Export' : 'Run Export' }}
                        </ui-button>
                    </Form>
                </div>

                <template v-if="exportItem.errors">
                    <p class="bg-red-400 rounded p-2 my-4">Errors</p>
                    <JsonPrettyPrint
                        :data="exportItem.errors"
                        class="text-gray dark:text-dark-150 text-sm my-2"
                    />
                </template>
            </Card>
        </Panel>
  </div>

  <DocsCallout
    topic="Statamic Formidable"
    url="https://packagist.org/packages/fahlgrendigital/packages-statamic-form-manager"
  />
</template>

<script setup>
import { computed } from 'vue'
import { Head, Form } from '@statamic/cms/inertia'
import { Header, DocsCallout, Panel, PanelHeader, Heading, Card } from '@statamic/cms/ui'
import JsonPrettyPrint from '../components/JsonPrettyPrint.vue'

const props = defineProps({
    submission: { type: Object, required: true },
    exports: { type: Array, required: true },
    completed: { type: Boolean, required: true },
})

const title = computed(() =>
    props.submission ? `Submission #${props.submission.id}` : 'Submission'
)
</script>
