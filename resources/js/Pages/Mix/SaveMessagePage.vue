<script setup>
import { useMainStore } from '@/stores/MainStore';
import { storeToRefs } from 'pinia';
import TextInput from '@/Components/TextInput.vue';
import { ref } from "vue"
import { useIntersectionObserver } from '@vueuse/core'
import TimeFormatter from '@/Functions/dateTimeFormatter';

const store = useMainStore()
const { tempMessages } = storeToRefs(store)

const messageBox = ref("");
const target = ref(null)
const dropDownToggle = ref(false)
const messageMenu = ref(false)

const sendFun = () => {
    let trimmedStr = messageBox.value.trim()
    messageBox.value = ""
    if(trimmedStr){
        axios.post("save-message",{ message: trimmedStr}).then(response => {

            let temp = tempMessages.value
            temp.data = [ response.data, ...temp.data ];

            store.setMessage(temp)
        }).catch(error => {
            console.log(error);
        })
    }
}

const changeDateFormat = (dateData) => {
    const formatter = new TimeFormatter(dateData);
    return formatter.getTime();
}

const dropDownFun = () => {
    dropDownToggle.value = !dropDownToggle.value
}

const messageMenuFun = (index) => {
    tempMessages.value.data[index].button = true
}

const { stop } = useIntersectionObserver(
    target,
    ([{ isIntersecting }], observerElement) => {
        if(isIntersecting && tempMessages.value.next_page_url){
            axios.get('save-messages?page='+(tempMessages.value.current_page+1)).then(response => {

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
    <section class="bg-gray-900 h-screen border-l border-l-slate-700">
        <div class="h-full flex flex-col">
            <div class="basis-[7%] bg-gray-900 border-b border-b-slate-800 flex justify-between items-center">
                <div class="flex items-center">
                    <div class="ml-3 md:hidden inline" @click="store.backHome()">
                        <button><i class="fa-solid fa-chevron-left"></i></button>
                    </div>
                    <img src="/storage/inbox.svg"
                        alt="save-message-icon"
                        class="ml-3 w-10 h-10 rounded-full object-cover md:hidden inline"
                    >
                    <span class="ml-3">Save Messages</span>
                </div>
                <div class="mr-5 relative">
                    <button @click="dropDownFun()" class="text-lg"><i class="fa-solid fa-ellipsis"></i></button>
                    <div v-if="dropDownToggle" class="z-30 bg-gray-700 shadow-md px-4 absolute top-8 rounded-lg right-0 mt-2 w-48 flex flex-col">
                        <span class="mt-2 py-2">testing one</span>
                        <span class="py-2">testing two</span>
                        <span class="mb-2 py-2">testign three</span>
                    </div>
                    <div v-if="dropDownToggle" class="fixed inset-0 z-20" @click="dropDownFun()"></div>
                </div>
            </div>
            <div v-if="tempMessages.data.length" class="basis-[86%] flex flex-col-reverse overflow-y-scroll">
                <div v-for="(message,index) in tempMessages.data" :key="index">
                    <div class="text-center w-full" v-if="tempMessages.data.length-1 === index">
                        <span class="bg-gray-800 px-5 py-1 text-sm rounded-full">{{ changeDateFormat(message.created_at)[0] }}</span>
                    </div>
                    <div class="flex my-2 mx-1 justify-end items-center">
                        <div class="z-30 w-32 mr-3 relative">
                            <div v-if="message.button" class="absolute bg-gray-700 p-2 px-3 rounded-xl right-0 bottom-0">
                                <div class=" pt-1 py-2 border-b border-b-gray-600 cursor-pointer text-red-400">delete</div>
                                <div class=" py-2 border-b border-b-gray-600 cursor-pointer">forward</div>
                                <div class="pt-2 pb-1 cursor-pointer">testing three</div>
                            </div>
                        </div>
                        <div v-if="message.button" class="fixed inset-0 z-20" @click="message.button = false"></div>
                        <div @click="messageMenuFun(index)" class="cursor-pointer mr-2 bg-gray-700 p-1 rounded-full text-xs"><i class="fa-solid fa-share"></i></div>
                        <div @click.right.prevent="messageMenuFun(index)" class="max-w-lg rounded-lg px-3 p-2 pb-1 bg-gray-800">
                            {{ message.message }}
                            <div class="text-xs text-end text-gray-500">{{ changeDateFormat(message.created_at)[1] }}</div>
                        </div>
                    </div>
                    <div class="text-center" v-if="tempMessages.data[index-1] && changeDateFormat(tempMessages.data[index-1].created_at)[0] !== changeDateFormat(message.created_at)[0]">
                        <span class="bg-gray-800 px-5 py-1 text-sm rounded-full">{{ changeDateFormat(tempMessages.data[index-1].created_at)[0] }}</span>
                    </div>
                </div>
                <div ref="target" class="text-center self-center">
                    <div v-if="tempMessages.next_page_url" class="lds-dual-ring"></div>
                    <div v-else class="my-5">You've caught up on all messages.....</div>
                </div>
            </div>
            <div v-else class="w-full flex justify-center items-center basis-[93%]">
                <span class="bg-gray-700 p-3 rounded-lg">There's no save data yet! Try to save something...</span>
            </div>
            <form @submit.prevent="sendFun" class="basis-[7%] bg-gray-900 flex items-center p-3">
                <TextInput v-model="messageBox" placeholder="Text Here...." class="flex-1 border-none outline-none"></TextInput>
                <button class="px-2 hover:bg-slate-700 py-2">Send <span><i class="fa-regular fa-paper-plane"></i></span></button>
            </form>
        </div>
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
