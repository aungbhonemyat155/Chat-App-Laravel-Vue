<script setup>
import { useMainStore } from "@/stores/MainStore";
import { onMounted, ref } from 'vue'
import { storeToRefs } from "pinia";
import { useIntersectionObserver } from '@vueuse/core'
import TimeFormatter from "@/Functions/dateTimeFormatter"
import TextInput from "@/Components/TextInput.vue"
import axios from "axios";
import { ssrImportMetaKey } from "vite/runtime";

const store = useMainStore()

const { friendLists, friendIndex, userData, tempMessages } = storeToRefs(store)

const friendId = ref(friendLists.value.data[friendIndex.value].friend_id);

const messageBox = ref("")

const target = ref(null)

const dropDownToggle = ref(false)

const forwardModal = ref(false)
const friendListTarget = ref(null)

const messageSettingModal = ref(false)
const messageModalX = ref(null)
const messageModalY = ref(null)

const modalMessageId = ref(null)
const modalMessageText = ref(null)
const modalFromUser = ref(null)

const messageModal = ref(null)

const popUpModalToggle = ref(false)
const popUpModalText = ref(null)

const changeDateFormat = (dateData) => {
    const formatter = new TimeFormatter(dateData);
    return formatter.getTime();
}

const sendFun = () => {
    let trimmedStr = messageBox.value.trim()
    messageBox.value = ""
    if(trimmedStr){
        axios.post(`send/message/${friendId.value}`,{
            'message' : trimmedStr,
            'friend_list_id' : friendLists.value.data[friendIndex.value].friend_list_id}
        ).then(response => {
            //update the updated_at of friendLists
            friendLists.value.data[friendIndex.value].updated_at = response.data.friend_list.updated_at;

            //push the message to tempMessage
            let temp = [ response.data.message, ...tempMessages.value.data ]
            store.pushMessage(temp)

            //assign latest message's info
            let friendData = friendLists.value.data[friendIndex.value];
            friendData.latest_message_created_at = response.data.message.created_at;
            friendData.latest_message_from_user_id = response.data.message.from_user_id;
            friendData.latest_message_id = response.data.message.id;
            friendData.latest_message_text = response.data.message.message;
            friendData.latest_message_to_user_id = response.data.message.to_user_id;

            if(friendIndex.value != 0){
                let removedValue = friendLists.value.data.splice(friendIndex.value, 1)[0];
                friendLists.value.data.unshift(removedValue)
                friendIndex.value = 0
            }
        }).catch(error => {
            console.log(error);
        })
    }
}

const messageMenuFun = (event) => {
    if (event.target.classList.contains('messageContainer') || event.target.classList.contains('dateContainer')) {
        if(event.target.classList.contains('dateContainer')){
            modalMessageId.value = event.target.parentNode.id
            modalMessageText.value = event.target.parentNode.childNodes[0].nodeValue.trim();
            modalFromUser.value = event.target.parentNode.getAttribute('data-from-user-id');
        }else{
            modalMessageId.value = event.target.id;
            modalMessageText.value = event.target.childNodes[0].nodeValue.trim();
            modalFromUser.value = event.target.getAttribute('data-from-user-id');
        }

        //information of scrollable element
        const sectionElement = document.getElementById("messageBlock");
        const rect = sectionElement.getBoundingClientRect();
        const sectionX = rect.left + window.scrollX; // X coordinate of scrollable element
        const sectionY = rect.top + window.scrollY;  // Y coordinate of scrollable element
        const sectionWidth = sectionElement.offsetWidth; //widht of scrollable element
        const sectionHeight = sectionElement.offsetHeight; //height of scrollable element

        //information of modal element
        messageModal.value.classList.remove('hidden');
        const modalWidth = messageModal.value.offsetWidth;
        const modalHeight = messageModal.value.offsetHeight;

        //calculate and assign the x and y value of where modal should appear
        if(sectionHeight + sectionY < event.clientY + modalHeight){
            messageModalY.value = event.clientY - modalHeight;
        }else{
            messageModalY.value = event.clientY;
        }

        if(sectionX + sectionWidth < event.clientX + modalWidth){
            messageModalX.value = event.clientX - sectionX - modalWidth;
        }else{
            messageModalX.value = event.clientX - sectionX;
        }

        messageSettingModal.value = true
    }
}

const messageForwardToggle = () => {
    forwardModal.value = true
    messageSettingModal.value = false;
}

const messageForward = (id, friend_list_id, index) => {
    axios.post(`send/message/${id}`,{
        'message' : modalMessageText.value,
        'friend_list_id' : friend_list_id}
    ).then(response => {
        forwardModal.value = false
        //pop up notification about forward success
        popUpModalText.value = 'Message Forwarded'
        popUpModalToggle.value = true
        setTimeout(() => {
            popUpModalToggle.value = false;
        }, 1100);

        if(friendLists.value.data[index].messages){
            friendLists.value.data[index].messages.data.unshift(response.data.message)
        }

        //assign latest message's info
        let friendData = friendLists.value.data[index];
        friendData.latest_message_created_at = response.data.message.created_at;
        friendData.latest_message_from_user_id = response.data.message.from_user_id;
        friendData.latest_message_id = response.data.message.id;
        friendData.latest_message_text = response.data.message.message;
        friendData.latest_message_to_user_id = response.data.message.to_user_id;

        if(index != 0){
            let removedValue = friendLists.value.data.splice(index, 1)[0];
            friendLists.value.data.unshift(removedValue)
            friendIndex.value = friendIndex.value+1
        }
    }).catch(error => {
        console.log(error);
    })
}

const forwardSaveMessage = () => {
    axios.post('save-message',{
        'message': modalMessageText.value
    }).then(response => {
        messageSettingModal.value = false

        popUpModalText.value = 'Message Forwarded to SaveMessages'
        popUpModalToggle.value = true
        setTimeout(() => {
            popUpModalToggle.value = false;
        }, 1100);
    }).catch(error => {
        console.log(error);
    })
}

const messageDeleteForYou = () => {
    axios.get(`message/delete/${userData.value.user.id}/${modalMessageId.value}`).then(response => {
        let updatedMessages = tempMessages.value.data.map(item => {
            if(item.id == response.data.data.id){
                item = response.data.data;
            }

            return item;
        })
        //updated messages to tempMessages
        store.pushMessage(updatedMessages)

        if(modalMessageId.value == friendLists.value.data[friendIndex.value].latest_message_id){
            //update the latest message
            tempMessages.value.data.every((element, index) => {
                if((element.from_user_id == userData.value.user.id && !element.from_user_delete) || (element.to_user_id == userData.value.user.id && !element.to_user_delete)){
                    friendLists.value.data[friendIndex.value].latest_message_created_at = element.created_at;
                    friendLists.value.data[friendIndex.value].latest_message_from_user_id = element.from_user_id;
                    friendLists.value.data[friendIndex.value].latest_message_id = element.id;
                    friendLists.value.data[friendIndex.value].latest_message_text = element.message;
                    friendLists.value.data[friendIndex.value].latest_message_to_user_id = element.to_user_id;

                    return false;
                }

                return true;
            })

            //sort the friendLists array
            const sortedArray = arraySort(friendLists.value.data);
            friendLists.value.data = sortedArray;

            //update the friend Index value
            for (let i = 0; i < sortedArray.length; i++) {
                if(sortedArray[i].friend_list_id == response.data.data.friend_lists_id){
                    friendIndex.value = i;
                    break;
                }
            }
        }

        //closing the message menu model
        messageSettingModal.value = false

    }).catch(error => {
        console.log(error);
    })
}

const arraySort = (data) => {
    data.sort((a, b) => {
        const dateA = a.latest_message_created_at ? new Date(a.latest_message_created_at) : new Date(a.updated_at);
        const dateB = b.latest_message_created_at ? new Date(b.latest_message_created_at) : new Date(b.updated_at);

        // Sort in descending order (most recent date first)
        return dateB - dateA;
    });

    return data;
}


const messageDeleteForEveryone = () => {
    axios.get(`message/delete/${modalMessageId.value}`).then(response => {
        const friList = friendLists.value.data[friendIndex.value];

        //update the temp message
        let filteredMessage = tempMessages.value.data.filter(item => {
            return item.id != modalMessageId.value
        })
        store.pushMessage(filteredMessage);

        //checking latest message need to update?
        if(modalMessageId.value == friList.latest_message_id){
            //updating the latest message data
            tempMessages.value.data.every((element, index) => {
                if((element.from_user_id == userData.value.user.id && !element.from_user_delete) || (element.to_user_id == userData.value.user.id && !element.to_user_delete)){
                    friList.latest_message_created_at = element.created_at;
                    friList.latest_message_from_user_id = element.from_user_id;
                    friList.latest_message_id = element.id;
                    friList.latest_message_text = element.message;
                    friList.latest_message_to_user_id = element.to_user_id;

                    return false;
                }

                return true;
            })

            //sorting the friendLists
            const sortedArray = arraySort(friendLists.value.data);
            friendLists.value.data = sortedArray;

            //correcting the frined index
            for (let i = 0; i < sortedArray.length; i++) {
                if(sortedArray[i].friend_list_id == response.data.friend_lists_id){
                    friendIndex.value = i;
                    break;
                }
            }
        }

        messageSettingModal.value = false;


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

const { secStop } = useIntersectionObserver(
    friendListTarget,
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

const menuFun = () => {
    dropDownToggle.value = !dropDownToggle.value
}

</script>

<template>
    <section class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9 border-l border-l-slate-800 bg-gray-900 flex flex-col h-screen relative">
        <div class="basis-[7%] bg-gray-900 border-b border-b-slate-800 flex justify-between items-center">
            <div class="flex items-center">
                <div class="ml-3 md:hidden inline" @click="store.backHome()">
                    <button><i class="fa-solid fa-chevron-left"></i></button>
                </div>
                <img v-if="friendIndex != null" :src="friendLists.data[friendIndex].profile_photo ?
                    '/storage/' + friendLists.data[friendIndex].profile_photo :
                    '/storage/user3.svg'"
                    alt="User Image"
                    class="ml-3 w-10 h-10 rounded-full object-cover md:hidden inline"
                >
                <span v-if="friendIndex != null" class="ml-3">{{ friendLists.data[friendIndex].name }}</span>
            </div>
            <div class="mr-5 relative">
                <button @click="menuFun()" class="text-lg"><i class="fa-solid fa-ellipsis"></i></button>
                <div v-if="dropDownToggle" class="z-20 bg-gray-700 shadow-md px-4 absolute top-8 rounded-lg right-0 mt-2 w-48 flex flex-col">
                    <span class="mt-2 py-2">testing one</span>
                    <span class="py-2">testing two</span>
                    <span class="mb-2 py-2">testign three</span>
                </div>
                <div v-if="dropDownToggle" class="fixed inset-0 z-10" @click="menuFun()"></div>
            </div>
        </div>
        <div @click.right.prevent="messageMenuFun($event)" v-if="tempMessages.data.length > 0" class="basis-[86%] flex flex-col-reverse overflow-y-scroll" id="messageBlock">
            <div v-for="(message, index) in tempMessages.data" :key="index">
                <div class="text-center" v-if="tempMessages.data.length-1 === index">
                    <span class="bg-gray-800 px-5 py-1 text-sm rounded-full">{{ changeDateFormat(message.created_at)[0] }}</span>
                </div>
                <div v-if="(message.from_user_id == userData.user.id && !message.from_user_delete) || (message.to_user_id == userData.user.id && !message.to_user_delete)" class="flex my-2 mx-1" :class="{'flex-row-reverse' : userData.user.id === message.from_user_id}">
                    <div :id="message.id"
                    class="w-max max-w-lg rounded-lg px-3 p-2 pb-1 messageContainer"
                    :class="{'bg-blue-800' : userData.user.id === message.from_user_id,
                    'bg-gray-800' : userData.user.id !== message.from_user_id}"
                    :data-from-user-id="message.from_user_id">
                        {{ message.message }}
                        <div class="text-xs text-end text-gray-500 dateContainer">{{ changeDateFormat(message.created_at)[1] }}</div>
                    </div>

                </div>

                <div class="text-center" v-if="tempMessages.data[index-1] && changeDateFormat(tempMessages.data[index-1].created_at)[0] !== changeDateFormat(message.created_at)[0]">
                    <span class="bg-gray-800 px-5 py-1 text-sm rounded-full">{{ changeDateFormat(tempMessages.data[index-1].created_at)[0] }}</span>
                </div>
            </div>

            <!-- message setting modal  -->
            <div ref="messageModal" :class="{'hidden' : !messageSettingModal}" class="w-min absolute h-min bg-gray-700 p-2 px-3 rounded-xl shadow" id="modalBlock" :style="{ top: `${messageModalY}px`, left: `${messageModalX}px`, zIndex: 1000}">
                <div class="w-max pt-1 py-2 border-b border-b-gray-600 cursor-pointer text-red-400" @click="messageDeleteForYou">delete for you</div>
                <div v-if="modalFromUser == userData.user.id" class="w-max py-2 border-b border-b-gray-600 cursor-pointer text-red-400" @click="messageDeleteForEveryone">delete for everyone</div>
                <div class="w-max py-2 border-b border-b-gray-600 cursor-pointer" @click="messageForwardToggle()">forward</div>
                <div class="w-max pt-2 pb-1 cursor-pointer" @click="forwardSaveMessage()">save-messages</div>
            </div>

            <div v-if="messageSettingModal" @click.right.stop.prevent="messageSettingModal = false" class="fixed inset-0 z-10" @click="messageSettingModal = false"></div>
            <!-- message setting modal  -->

            <div ref="target" class="text-center">
                <div v-if="tempMessages.next_page_url" class="lds-dual-ring"></div>
                <div v-else class="my-5">You've caught up on all messages.....</div>
            </div>

            <!-- pop up modal  -->
            <div v-if="popUpModalToggle" class="absolute inset-0 flex justify-center items-center">
                <div class="bg-black px-5 bg-opacity-50 p-2 rounded-xl">{{ popUpModalText }}</div>
            </div>
            <!-- pop up modal  -->
        </div>

        <div v-else class="basis-[86%] flex justify-center items-start">
            <div class="p-3 px-7 mt-10 rounded-xl bg-gray-700 font-semibold">There's no message yet! Start messaging...</div>
        </div>

        <form @submit.prevent="sendFun" class="basis-[7%] bg-gray-900 flex items-center p-3">
            <TextInput v-model="messageBox" placeholder="Text Here...." class="flex-1 border-none outline-none"></TextInput>
            <button class="px-2 hover:bg-slate-700 py-2">Send <span><i class="fa-regular fa-paper-plane"></i></span></button>
        </form>

        <!-- forward message modal  -->
        <div v-if="forwardModal" class="absolute bg-gray-800 bg-opacity-50 inset-0 flex justify-center items-center">
            <div class="bg-gray-900 rounded-xl z-50 flex flex-col h-screen w-6/12 overflow-y-scroll p-5 py-3">
                <div v-for="(item, index) in friendLists.data" :key="index" class="grid grid-cols-12 my-2 justify-center items-center">
                    <div class="col-span-2 mx-auto">
                        <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/user3.svg'" alt="" class=" rounded-full col-span-2 2xl:col-span-3" style="width: 40px; height: 40px; object-fit: cover;">
                    </div>
                    <div class="col-span-7 text-center">{{ item.name }}</div>
                    <div class="col-span-3 text-center">
                        <button @click="messageForward(item.friend_id, item.friend_list_id, index)" class="px-2 hover:bg-slate-700 py-2 rounded-lg bg-gray-800">Send</button>
                    </div>
                </div>
                <div ref="friendListTarget"></div>
            </div>
            <div @click="forwardModal = false" class="fixed inset-0 z-40"></div>
        </div>
        <!-- forward message modal  -->
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
