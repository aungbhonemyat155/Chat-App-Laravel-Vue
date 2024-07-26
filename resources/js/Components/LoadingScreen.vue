<script setup>
import TextInput from "@/Components/TextInput.vue"
import { ref } from "vue"

const testing = ref("")
</script>

<template>
    <section class="h-screen bg-gray-900 flex flex-col">
        <div class="basis-[93%] flex flex-col items-start overflow-y-scroll">
            <div class="w-3/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600"></div>
            <div class="w-1/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600"></div>
            <div class="w-2/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600 self-end"></div>
            <div class="w-3/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600 self-end"></div>
            <div class="w-1/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600"></div>
            <div class="w-2/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600 self-end"></div>
            <div class="w-3/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600"></div>
            <div class="w-1/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600 self-end"></div>
            <div class="w-2/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600 self-end"></div>
            <div class="w-3/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600"></div>
            <div class="w-1/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600 self-end"></div>
            <div class="w-2/6 h-12 mb-5 animate-pulse rounded-xl bg-gray-600 self-end"></div>
        </div>
        <div class="basis-[7%] bg-gray-900 flex items-center p-3">
            <TextInput v-model="testing" placeholder="Text Here...." class="flex-1 border-none outline-none"></TextInput>
            <button class="px-2 hover:bg-slate-700 py-2">Send <span><i class="fa-regular fa-paper-plane"></i></span></button>
        </div>
    </section>
</template>
