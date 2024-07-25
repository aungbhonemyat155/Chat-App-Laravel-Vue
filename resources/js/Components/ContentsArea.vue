<script setup>
import { useMainStore } from "@/stores/MainStore";
import { onMounted, ref } from 'vue'
import { storeToRefs } from "pinia";
import { useIntersectionObserver } from '@vueuse/core'
import TextInput from "@/Components/TextInput.vue"

const store = useMainStore()

const { friendLists, friendIndex, userData } = storeToRefs(store)

const messageBox = ref("")

const target = ref(null)

// const { stop } = useIntersectionObserver(
//     target,
//     ([{ isIntersecting }], observerElement) => {
//         if(isIntersecting){
//             alert('this is at the end of message')
//         }
//     },
// )

</script>

<template>
    <section class="col-span-9 border-l border-l-slate-800 bg-gray-900 flex flex-col h-screen">
        <div class="basis-[93%] flex flex-col-reverse overflow-y-scroll">
            <div v-for="(message, index) in friendLists.data[friendIndex].messages.data" :key="index" class="m-2 p-2 w-fit max-w-lg rounded-xl" :class="{'self-end bg-blue-900' : userData.user.id == message.from_user_id, 'bg-slate-700' : userData.user.id != message.from_user_id}">{{ message.message }}</div>
            <!-- <div ref="target">last</div> -->
        </div>

        <div class="basis-[7%] bg-gray-900 flex items-center p-3">
            <TextInput v-model="messageBox" placeholder="Text Here...." class="flex-1 border-none outline-none"></TextInput>
            <button class="px-2 hover:bg-slate-700 py-2">Send <span><i class="fa-regular fa-paper-plane"></i></span></button>
        </div>
    </section>
</template>
