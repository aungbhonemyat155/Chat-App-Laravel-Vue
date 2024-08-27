<script setup>
import { useMainStore } from "@/stores/MainStore"
import { storeToRefs } from "pinia"
import { onMounted, ref } from 'vue'
import { useIntersectionObserver } from '@vueuse/core'
import axios from "axios"

const props = defineProps({
    numbers: Array
})

const store = useMainStore()

const { friendLists, friendIndex, messageNoti } = storeToRefs(store)


const messageToggle = (id, index) => {
    store.contentBoxToggle(id, index)
    if(messageNoti.value[`${id}`]){
        delete messageNoti.value[`${id}`]

        axios.get("message/read/"+id).catch(error => {
            console.log(error)
        })
    }
}

const target = ref(null)

const truncateMessage = (message) => {
    if (message.length <= 30) {
        return message;
    } else {
        return message.slice(0, 30) + "     ...";
    }
}

const { stop } = useIntersectionObserver(
    target,
    ([{ isIntersecting }], observerElement) => {
        if(isIntersecting){
            if(friendLists.value.next_page_url){
                axios.get(`/friends/lists?page=${friendLists.value.current_page+1}`).then(response => {
                    response.data.data.map(item => {
                        if(item.last_message){
                            item.last_message = JSON.parse(item.last_message)
                        }
                        return item;
                    })

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
    <section >
        <div class="flex items-center p-1 hover:bg-gray-800" @click="store.saveMessageToggle()">
            <img src="/storage/inbox.svg" alt="" class="rounded-full mr-5" style="width: 50px; height: 50px; object-fit: cover;">
            <div>
                <span class="font-bold">Save Messages</span>
            </div>
        </div>
        <div v-for="(item,index) in friendLists.data" :key="index">
            <div v-if="item.is_approve" class="grid grid-cols-12 p-1 hover:bg-gray-800" :class="{'bg-gray-700' : index == friendIndex}" @click="messageToggle(item.friend_id, index)">
                <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/user3.svg'" alt="" class=" rounded-full col-span-2 2xl:col-span-3" style="width: 50px; height: 50px; object-fit: cover;">
                <div class="flex flex-col mt-1 col-span-9 2xl:col-span-8">
                    <span class="text-white font-bold">{{ item.name }}</span>
                    <span v-if="item.last_message" class="text-slate-500 text-sm">{{ truncateMessage(item.last_message.message) }}</span>
                </div>
                <div v-if="messageNoti[`${item.friend_id}`]" class="bg-blue-700 rounded-full w-min h-min p-1 px-3 self-center col-span-1 text-center text-xs font-bold">{{ messageNoti[`${item.friend_id}`].length }}</div>
            </div>
        </div>
        <div ref="target" class="-translate-y-48"></div>
    </section>
</template>
