class AddButton{
    static add(arr){
        const newArr = arr.map(item => {
            item.button = false

            return item;
        })

        return newArr
    }
}

export default AddButton;
