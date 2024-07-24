<script setup>
import { Link } from '@inertiajs/vue3';


const props = defineProps({
    pagination: {
        type: Object
    }
})

</script>

<template>

    <span v-for="link in pagination.links" :key="link.label" class="mx-1 p-3 bg-slate-200 rounded-lg" :class="{ 'bg-sky-300' : link.active }">

        <Link :href="link.url ?? ''"  v-html="link.label"></Link>

    </span>

</template>
