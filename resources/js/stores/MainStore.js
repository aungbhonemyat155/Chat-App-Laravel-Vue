import axios from "axios";
import { defineStore } from "pinia";
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
    const loadingScreen = ref(true)
    //getter


    //action
    function toggleOff(){
        searchFriToggle.value = false
        editToggle.value = false
        notiToggle.value = false
        friendListToggle.value = false
        contentBox.value = false
        emptyBox.value = true
    }

    function toggleOn(){
        searchFriToggle.value = false
        editToggle.value = false
        notiToggle.value = false
        friendListToggle.value = false
        emptyBox.value = false
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
        toggleOff();
        emptyBox.value = false

        if(!friendLists.value.data[index].messages){
            setTimeout(() => testing(id, index), 3000)
        }else{
            setTimeout(testingTwo, 3000)
        }
    }

    function testing(id, index){
        axios.get('messages/'+id).then(response => {
            friendLists.value.data[index].messages = response.data

            contentBox.value = true
        }).catch(error => {
            console.log(error);
        })
    }

    function testingTwo(){
        contentBox.value = true
    }

    function setMessage(index, messages){
        friendLists.value.data[index].messages = messages
    }

    function setFriendIndex(index){
        friendIndex.value = index
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
        setFriendIndex
    }
})
