<script setup>
import { useMainStore } from "@/stores/MainStore"
import { storeToRefs } from "pinia"
import { ref } from 'vue'
import { useIntersectionObserver } from '@vueuse/core'
import axios from "axios"

const props = defineProps({
    numbers: Array
})

const store = useMainStore()

const { friendLists } = storeToRefs(store)

const messageToggle = (messages) => {
    store.contentBoxToggle()

    store.pushMessages(messages)
}

const target = ref(null)
const targetIsVisible = ref(false)

const { stop } = useIntersectionObserver(
    target,
    ([{ isIntersecting }], observerElement) => {
        if(isIntersecting){
            if(friendLists.value.next_page_url){
                axios.get(`/friends/lists?page=${friendLists.value.current_page+1}`).then(response => {
                    let temp = [ ...friendLists.value.data, ...response.data.data ]
                    response.data.data = temp
                    store.setFriendLists(response.data)
                }).catch(error => {
                    console.log(error);
                })
            }
        }
    },
)
</script>

<template>
    <section>
        <div v-for="(item,index) in friendLists.data" :key="index">
            <div v-if="item.is_approve" class="flex items-start mb-3" @click="messageToggle(item.messages)">
                <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/user3.svg'" alt="" class="mr-5 rounded-full" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex flex-col mt-1">
                    <span class="text-white font-bold">{{ item.name }}</span>
                    <span class="text-slate-500 text-sm">this is testing</span>
                </div>
            </div>
        </div>
        <div class="flex items-center mb-3">
            <img src="/storage/inbox.svg" alt="" class="rounded-full mr-5" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
                <span class="font-bold">Save Messages</span>
            </div>
        </div>
        <div ref="target" class="-translate-y-48"></div>
    </section>
</template>
