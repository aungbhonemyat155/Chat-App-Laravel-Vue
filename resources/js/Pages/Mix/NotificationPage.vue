<script setup>
import { useMainStore } from "@/stores/MainStore";
import axios from "axios";
import { storeToRefs } from "pinia";
import { onBeforeMount, onMounted, ref } from "vue";

const store = useMainStore()

const { notifications, friendLists } = storeToRefs(store)

const modalToggle = ref(false)

const modalContent = ref(null)

const friendAcceptFun = (friend_list_id, noti_id) => {
    axios.get("/friend/accept/"+friend_list_id,{
        params: {
            "key": noti_id
        }
    }).then((response) => {
        if(response.data.status){
            let filtered = notifications.value.filter(item => item.id != noti_id)
            store.setNoti(filtered)

            let temp = friendLists.value.map((item) => {
                if(item.friend_list_id == friend_list_id){
                    item.is_approve = true
                }
                return item
            })
            store.setFriendLists(temp)
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

const deleteFriReq = (friend_list_id, noti_id) => {
    axios.get('friend/request/delete/'+friend_list_id).then(response => {

        if(response.data.status){
            let filtered = notifications.value.filter(item => item.id != noti_id)
            store.setNoti(filtered)

            let temp = friendLists.value.filter(item => item.friend_list_id != friend_list_id)
            store.setFriendLists(temp)
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

onMounted(() => {
    axios.get("notification/read")

    store.unreadNotiCountSet(0)
})
</script>

<template>
    <section class="z-40 h-screen bg-gray-900 flex justify-center items-start relative text-slate-300">
        <div class="z-40 bg-gray-800 p-5 basis-[50%] rounded-xl mt-5 h-5/6">
            <div v-if="notifications.length">
                <div v-for="(item,index) in notifications" :key="index" class="flex items-center bg-gray-700 p-3 rounded-lg mb-4">
                    <img :src="item.data.sender_profile_photo ? '/storage/' + item.data.sender_profile_photo : '/storage/user.svg'" alt="" class="rounded-full mr-5" style="width: 60px; height: 60px; object-fit: cover;">
                    <div v-if="item.type == 'FriendRequest'">
                        <span class="font-bold">{{ item.data.sender_name }}</span>
                        <span> send you friend request</span>
                        <div class="mt-2">
                            <button class="py-2 px-3 bg-blue-600 hover:bg-blue-500 rounded-lg text-sm text-white font-semibold me-4" @click="friendAcceptFun(item.data.friend_list_id, item.id)">Accept</button>
                            <button class="py-2 px-3 bg-slate-600 hover:bg-slate-500 rounded-lg text-sm text-white font-semibold" @click="deleteFriReq(item.data.friend_list_id, item.id)">Delete</button>
                        </div>
                    </div>
                    <div v-if="item.type == 'FriendAccepted'">
                        <span class="font-bold">{{ item.data.sender_name }}</span>
                        <span> accept your friend request</span>
                    </div>
                </div>
            </div>
            <div v-else class="w-full h-full flex justify-center items-center" id="loadingScreen">
                <span class="font-semibold text-lg">THERE IS NO NOTIFICATION</span>
            </div>
        </div>
        <div v-if="modalToggle" class="absolute top-36 text-slate-600 font-semibold bg-slate-300 p-3 rounded-xl z-50">
            {{ modalContent }}
        </div>
    </section>
</template>
