class MessageFormatter{
    constructor(data){
        this.data = data

        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        this.formatter = new Intl.DateTimeFormat(undefined, {
          timeZone,
          day: 'numeric',
          month: 'numeric',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
    }

    changeMessageDate(){
        let messages = this.data.map(item => {
            let date = new Date(item.created_at)

            const localTimeString = this.formatter.format(date);
            item.created_at = localTimeString.split(", ")

            return item;
        })

        return messages;
    }
}

export default MessageFormatter;
