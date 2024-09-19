import { storeToRefs } from "pinia";

class BroadcastMessage {
    constructor(store) {
        const { friendIndex,
            friendLists,
            tempMessage } = storeToRefs(store);

        this.friendIndex = friendIndex;
        this.friendLists = friendLists;
        this.tempMessage = tempMessage;
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

    sendMessage(data) {
        //search if the sender info include in friend list
        this.friendListsIndex = this.searchFriendLists(data.senderData)

        //sorting friend lists
        if(this.friendListsIndex === -1){
            this.unshiftFriendLists(data.senderData)
        }else{
            this.sortFriendLists(this.friendListsIndex)
        }

        //push message to message array

    }
}

export default BroadcastMessage;
