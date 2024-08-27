<script setup>
import { useMainStore } from "@/stores/MainStore";
import { computed, ref } from "vue";
import TextInput from "@/Components/TextInput.vue"
import axios from "axios";
import { storeToRefs } from "pinia";

const store = useMainStore()

const { friendLists, userData, notifications } = storeToRefs(store)

const key = ref("")

const data = ref(null)

const modalToggle = ref(false)

const modalContent = ref(null)

const emptyModal = ref(false)

let emptyModalContent = null

const searchFun = () => {
    if(key.value){
        axios.get("/users",{
            params: {
                "key": key.value
            }
        }).then((response) => {
            if(response.data.length == 0){
                emptyModal.value = true
                emptyModalContent = key.value
                data.value = response.data
            }else{
                emptyModal.value = false
                data.value = response.data
            }
        }).catch((error) => {
            console.log(error);
        })
    }else{
        data.value = null
    }
}

const sendFriReq = (friend_id, index) => {
    axios.get('/friend/request/'+friend_id).then((response) => {
        if(response.data.status){
            data.value[index].friend_list_id = response.data.data.friend_list_id
            data.value[index].first_user_id = response.data.data.first_user_id
            data.value[index].second_user_id = response.data.data.second_user_id
            data.value[index].is_approve = response.data.data.is_approve

            let temp = friendLists.value
            temp.data = [ response.data.data, ...friendLists.value.data ]
            store.setFriendLists(temp)
        }else{
            modalContent.value = response.data.message
            testing()
        }
    }).catch((error) => {
        console.log(error);
    })
}

const cancelFriReq = (friend_list_id, index) => {
    axios.get('/friend/request/cancel/'+friend_list_id).then((response) => {
        if(response.data.status){
            data.value[index].friend_list_id = null
            data.value[index].first_user_id = null
            data.value[index].second_user_id = null
            data.value[index].is_approve = null

            let temp = friendLists.value.data.filter((item) => item.friend_list_id != friend_list_id)
            let secTemp = friendLists.value
            secTemp.data = temp
            store.setFriendLists(secTemp)
        }else{
            modalContent.value = response.data.message
            testing()
        }
    }).catch((error) => {
        console.log(error);
    })
}

const friendAccept = (friend_list_id, index) => {
    axios.get("/friend/accept/"+friend_list_id).then((response) => {
        if(response.data.status){
            let filtered = notifications.value.data.filter(item => item.id != response.data.notiId)
            store.setNoti(filtered)

            let temp = friendLists.value.data.map((item) => {
                if(item.friend_list_id == friend_list_id){
                    item.is_approve = true
                }
                return item
            })
            store.setFriendLists(temp)

            data.value[index].is_approve = true
        }else{
            modalContent.value = response.data.message
            modalToggle.value = true
            setTimeout(() => {
                modalToggle.value = false;
            }, 1000);
        }
    }).catch((error) => {
        console.log(error);
    })
}

const deleteReq = (friend_list_id, index) => {
    axios.get('friend/request/delete/'+friend_list_id).then(response => {
        if(response.data.status){
            let filtered = notifications.value.filter(item => item.id != response.data.notiId)
            store.setNoti(filtered)

            let temp = friendLists.value.data.filter(item => item.friend_list_id != friend_list_id)
            store.setFriendLists(temp)

            data.value[index].is_delete = true
        }else{
            modalContent.value = response.data.message
            modalToggle.value = true
            setTimeout(() => {
                modalToggle.value = false;
            }, 1000);
        }
    }).catch(error => {
        console.log(error);
    })
}

const testing = () => {
    modalToggle.value = true
    setTimeout(() => {
        modalToggle.value = false;
    }, 1000);
}
</script>

<template>
    <section class="w-full h-screen flex justify-center items-start relative">
        <div class="bg-gray-900 w-full h-full flex flex-col">
            <form class="basis-[5%] sm:basis-[10%] flex justify-center items-center p-4 sm:p-0" @submit.prevent="searchFun">
                <div class="basis-[5%] sm:ms-5 md:hidden" @click="store.backToSetting">
                    <button><i class="fa-solid fa-chevron-left"></i></button>
                </div>
                <TextInput class="basis-[95%] sm:me-12 md:me-0 sm:w-5/6 md:w-11/12 lg:w-4/6" placeholder="Find with name or email..." v-model="key" :class="'bg-gray-800 text-slate-200 outline-none border-none border-b border-b-slate-400'"></TextInput>
            </form>
            <div v-if="data && data.length" class="basis-[95%] sm:basis-[90%] overflow-y-scroll">
                <div class="flex flex-col items-center">
                    <div v-for="(item, index) in data" :key="index" class="grid grid-cols-12 p-1 sm:p-2 items-center text-center text-slate-200 bg-gray-800 sm:my-2 border-b border-b-gray-700 sm:border-b-0 sm:rounded-xl py-3 sm:w-5/6 w-full md:w-11/12 lg:w-4/6">
                        <div class="flex items-center ms-1 sm:ms-4" :class="{'col-span-9' : userData.user.id == item.first_user_id || item.first_user_id == null || (item.second_user_id == userData.user.id && item.is_delete) || item.is_approve, 'col-span-8' : userData.user.id == item.second_user_id && !item.is_approve && !item.is_delete}">
                            <span>
                                <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/user.svg'" alt="" class="rounded-full mr-5 w-10 lg:w-12 xl:w-16">
                            </span>
                            <span class="text-md font-semibold">
                                {{ item.name }}
                            </span>
                        </div>
                        <span v-if="item.is_delete" class="col-span-3 font-semibold text-sm sm:text-base">
                            <button class="px-2 py-2 bg-blue-600 bg-opacity-50 rounded-md  min-w-4/6 shadow-inner" v-if="userData.user.id == item.second_user_id" @click="sendFriReq(item.friend_id,index)">Add Friend</button>
                            <button class="px-2 py-2 bg-slate-600 bg-opacity-50 rounded-md min-w-4/6 shadow-inner" v-if="userData.user.id == item.first_user_id">Delete</button>
                        </span>
                        <span v-if="!item.friend_list_id || (item.first_user_id == userData.user.id && !item.is_approve && !item.delete)" class="col-span-3 font-semibold text-sm sm:text-base">
                            <button class="px-2 py-2 bg-slate-600 bg-opacity-50 rounded-md min-w-4/6 shadow-inner" v-if="item.friend_list_id && !item.is_approve && userData.user.id == item.first_user_id && !item.is_delete" @click="cancelFriReq(item.friend_list_id, index)">Cancel Request</button>
                            <button class="px-2 py-2 bg-blue-600 bg-opacity-50 rounded-md  min-w-4/6 shadow-inner" v-if="!item.friend_list_id" @click="sendFriReq(item.friend_id,index)">Add Friend</button>
                        </span>
                        <span v-if="userData.user.id == item.second_user_id && !item.is_approve && !item.is_delete" class="col-span-4 flex justify-center font-semibold text-sm sm:text-base">
                            <button class="px-2 py-2 bg-blue-600 bg-opacity-50 rounded-md  min-w-4/6 shadow-inner me-3" @click="friendAccept(item.friend_list_id, index)">Accept</button>
                            <button class="px-2 py-2 bg-slate-600 bg-opacity-50 rounded-md min-w-4/6 shadow-inner" @click="deleteReq(item.friend_list_id, index)">Delete</button>
                        </span>
                        <span v-if="item.is_approve" class="col-span-3 font-semibold text-sm sm:text-base">
                            <button class="px-2 py-2 bg-slate-600 bg-opacity-50 rounded-md min-w-4/6 shadow-inner" v-if="item.friend_list_id && item.is_approve">Friend</button>
                        </span>
                    </div>
                </div>
            </div>
            <div v-if="emptyModal" class="text-center text-xl my-8">there is no user name or email like '<span class="font-bold text-white">{{ emptyModalContent }}</span>'</div>
        </div>
        <div v-if="modalToggle" class="absolute top-36 text-slate-600 font-semibold bg-slate-300 p-3 rounded-xl">
            {{ modalContent }}
        </div>
    </section>
</template>
