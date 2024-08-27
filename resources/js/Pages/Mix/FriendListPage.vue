<script setup>
import { useMainStore } from "@/stores/MainStore";
import { storeToRefs } from "pinia";
import { onBeforeMount, ref } from "vue";
import { useIntersectionObserver } from '@vueuse/core'
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

            let temp = friendLists.value
            temp.data = friendLists.value.data.map((item) => {
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
            let temp = friendLists.value
            temp.data = friendLists.value.data.filter((item) => item.friend_list_id != friend_list_id)
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

            let temp = friendLists.value
            temp.data = friendLists.value.data.filter((item) => item.friend_list_id != friend_list_id)
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

            let temp = friendLists.value
            temp.data = friendLists.value.data.filter(item => item.friend_list_id != friend_list_id)
            store.setFriendLists(temp)
        }else{
            modalContent.value = response.data.message
            showModal();
        }
    }).catch(error => {
        console.log(error);
    })
}

const target = ref(null)

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

const showModal = () => {
    modalToggle.value = true
    setTimeout(() => {
        modalToggle.value = false;
    }, 1000);
}

</script>

<template>
    <section class="w-full h-screen bg-slate-900 flex justify-center items-start relative text-sm sm:text-base">
        <div class=" bg-slate-800 p-1 sm:p-3 pt-0 md:pt-0 lg:pt-2 md:p-3 lg:p-5 basis-[100%] sm:basis-[80%] md:basis-[100%] xl:basis-[60%] sm:rounded-xl sm:mt-5 h-full sm:h-5/6 flex flex-col">
            <div class="flex justify-evenly items-center text-slate-200 basis-[7%]">
                <button class="md:hidden" @click="store.backToSetting"><i class="fa-solid fa-chevron-left"></i></button>
                <span class="font-semibold p-3 cursor-pointer" :class="{'border-b-blue-400 border-b-2' : friends}" @click="friFun">Your Friends</span>
                <span class="font-semibold p-3 cursor-pointer" :class="{'border-b-blue-400 border-b-2' : urReq}" @click="urReqFun">Your Requests</span>
                <span class="font-semibold p-3 cursor-pointer" :class="{'border-b-blue-400 border-b-2' : friReq}" @click="friReqFun">Friend Requests</span>
            </div>
            <div v-if="friends && friendLists.data" class="basis-[93%] overflow-y-scroll">
                <div v-for="(item,index) in friendLists.data" :key="index" class="mt-4">
                    <div v-if="item.is_approve" class="grid grid-cols-12 items-center text-center">
                        <span class="col-span-3 block flex justify-center">
                            <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/user.svg'" alt="" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                        </span>
                        <span class="col-span-6">{{ item.name }}</span>
                        <span class="col-span-3">
                            <button class="py-2 px-3 bg-slate-600 hover:bg-slate-500 rounded-lg text-sm text-white font-semibold sm:me-4" @click="unfriend(item.friend_list_id)">Unfriend</button>
                        </span>
                    </div>
                </div>
                <div ref="target"></div>
            </div>
            <div v-if="urReq && friendLists.data" class="basis-[93%] overflow-y-scroll">
                <div v-for="(item,index) in friendLists.data" :key="index" class="mt-4">
                    <div v-if="!item.is_approve && userData.user.id == item.first_user_id" class="grid grid-cols-12 items-center text-center">
                        <span class="col-span-3 block flex justify-center">
                            <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/user.svg'" alt="" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                        </span>
                        <span class="col-span-6">{{ item.name }}</span>
                        <span class="col-span-3">
                            <button class="py-2 px-3 bg-slate-600 hover:bg-slate-500 rounded-lg text-sm text-white font-semibold me-4" @click="cancelFriReq(item.friend_list_id)">Cancel</button>
                        </span>
                    </div>
                </div>
                <div ref="target"></div>
            </div>
            <div v-if="friReq && friendLists.data" class="basis-[93%] overflow-y-scroll">
                <div v-for="(item,index) in friendLists.data" :key="index" class="mt-4">
                    <div v-if="!item.is_approve && userData.user.id == item.second_user_id" class="grid grid-cols-12 items-center text-center">
                        <span class="col-span-3 block flex justify-center">
                            <img :src="item.profile_photo ? '/storage/' + item.profile_photo : '/storage/user.svg'" alt="" class="rounded-full" style="width: 40px; height: 40px; object-fit: cover;">
                        </span>
                        <span class="col-span-5">{{ item.name }}</span>
                        <span class="col-span-4">
                            <button class="py-2 px-3 bg-blue-600 hover:bg-blue-400 rounded-lg text-sm text-white font-semibold me-4" @click="friendAcceptFun(item.friend_list_id)">Accept</button>
                            <button class="py-2 px-3 bg-slate-600 hover:bg-slate-400 rounded-lg text-sm text-white font-semibold me-4" @click="deleteReq(item.friend_list_id)">Delete</button>
                        </span>
                    </div>
                </div>
                <div ref="target"></div>
            </div>
        </div>
        <div v-if="modalToggle" class="absolute top-36 text-slate-600 font-semibold bg-slate-300 p-3 rounded-xl z-50">
            {{ modalContent }}
        </div>
    </section>
</template>
