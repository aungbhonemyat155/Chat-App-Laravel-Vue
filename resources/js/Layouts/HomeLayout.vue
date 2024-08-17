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
import { onMounted, ref } from "vue"
import { useMainStore } from '../stores/MainStore'
import { storeToRefs } from 'pinia'
import LoadingScreen from "@/Components/LoadingScreen.vue";
import BroadCast from "@/Functions/broadcastFunctions";

const store = useMainStore()
const { settingToggle, searchFriToggle, notiToggle, emptyBox, editToggle, userData, friendListToggle, contentBox, loadingScreen } = storeToRefs(store)

const searchResult = ref("");

onMounted(() => {

    const broadcast = new BroadCast(store)

    Echo.private('App.Models.User.'+ userData.value.user.id)
    .notification((item) => {

        item.data = JSON.parse(item.data)

        switch(item.type) {
            case "broadcast.sendMessage": {
                broadcast.sendMessage(item)
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
            default: {
                console.log(item);
            }
        }
    });

    // console.log(Intl.DateTimeFormat().resolvedOptions().timeZone)


})
</script>

<template>
    <main class="grid grid-cols-12 relative text-slate-300">
        <!-- side bar  -->
        <div id="friendList" class="col-span-3 bg-gray-900 flex flex-col h-screen" :class="{'hidden' : settingToggle}">
            <!-- nav-bar and search box  -->
            <div class="bg-gray-900 py-3 px-5 flex justify-center">
                <SettingButton></SettingButton>
                <TextInput class="flex-1 border-none outline-none bg-gray-800" v-model="searchResult" placeholder="Search...."></TextInput>
            </div>
            <!-- nav-bar and search box  -->

            <!-- user's friends lists  -->
            <div class="p-2 flex-1 overflow-y-scroll">
                <SideBar></SideBar>
            </div>
            <!-- user's friends lists  -->
        </div>
        <!-- side bar  -->

        <!-- setting side bar toggle  -->
        <div v-if="settingToggle && emptyBox" class="fixed inset-0 z-20" @click="store.settingFun"></div>
        <SettingSidebar v-if="settingToggle" class="z-30 col-span-3 bg-gray-200"/>
        <!-- setting side bar toggle  -->

        <!-- content side  -->
        <ContentsArea v-if="contentBox"></ContentsArea>
        <!-- content side  -->

        <div v-if="!contentBox && emptyBox" class="col-span-9 bg-gray-800 flex h-screen justify-center items-center">
            <span class="bg-slate-600 px-2 text-gray-200 py-1 font-semibold rounded-xl">Select a chat to start messaging</span>
        </div>
        <!-- content side  -->
        <AddFriendPage class="col-span-9" v-if="searchFriToggle"></AddFriendPage>

        <NotificationPage class="col-span-9" v-if="notiToggle"></NotificationPage>

        <SettingPage class="col-span-9" v-if="editToggle"></SettingPage>

        <FriendListPage class="col-span-9" v-if="friendListToggle"></FriendListPage>

        <LoadingScreen class="col-span-9" v-if="loadingScreen"></LoadingScreen>
    </main>
</template>
