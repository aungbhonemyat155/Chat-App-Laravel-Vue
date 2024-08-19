import { storeToRefs } from "pinia";
import TimeFormatter from "./dateTimeFormatter";

class BroadCast{
    constructor(store){
        this.store = store;
        const { friendLists, notifications, notiToggle, userData, friendIndex, messageNoti } = storeToRefs(store)
        this.friendLists = friendLists;
        this.notifications = notifications;
        this.notiToggle = notiToggle;
        this.userData = userData;
        this.friendIndex = friendIndex;
        this.messageNoti = messageNoti
    }

    friendRequest(item){
        let tempFriend = {
            first_user_id : item.data.first_user_id,
            second_user_id : item.data.second_user_id,
            friend_list_id : item.data.id,
            is_approve : false,
            is_delete : false,
            last_message : null,
            friend_id : item.data.senderData.id,
            name : item.data.senderData.name,
            profile_photo : item.data.senderData.profile_photo
        }

        let temp = this.friendLists.value
        temp.data = [ tempFriend, ...this.friendLists.value.data ];
        this.store.setFriendLists(temp)

        let noti = {
            "id" : item.id,
            "type" : item.type,
            "data" : {
                "sender_profile_photo" : item.data.senderData.profile_photo,
                "sender_name" : item.data.senderData.name,
                "friend_list_id" : item.data.id,
            }
        };
        let secTemp = [ noti, ...this.notifications.value ]
        this.store.setNoti(secTemp)

        if(!this.notiToggle.value){
            this.userData.value.unreadNotiCount = this.userData.value.unreadNotiCount + 1
        }
    }

    friReqCancel(item){
        let temp = this.notifications.value.filter(tempItem => tempItem.id != item.data.notiId)
        this.store.setNoti(temp)

        let secTemp = this.friendLists.value
        secTemp.data = this.friendLists.value.data.filter(tempItem => tempItem.friend_list_id != item.data.friendList.id)
        this.store.setFriendLists(secTemp)

        this.userData.value.unreadNotiCount = item.unreadNotiCount
    }

    friAccepted(item){
        this.friendLists.value.data.map(tempItem => {
            if(tempItem.friend_list_id == item.data.id){
                tempItem.is_approve = true
            }

            return tempItem;
        })

        let noti = {
            "id" : item.id,
            "type" : item.type,
            "data" : {
                "sender_profile_photo" : item.senderData.profile_photo,
                "sender_name" : item.senderData.name,
            }
        };
        let secTemp = [ noti, ...this.notifications.value ]
        this.store.setNoti(secTemp)

        if(!this.notiToggle.value){
            this.userData.value.unreadNotiCount = this.userData.value.unreadNotiCount + 1
        }
    }

    unfriend(item){
        let temp = this.friendLists.value
        temp.data = temp.data.filter(tempItem => tempItem.friend_list_id != item.data.id)
        this.store.setFriendLists(temp)

        let secTemp = this.notifications.value.filter(tempItem => tempItem.id != item.data.notiId)
        this.store.setNoti(secTemp)
    }

    deleteFriReq(item){
        let temp = this.friendLists.value
        temp.data = temp.data.map(tempItem => {
            if(tempItem.friend_list_id == item.data.id){
                tempItem.is_delete = true
            }
            return tempItem;
        })
        this.store.setFriendLists(temp)
    }

    sendMessage(item){
        let removedValue;
        let temp = this.friendLists.value

        item.message = JSON.parse(item.message)
        let formatter = new TimeFormatter(item.message.created_at)
        item.message.created_at = formatter.getTime()
        item.senderData = JSON.parse(item.senderData)
        // item.data.last_message = JSON.parse(item.data.last_message)

        const index = this.friendLists.value.data.findIndex((tempItem) => tempItem.friend_list_id == item.data.id)

        if(index != -1){
            removedValue = temp.data.splice(index, 1)[0];
            removedValue.last_message = item.message;

            if(this.friendIndex.value < index) this.friendIndex.value = this.friendIndex.value + 1

            if(removedValue.messages) removedValue.messages.data = [ item.message, ...removedValue.messages.data ]

            if(index != this.friendIndex.value) {
                if(this.messageNoti.value[`${item.message.from_user_id}`]){
                    this.messageNoti.value[`${item.message.from_user_id}`].push(item.id)
                }else{
                    let senderId = item.senderData.id;
                    let temp = {};
                    temp[`${senderId}`] = ["some value"];

                    temp = { ...temp, ...this.messageNoti.value }
                    this.store.setMessageNoti(temp)
                }
            }else{
                    axios.get("message/read/"+item.senderData.id).catch(error => {
                    console.log(error);
                })
            }
        }else{
            let { id, ...friend_list } = item.data
            removedValue = {
                ...friend_list,
                ...item.senderData,
                friend_id : item.senderData.id,
                friend_list_id : item.data.id
            }

            removedValue.last_message = JSON.parse(removedValue.last_message)

            if(this.friendIndex.value)this.friendIndex.value = this.friendIndex.value + 1

            if(this.messageNoti.value[`${item.senderData.id}`]){
                this.messageNoti.value[`${item.senderData.id}`].push(item.id)
            }else{
                let senderId = item.senderData.id;
                let temp = {};
                temp[`${senderId}`] = ["some value"];
                temp = { ...temp, ...this.messageNoti.value }

                this.store.setMessageNoti(temp)
            }
        }

        temp.data = [ removedValue, ...temp.data ]
        this.store.setFriendLists(temp)
    }
}

export default BroadCast;




