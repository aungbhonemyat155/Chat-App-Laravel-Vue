<script setup>
import { useMainStore } from "@/stores/MainStore";
import { onMounted, ref } from 'vue'
import { storeToRefs } from "pinia";
import { useIntersectionObserver } from '@vueuse/core'

const store = useMainStore()

const props = defineProps({
    index: {
        type: Number,
    },
    friend_id: {
        type:Number,
    }
});

const { messages,userData } = storeToRefs(store)

const target = ref(null)

const { stop } = useIntersectionObserver(
    target,
    ([{ isIntersecting }], observerElement) => {
        if(isIntersecting){
            alert('this is at the end of message')
        }
    },
)

onMounted(() => {
    axios.get('messages/'+props.friend_id).then(response => {
        console.log(response.data);
    }).catch(error => {
        console.log(error);
    })
})
</script>

<template>
    <section>
        <div v-for="(message, index) in messages" :key="index" class="m-2 p-2 w-fit max-w-lg rounded-xl" :class="{'self-end bg-blue-900' : userData.user.id == message.from_user_id, 'bg-slate-700' : userData.user.id != message.from_user_id}">{{ message.message }}</div>
        <div ref="target">last</div>
    </section>
</template>
