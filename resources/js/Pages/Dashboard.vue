<script setup>
import HomeLayout from '@/Layouts/HomeLayout.vue';
import { useMainStore } from '@/stores/MainStore';
import { Head } from '@inertiajs/vue3';
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';

const props = defineProps({
    data : Object
})

const store = useMainStore()

const { userData } = storeToRefs(store)

onMounted(()=>{
    store.setUser(props.data.userData)
    store.setNoti(props.data.notifications)
    store.setFriendLists(props.data.friendLists)
})

</script>

<template>
    <Head title="Dashboard" />

    <HomeLayout v-if="userData"></HomeLayout>
</template>

<style>
    /* Cross-browser compatibility */
    * {
      scrollbar-width: thin; /* thin scrollbar */
      scrollbar-color: #aaa transparent; /* color of the scroll thumb and track */
    }
</style>
