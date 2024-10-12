<script setup>
import SettingButton from "@/Components/SettingButton.vue";
import TextInput from "@/Components/TextInput.vue"
import SideBar from "@/Components/SideBar.vue"
import ContentsArea from "@/Components/ContentsArea.vue"
import SettingSidebar from "@/Components/SettingSidebar.vue"
import AddFriendPage from "@/Pages/Mix/AddFriendPage.vue"
import NotificationPage from "@/Pages/Mix/NotificationPage.vue"
import SettingPage from "@/Pages/Mix/SettingPage.vue"
import FriendListPage from "@/Pages/Mix/FriendListPage.vue"
import SaveMessagePage from "@/Pages/Mix/SaveMessagePage.vue";
import { onMounted, ref } from "vue"
import { useMainStore } from '../stores/MainStore'
import { storeToRefs } from 'pinia'
import LoadingScreen from "@/Components/LoadingScreen.vue";
import BroadCast from "@/Functions/broadcastFunctions";
import BroadcastMessage from "@/Functions/broadcastMessage";

const store = useMainStore()
const { settingToggle, searchFriToggle, notiToggle, emptyBox, editToggle, userData, friendListToggle, contentBox, loadingScreen, saveMessage, friendLists, messageNoti, friendIndex } = storeToRefs(store);

onMounted(() => {

    const broadcast = new BroadCast(store)
    const messageBroadcast = new BroadcastMessage(store)

    Echo.private('App.Models.User.'+ userData.value.user.id)
    .notification((item) => {

        item.data = JSON.parse(item.data)

        switch(item.type) {
            case "broadcast.sendMessage": {
                let data = {
                    "data": item.data,
                    "senderData": JSON.parse(item.senderData),
                    "message": JSON.parse(item.message)
                }

                messageBroadcast.sendMessage(data)
                break;
            }
            case "broadcast.friendRequest": {
                broadcast.friendRequest(item)
                break;
            }
            case 'broadcast.friReqCancel': {
                broadcast.friReqCancel(item)
                break;
            }
            case 'broadcast.friendAccepted': {
                broadcast.friAccepted(item)
                break;
            }
            case 'broadcast.unfriend': {
                broadcast.unfriend(item)
                break;
            }
            case 'broadcast.deleteFriReq': {
                broadcast.deleteFriReq(item)
                break;
            }
            case 'broadcast.deleteMessage': {
                broadcast.deleteMessage(item)
            }
            default: {
                console.log(item);
            }
        }
    });
})

const arraySearch = (arr, value, current_page) => {
    for(let i = 0; i < arr.length; i++){
        if(arr[i].name.toLowerCase().includes(value.toLowerCase())){
            searchValues.value.push({ "index":i+(current_page-1)*10, "value": arr[i] });
        }
    }
}

function fetchNextPage() {
  if (friendLists.value.next_page_url) {
    axios.get(`/friends/lists?page=${friendLists.value.current_page + 1}`)
      .then(response => {
        //search the newly got array
        arraySearch(response.data.data, searchKey.value, response.data.current_page);

        //assign the newly got array value to the state friendLists
        let temp = [...friendLists.value.data, ...response.data.data];
        response.data.data = temp;
        store.setFriendLists(response.data);

        // recursive call
        fetchNextPage();
    })
      .catch(error => {
        console.log(error);
    });
  }
}

const searchKey = ref("");
const searchValues = ref([]);
const searchToggle = ref(false);

const searchFun = () => {
    searchValues.value = [];

    searchKey.value = searchKey.value.trim();
    searchKey.value = searchKey.value.toLowerCase();

    arraySearch(friendLists.value.data, searchKey.value, 1);

    fetchNextPage();

    searchToggle.value = true
}

const closeSearchBox = () => {
    searchToggle.value = false;
    searchValues.value = [];
    searchKey.value = "";
}

const contentFun = (index, id) => {
    store.contentBoxToggle(id, index)

    if(messageNoti.value[`${id}`]){
        delete messageNoti.value[`${id}`]

        axios.get("message/read/"+id).catch(error => {
            console.log(error)
        })
    }
}
</script>

<template>
    <main class="grid grid-cols-12 relative text-slate-300">
        <!-- side bar  -->
        <div id="friendList" class="col-span-12 md:col-span-5 lg:col-span-4 2xl:col-span-3 bg-gray-900 flex flex-col h-screen" :class="{'hidden md:hidden' : settingToggle, 'hidden md:flex md:flex-col' : contentBox || loadingScreen || saveMessage}">
            <!-- nav-bar and search box  -->
            <div class="bg-gray-900 py-3 px-5 flex justify-center basis-[7%]">
                <SettingButton></SettingButton>
                <form @submit.prevent="searchFun()" class="flex-1 relative">
                    <TextInput class="w-full border-none outline-none bg-gray-800" v-model="searchKey" placeholder="Search...."></TextInput>
                    <span class="absolute inset-y-0 right-2 flex items-center justify-center text-slate-500"><i @click="closeSearchBox" class="fa-solid fa-xmark cursor-pointer"></i></span>
                </form>
            </div>
            <!-- nav-bar and search box  -->

            <!-- user's friends lists  -->
            <div v-if="!searchToggle" class="p-2 basis-[93%] overflow-y-scroll">
                <SideBar></SideBar>
            </div>

            <div v-if="searchToggle" class="p-2 basis-[93%] overflow-y-scroll">
                <div v-for="(item, index) in searchValues" :key="index" @click="contentFun(item.index, item.value.friend_id)">
                    <div class="grid grid-cols-12 p-1 hover:bg-gray-800">
                        <img :src="item.value.profile_photo ? '/storage/' + item.value.profile_photo : '/storage/user3.svg'" alt="" class=" rounded-full col-span-2 2xl:col-span-3" style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="flex flex-col mt-1 col-span-9 2xl:col-span-8">
                            <span class="text-white font-bold">{{ item.value.name }}</span>
                            <!-- <span v-if="item.value.latest_message_text" class="text-slate-500 text-sm">{{ truncateMessage(item.value.latest_message_text) }}</span> -->
                        </div>
                        <div v-if="messageNoti[`${item.value.friend_id}`]" class="bg-blue-700 rounded-full w-min h-min p-1 px-3 self-center col-span-1 text-center text-xs font-bold">{{ messageNoti[`${item.value.friend_id}`].length }}</div>
                    </div>
                </div>
            </div>
            <!-- user's friends lists  -->
        </div>
        <!-- side bar  -->

        <!-- setting side bar toggle  -->
        <div v-if="settingToggle && emptyBox" class="fixed inset-0 z-20" @click="store.settingFun"></div>
        <SettingSidebar v-if="settingToggle" class="z-30 h-screen col-span-12 md:col-span-5 lg:col-span-4 2xl:col-span-3 bg-gray-200"
        :class="{'hidden' : searchFriToggle || notiToggle || editToggle || friendListToggle }"/>
        <!-- setting side bar toggle  -->

        <!-- content side  -->
        <ContentsArea v-if="contentBox && (friendIndex !== -1)"></ContentsArea>
        <!-- content side  -->

        <div v-if="(!contentBox && emptyBox && !saveMessage)" class="hidden md:col-span-7 lg:col-span-8 2xl:col-span-9 bg-gray-800 md:flex h-screen justify-center items-center">
            <span class="bg-slate-600 px-2 text-gray-200 py-1 font-semibold rounded-xl">Select a chat to start messaging</span>
        </div>
        <!-- content side  -->
        <AddFriendPage class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9" v-if="searchFriToggle"></AddFriendPage>

        <NotificationPage class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9" v-if="notiToggle"></NotificationPage>

        <SettingPage class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9" v-if="editToggle"></SettingPage>

        <FriendListPage class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9" v-if="friendListToggle"></FriendListPage>

        <SaveMessagePage class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9" v-if="saveMessage"></SaveMessagePage>

        <LoadingScreen class="col-span-12 md:col-span-7 lg:col-span-8 2xl:col-span-9" v-if="loadingScreen"></LoadingScreen>
    </main>
</template>
