class TimeFormatter{
    constructor(time){
        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        this.date = new Date(time);
        this.formatter = new Intl.DateTimeFormat(undefined, {
          timeZone,
          day: 'numeric',
          month: 'numeric',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
    }

    getTime(){
        const localTimeString = this.formatter.format(this.date);

        return localTimeString;
    }

    getDayDate(){
        const localTimeString = this.formatter.format(this.date);

        return localTimeString.split(", ")[0];
    }

    getHourDate(){
        const localTimeString = this.formatter.format(this.date);

        return localTimeString.split(", ")[1];
    }
}

export default TimeFormatter;
