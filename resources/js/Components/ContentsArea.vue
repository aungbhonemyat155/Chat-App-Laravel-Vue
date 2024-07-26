<script setup>
import { useMainStore } from "@/stores/MainStore";
import { onMounted, ref, watch } from 'vue'
import { storeToRefs } from "pinia";
import { useIntersectionObserver } from '@vueuse/core'
import TextInput from "@/Components/TextInput.vue"
import axios from "axios";

const store = useMainStore()

const { friendLists, friendIndex, userData, tempMessages } = storeToRefs(store)

const messageBox = ref("")

const target = ref(null)

const sendFun = () => {
    let trimmedStr = messageBox.value.trim()
    axios.post(`send/message/${friendLists.value.data[friendIndex.value].friend_id}`,{
        'message' : messageBox.value,
        'friend_list_id' : friendLists.value.data[friendIndex.value].friend_list_id}
    ).then(response => {
        console.log(response.data);
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
                let temp = [...tempMessages.value.data, ...response.data.data]

                response.data.data = temp
                store.setMessage(response.data)
            }).catch(error => {
                console.log(error);
            })
        }
    },
)

</script>

<template>
    <section class="col-span-9 border-l border-l-slate-800 bg-gray-900 flex flex-col h-screen">
        <div v-if="tempMessages.data.length > 0" class="basis-[93%] flex flex-col-reverse overflow-y-scroll">
            <div v-for="(message, index) in tempMessages.data" :key="index" class="m-2 p-2 w-fit max-w-lg rounded-xl" :class="{'self-end bg-blue-900' : userData.user.id == message.from_user_id, 'bg-slate-700' : userData.user.id != message.from_user_id}">{{ message.message }}</div>
            <div ref="target" class="text-center">
                <div v-if="tempMessages.next_page_url" class="lds-dual-ring"></div>
                <div v-else class="my-5">You've caught up on all messages.....</div>
            </div>
        </div>
        <div v-else class="basis-[93%] flex justify-center items-start">
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
