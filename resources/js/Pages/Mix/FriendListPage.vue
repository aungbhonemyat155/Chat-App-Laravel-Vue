<script setup>
import { useMainStore } from "@/stores/MainStore";
import { storeToRefs } from "pinia";
import { onBeforeMount, ref } from "vue";
import axios from "axios";

const store = useMainStore()
const { userData, friendLists, notifications } = storeToRefs(store)

const friends = ref(true)
const urReq = ref(false)
const friReq = ref(false)
const modalToggle = ref(false)
const modalContent = ref('this is testing')

const offAll = () => {
    friends.value = false
    urReq.value = false
    friReq.value = false
}

const friFun = () => {
    offAll()
    friends.value = true
}

const urReqFun = () => {
    offAll()
    urReq.value = true
}

const friReqFun = () => {
    offAll()
    friReq.value = true
}

const friendAcceptFun = (friend_list_id) => {
    axios.get("/friend/accept/"+friend_list_id).then((response) => {
        console.log(response.data);
        if(response.data.status){
            let filtered = notifications.value.filter(item => item.id != response.data.notiId)
            store.setNoti(filtered)

            let temp = friendLists.value.map((item) => {
                if(item.friend_list_id == friend_list_id){
                    item.is_approve = true
                }
                return item
            })
            store.setFriendLists(temp)

            store.unreadNotiCountSet(userData.value.unreadNotiCount - 1)
        }else{
            modalContent.value = response.data.message
            showModal()
        }
    }).catch((error) => {
        console.log(error);
    })
}

const cancelFriReq = (friend_list_id) => {
    axios.get('/friend/request/cancel/'+friend_list_id).then((response) => {
        if(response.data.status){
            let temp = friendLists.value.filter((item) => item.friend_list_id != friend_list_id)
            store.setFriendLists(temp)
        }else{
            modalContent.value = response.data.message
            showModal()
        }
    }).catch((error) => {
        console.log(error);
    })
}

const unfriend = (friend_list_id) => {
    axios.get('unfriend/'+friend_list_id).then(response => {
        if(response.data.status){
            let filtered = notifications.value.filter(item => item.id != response.data.notiId)
            store.setNoti(filtered)

            let temp = friendLists.value.filter((item) => item.friend_list_id != friend_list_id)
            store.setFriendLists(temp)
        }else{
            modalContent.value = response.data.message
            showModal()
        }

    }).catch(error => {
        console.log(error);
    })
}

const deleteReq = (friend_list_id) => {
    axios.get('friend/request/delete/'+friend_list_id).then(response => {
        if(response.data.status){
            let filtered = notifications.value.filter(item => item.id != response.data.notiId)
            store.setNoti(filtered)

            let temp = friendLists.value.filter(item => item.friend_list_id != friend_list_id)
            store.setFriendLists(temp)
        }else{
            modalContent.value = response.data.message
            showModal();
        }
    }).catch(error => {
        console.log(error);
    })
}

const showModal = () => {
    modalToggle.value = true
    setTimeout(() => {
        modalToggle.value = false;
    }, 1000);
}

</script>

<template>
    <section class="w-full h-screen bg-slate-900 flex justify-center items-start relative">
        <div class=" bg-slate-800 p-5 basis-[50%] rounded-xl mt-5 h-5/6">
            <div class="flex justify-evenly text-slate-200">
                <span class="font-semibold pb-2 cursor-pointer" :class="{'border-b-blue-400 border-b-2' : friends}" @click="friFun">Your Friends</span>
                <span class="font-semibold pb-2 cursor-pointer" :class="{'border-b-blue-400 border-b-2' : urReq}" @click="urReqFun">Your Requests</span>
                <span class="font-semibold pb-2 cursor-pointer" :class="{'border-b-blue-400 border-b-2' : friReq}" @click="friReqFun">Friend Requests</span>
            </div>
            <div v-if="friends && friendLists">
                <div v-for="(item,index) in friendLists" :key="index" class="mt-4">
                    <div v-if="item.is_approve" class="grid grid-cols-12 items-center text-center">
                        <span class="col-span-3 block mx-auto">
                            <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/default_profile.png'" alt="" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                        </span>
                        <span class="col-span-6">{{ item.name }}</span>
                        <span class="col-span-3">
                            <button class="py-2 px-3 bg-slate-600 hover:bg-slate-500 rounded-lg text-sm text-white font-semibold me-4" @click="unfriend(item.friend_list_id)">Unfriend</button>
                        </span>
                    </div>
                </div>
            </div>
            <div v-if="urReq && friendLists">
                <div v-for="(item,index) in friendLists" :key="index" class="mt-4">
                    <div v-if="!item.is_approve && userData.user.id == item.first_user_id" class="grid grid-cols-12 items-center text-center">
                        <span class="col-span-3 block mx-auto">
                            <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/default_profile.png'" alt="" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                        </span>
                        <span class="col-span-6">{{ item.name }}</span>
                        <span class="col-span-3">
                            <button class="py-2 px-3 bg-slate-600 hover:bg-slate-500 rounded-lg text-sm text-white font-semibold me-4" @click="cancelFriReq(item.friend_list_id)">Cancel</button>
                        </span>
                    </div>
                </div>
            </div>
            <div v-if="friReq && friendLists">
                <div v-for="(item,index) in friendLists" :key="index" class="mt-4">
                    <div v-if="!item.is_approve && userData.user.id == item.second_user_id" class="grid grid-cols-12 items-center text-center">
                        <span class="col-span-3 block mx-auto">
                            <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/default_profile.png'" alt="" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                        </span>
                        <span class="col-span-5">{{ item.name }}</span>
                        <span class="col-span-4">
                            <button class="py-2 px-3 bg-blue-600 hover:bg-blue-400 rounded-lg text-sm text-white font-semibold me-4" @click="friendAcceptFun(item.friend_list_id)">Accept</button>
                            <button class="py-2 px-3 bg-slate-600 hover:bg-slate-400 rounded-lg text-sm text-white font-semibold me-4" @click="deleteReq(item.friend_list_id)">Delete</button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="modalToggle" class="absolute top-36 text-slate-600 font-semibold bg-slate-300 p-3 rounded-xl z-50">
            {{ modalContent }}
        </div>
    </section>
</template>
