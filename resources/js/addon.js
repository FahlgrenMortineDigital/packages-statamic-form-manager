import ExportsListing from './components/ExportsListing.vue';
import JsonPrettyPrint from './components/JsonPrettyPrint.vue';
import Dashboard from './pages/Dashboard.vue';
import Submission from './pages/Submission.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('formidable::Dashboard', Dashboard);
    Statamic.$inertia.register('formidable::Submission', Submission);
    Statamic.$components.register('exports-listing', ExportsListing);
    Statamic.$components.register('json-pretty-print', JsonPrettyPrint);
});
