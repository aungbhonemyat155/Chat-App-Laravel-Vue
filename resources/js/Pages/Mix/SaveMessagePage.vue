<script setup>
import { useMainStore } from '@/stores/MainStore';
import { storeToRefs } from 'pinia';
import TextInput from '@/Components/TextInput.vue';
import { ref } from "vue"

const store = useMainStore()
const messageBox = ref("");

const sendFun = () => {

}


const { tempMessages } = storeToRefs(store)
</script>

<template>
    <section class="bg-gray-900 h-screen border-l border-l-slate-700">
        <div class="h-full flex flex-col">
            <div v-if="tempMessages.data.length" class="basis-[93%] hidden"></div>
            <div class="w-full flex justify-center items-center basis-[93%]">
                <span class="bg-gray-700 p-3 rounded-lg">There's no save data yet! Try to save something...</span>
            </div>
            <form @submit.prevent="sendFun" class="basis-[7%] bg-gray-900 flex items-center p-3">
                <TextInput v-model="messageBox" placeholder="Text Here...." class="flex-1 border-none outline-none"></TextInput>
                <button class="px-2 hover:bg-slate-700 py-2">Send <span><i class="fa-regular fa-paper-plane"></i></span></button>
            </form>
        </div>
    </section>
</template>
