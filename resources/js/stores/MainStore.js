import axios from "axios";
import { defineStore } from "pinia";
import MessageFormatter from "@/Functions/messageFormatter";
import { ref } from "vue";

export const useMainStore = defineStore( 'mainStore', () => {
    //store data
    const settingToggle = ref(false)
    const searchFriToggle = ref(false)
    const notiToggle = ref(false)
    const emptyBox = ref(true)
    const userData = ref(null)
    const editToggle = ref(false)
    const notifications = ref(null)
    const friendListToggle = ref(false)
    const friendLists = ref(null)
    const contentBox = ref(false)
    const friendIndex = ref(null)
    const loadingScreen = ref(false)
    const tempMessages = ref(null)
    const messageNoti = ref(null)
    const saveMessage = ref(false)
    //getter


    //action
    function toggleOff(){
        searchFriToggle.value = false
        editToggle.value = false
        notiToggle.value = false
        friendListToggle.value = false
        contentBox.value = false
        saveMessage.value = false
        emptyBox.value = true
    }

    function toggleOn(){
        searchFriToggle.value = false
        editToggle.value = false
        notiToggle.value = false
        friendListToggle.value = false
        emptyBox.value = false
        saveMessage.value = false
        contentBox.value = false
    }

    function settingFun() {
        settingToggle.value = !settingToggle.value
    }

    function searchFriFun() {
        if(searchFriToggle.value){
            toggleOff()
        }else{
            toggleOn()
            searchFriToggle.value = true
        }
    }

    function notiFun() {
        if(notiToggle.value){
            toggleOff()
        }else{
            toggleOn()
            notiToggle.value = true
        }
    }

    function emptyBoxFun(){
        emptyBox.value = !emptyBox.value
    }

    function homeFun() {
        settingToggle.value = false
        toggleOff()
    }

    function setUser(data) {
        userData.value = data
    }

    function editToggleFun(){
        if(editToggle.value){
            toggleOff()
        }else{
            toggleOn()
            editToggle.value = true
        }
    }

    function friListToggleFun(){
        if(friendListToggle.value){
            toggleOff()
        }else{
            toggleOn()
            friendListToggle.value = true
        }
    }

    function setNoti($data){
        notifications.value = $data
    }

    function unreadNotiCountSet($data){
        userData.value.unreadNotiCount = $data
    }

    function setFriendLists($data){
        friendLists.value = $data
    }

    function contentBoxToggle(id, index){
        toggleOn();
        friendIndex.value = index

        if(!friendLists.value.data[index].messages){
            loadingScreen.value = true
            axios.get('messages/'+id).then(response => {
                let formatter = new MessageFormatter(response.data.data)
                response.data.data = formatter.changeMessageDate()

                friendLists.value.data[index].messages = response.data
                tempMessages.value = response.data

                loadingScreen.value = false
                contentBox.value = true
            }).catch(error => {
                console.log(error);
            })
        }else{
            tempMessages.value = friendLists.value.data[index].messages
            contentBox.value = true
        }
    }

    function setMessage(data){
        tempMessages.value = data
    }

    function pushMessage(data){
        tempMessages.value.data = data
    }

    function setMessageNoti(data){
        messageNoti.value = data
    }

    function backHome(){
        toggleOff();
        friendIndex.value = null
    }

    function backToSetting(){
        toggleOff();
        settingToggle.value = true
    }

    function saveMessageToggle(){
        toggleOn();
        friendIndex.value = null
        loadingScreen.value = true
        axios.get('save-messages').then(response => {
            console.log(response.data);

            tempMessages.value = response.data;
            saveMessage.value = true

            loadingScreen.value = false
        }).catch(error => {
            console.log(error);
        })
    }

    return { settingToggle,
        searchFriToggle,
        notiToggle,
        emptyBox,
        userData,
        editToggle,
        notifications,
        friendListToggle,
        friendLists,
        contentBox,
        friendIndex,
        loadingScreen,
        tempMessages,
        messageNoti,
        saveMessage,
        settingFun,
        searchFriFun,
        notiFun,
        emptyBoxFun,
        homeFun,
        setUser,
        editToggleFun,
        friListToggleFun,
        setNoti,
        unreadNotiCountSet,
        setFriendLists,
        contentBoxToggle,
        setMessage,
        pushMessage,
        setMessageNoti,
        backHome,
        backToSetting,
        saveMessageToggle
    }
})
