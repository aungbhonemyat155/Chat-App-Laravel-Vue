<script setup>
import { useMainStore } from "@/stores/MainStore";
import { onMounted, ref, watch } from 'vue'
import { storeToRefs } from "pinia";
import { useIntersectionObserver } from '@vueuse/core'
import TimeFormatter from "@/Functions/dateTimeFormatter"
import TextInput from "@/Components/TextInput.vue"
import axios from "axios";
import MessageFormatter from "@/Functions/messageFormatter";

const store = useMainStore()

const { friendLists, friendIndex, userData, tempMessages } = storeToRefs(store)

const messageBox = ref("")

const target = ref(null)

const dropDownToggle = ref(false)

const sendFun = () => {
    let trimmedStr = messageBox.value.trim()
    axios.post(`send/message/${friendLists.value.data[friendIndex.value].friend_id}`,{
        'message' : trimmedStr,
        'friend_list_id' : friendLists.value.data[friendIndex.value].friend_list_id}
    ).then(response => {
        let formatter = new TimeFormatter(response.data.message.created_at)
        response.data.message.created_at = formatter.getTime()

        let temp = [ response.data.message, ...tempMessages.value.data ]
        store.pushMessage(temp)

        friendLists.value.data[friendIndex.value].last_message = response.data.message
        if(friendIndex.value != 0){
            let removedValue = friendLists.value.data.splice(friendIndex.value, 1)[0];
            friendLists.value.data.unshift(removedValue)
            friendIndex.value = 0
        }

        messageBox.value = ""
    }).catch(error => {
        console.log(error);
    })
}


const { stop } = useIntersectionObserver(
    target,
    ([{ isIntersecting }], observerElement) => {
        if(isIntersecting && tempMessages.value.next_page_url){
            axios.get(`messages/${friendLists.value.data[friendIndex.value].friend_id}?page=${tempMessages.value.current_page+1}`).then(response => {
                let formatter = new MessageFormatter(response.data.data)
                response.data.data = formatter.changeMessageDate()

                let temp = [...tempMessages.value.data, ...response.data.data]

                response.data.data = temp
                store.setMessage(response.data)
            }).catch(error => {
                console.log(error);
            })
        }
    },
)

const menuFun = () => {
    dropDownToggle.value = !dropDownToggle.value
}

const backButtonFun = () => {
    store.backHome();
}

</script>

<template>
    <section class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9 border-l border-l-slate-800 bg-gray-900 flex flex-col h-screen">
        <div class="basis-[7%] bg-gray-900 border-b border-b-slate-800 flex justify-between items-center">
            <div class="flex items-center">
                <div class="ml-3 md:hidden inline" @click="backButtonFun()">
                    <button><i class="fa-solid fa-chevron-left"></i></button>
                </div>
                <img v-if="friendIndex" :src="friendLists.data[friendIndex].profile_photo ?
                    '/storage/' + friendLists.data[friendIndex].profile_photo :
                    '/storage/user3.svg'"
                    alt="User Image"
                    class="ml-3 w-10 h-10 rounded-full object-cover md:hidden inline"
                >
                <span v-if="friendIndex" class="ml-3">{{ friendLists.data[friendIndex].name }}</span>
            </div>
            <div class="mr-5 relative">
                <button @click="menuFun()" class="text-lg"><i class="fa-solid fa-ellipsis"></i></button>
                <div v-if="dropDownToggle" class="z-30 bg-gray-700 shadow-md px-4 absolute top-8 rounded-lg right-0 mt-2 w-48 flex flex-col">
                    <span class="mt-2 py-2">testing one</span>
                    <span class="py-2">testing two</span>
                    <span class="mb-2 py-2">testign three</span>
                </div>
                <div v-if="dropDownToggle" class="fixed inset-0 z-20" @click="menuFun()"></div>
            </div>
        </div>
        <div v-if="tempMessages.data.length > 0" class="basis-[86%] flex flex-col-reverse overflow-y-scroll">
            <div v-for="(message, index) in tempMessages.data" :key="index">
                <div class="text-center" v-if="tempMessages.data.length-1 === index">
                    <span class="bg-gray-800 px-5 py-1 text-sm rounded-full">{{ message.created_at[0] }}</span>
                </div>
                <div class="flex my-2 mx-1" :class="{'justify-end' : userData.user.id === message.from_user_id}">
                    <div class="w-max max-w-lg rounded-lg px-3 p-2 pb-1" :class="{'bg-blue-800' : userData.user.id === message.from_user_id, 'bg-gray-800' : userData.user.id !== message.from_user_id}">
                        {{ message.message }}
                        <div class="text-xs text-end text-gray-500">{{ message.created_at[1] }}</div>
                    </div>
                </div>
                <div class="text-center" v-if="tempMessages.data[index-1] && tempMessages.data[index-1].created_at[0] !== message.created_at[0]">
                    <span class="bg-gray-800 px-5 py-1 text-sm rounded-full">{{ tempMessages.data[index-1].created_at[0] }}</span>
                </div>
            </div>
            <div ref="target" class="text-center">
                <div v-if="tempMessages.next_page_url" class="lds-dual-ring"></div>
                <div v-else class="my-5">You've caught up on all messages.....</div>
            </div>
        </div>
        <div v-else class="basis-[86%] flex justify-center items-start">
            <div class="p-3 px-7 mt-10 rounded-xl bg-gray-700 font-semibold">There's no message yet! Start messaging...</div>
        </div>

        <form @submit.prevent="sendFun" class="basis-[7%] bg-gray-900 flex items-center p-3">
            <TextInput v-model="messageBox" placeholder="Text Here...." class="flex-1 border-none outline-none"></TextInput>
            <button class="px-2 hover:bg-slate-700 py-2">Send <span><i class="fa-regular fa-paper-plane"></i></span></button>
        </form>
    </section>
</template>

<style scoped>


    .lds-dual-ring,
    .lds-dual-ring:after {
      box-sizing: border-box;
    }
    .lds-dual-ring {
      display: inline-block;
      width: 80px;
      height: 80px;
    }
    .lds-dual-ring:after {
      content: " ";
      display: block;
      width: 30px;
      height: 30px;
      margin: 8px;
      border-radius: 50%;
      border: 4px solid currentColor;
      border-color: currentColor transparent currentColor transparent;
      animation: lds-dual-ring 1.2s linear infinite;
    }
    @keyframes lds-dual-ring {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }


</style>
