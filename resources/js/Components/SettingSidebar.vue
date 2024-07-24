<script setup>
import DangerButton from '@/Components/DangerButton.vue'
import { Link } from "@inertiajs/vue3"
import { useMainStore } from "@/stores/MainStore"
import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'
import axios from 'axios'

const store = useMainStore()

const { userData } = storeToRefs(store)

const logout = () => {
    axios.post('logout').then(response => {
        location.reload()
    })
}

</script>

<template>
    <div class="bg-gray-900 flex flex-col border-r border-r-slate-700 text-slate-100">
        <div class="flex justify-start items-center px-5 pb-5 m-5 border-b-2 border-b-slate-500">
            <img :src="userData.user.profile_photo ? '/storage/' + userData.user.profile_photo : '/storage/default_profile.png'" alt="" class="rounded-full mr-5" style="width: 60px; height: 60px; object-fit: cover;">
            <div class="font-bold text-xl">{{ userData.user.name }}</div>
        </div>
        <div class="flex-1 flex flex-col justify-between p-5">
            <div class="flex flex-col font-semibold text-lg text-slate-300">
                <span class="mb-5 cursor-pointer" @click="store.homeFun">home</span>
                <span class="mb-5 cursor-pointer">saved message</span>
                <span class="mb-5 cursor-pointer" @click="store.editToggleFun">setting</span>
                <span class="mb-5 cursor-pointer" @click="store.searchFriFun">add friend</span>
                <span class="mb-5 cursor-pointer flex items-center" @click="store.notiFun">notifications <div v-if="userData.unreadNotiCount" class="bg-sky-600 rounded-xl flex justify-center items-center w-6 h-4 text-xs ms-1 text-white">{{userData.unreadNotiCount}}</div></span>
                <span class="mb-5 cursor-pointer" @click="store.friListToggleFun">friend list</span>
            </div>
            <DangerButton @click="logout">Logout</DangerButton>
        </div>
    </div>
</template>


