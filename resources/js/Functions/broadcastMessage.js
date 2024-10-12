import { storeToRefs } from "pinia";
import axios from "axios"

class BroadcastMessage {
    constructor(store) {
        const { friendIndex,
            friendLists,
            tempMessage,
            messageNoti } = storeToRefs(store);

        this.store = store;
        this.friendIndex = friendIndex;
        this.friendLists = friendLists;
        this.tempMessage = tempMessage;
        this.messageNoti = messageNoti;
        this.friendListsIndex = -1;
    }

    searchFriendLists(friendInfo) {
        return this.friendLists.value.data.findIndex((item) => {
            return item.friend_id === friendInfo.id
        })
    }

    checkFriendIndex() {
        return this.friendIndex.value === this.friendListsIndex
    }

    sortFriendLists(friendListsIndex) {
        let removed = this.friendLists.value.data.splice(friendListsIndex, 1);

        this.unshiftFriendLists(removed)
    }

    unshiftFriendLists(friendData) {
        this.friendLists.value.data.unshift(friendData)
    }

    pushMessage(newMessage) {
        let messages = this.friendLists.value.data[this.friendListsIndex].messages.data;
        messages.push(newMessage);
    }

    pushTempMessage(newMessage) {
        let messages = this.tempMessage.value.data
        messages.push(newMessage);
    }

    updateLatestMessageData(message){
        let friLists = this.friendLists.value.data[this.friendListsIndex];

        friLists.latest_message_created_at = message.created_at;
        friLists.latest_message_from_user_id = message.from_user_id;
        friLists.latest_message_id = message.id;
        friLists.latest_message_text = message.message;
        friLists.latest_message_to_user_id = message.to_user_id;
    }

    friendListBuilder(data){
        return {
            "friend_id": data.senderData.id,
            "name": data.senderData.name,
            "email": data.senderData.email,
            "profile_photo": data.senderData.profile_photo,
            "friend_list_id": data.data.id,
            "first_user_id": data.data.first_user_id,
            "second_user_id": data.data.second_user_id,
            "is_approve": data.data.is_approve,
            "is_delete": data.data.is_delete,
            "updated_at": data.data.updated_at,
            "latest_message_id": data.message.id,
            "latest_message_from_user_id": data.message.from_user_id,
            "latest_message_to_user_id": data.message.to_user_id,
            "latest_message_text": data.message.message,
            "latest_message_created_at": data.message.created_at
        }
    }

    addMessageNotification(message){
        //check if the message.from_user_id index is exist
        if(this.messageNoti.value[`${message.from_user_id}`]){
            //push the notification to that index
            this.messageNoti.value[`${message.from_user_id}`].push("some value for message notification")
        }else{
            //create that index and push the value to that index
            let temp = {};
            temp[`${message.from_user_id}`] = ["some value for message notification"];
            temp = { ...temp, ...this.messageNoti.value }
            this.store.setMessageNoti(temp)
        }
    }

    readMessageNofication(senderId){
        axios.get("message/read/"+senderId).catch(error => {
            console.log(error);
        })
    }

    sendMessage(data) {
        //search if the sender info include in friend list
        this.friendListsIndex = this.searchFriendLists(data.senderData)

        //check if the sender data exist in the friendLists
        if(this.friendListsIndex != -1){
            var friLists = this.friendLists.value.data[this.friendListsIndex];

            //check if the sender index is not 0
            if(this.friendListsIndex != 0){
                //unshift the friend data to friendLists
                let removedValue = this.friendLists.value.data.splice(this.friendListsIndex, 1)[0];
                this.unshiftFriendLists(removedValue);

                //update the friend index
                if(typeof this.friendIndex.value === 'number' && this.friendIndex.value < this.friendListsIndex){
                    this.friendIndex.value += 1;
                }
            }

            //check if the temp message is need to update and adding notification for message
            if(this.friendListsIndex == this.friendIndex.value){
                //update the temp message
                let tempData = [ data.message, ...this.tempMessage.value.data ];
                this.store.pushMessage(tempData);

                //read message notification
                this.readMessageNofication(data.message.from_user_id)
            }else{
                if(friLists.messages){
                    let tempData = [ data.message, ...friLists.messages.data ];
                    friLists.messages.data = tempData;
                }

                //adding message notification
                this.addMessageNotification(data.message)
            }

            //update the latest message data
            this.updateLatestMessageData(data.message);
        }else{
            var friLists = this.friendLists.value;

            //assign friend data value to friend Lists
            let friendData = this.friendListBuilder(data);
            friLists.data = [ friendData, ...friLists.data ];
            friLists.data.pop();

            //update the friend index
            if(typeof this.friendIndex.value === 'number'){
                this.friendIndex.value += 1;
            }

            //adding message notification
            this.addMessageNotification(data.message)

            //assign new friend lists value to state value
            this.store.setFriendLists(friLists);
        }

    }
}

export default BroadcastMessage;
