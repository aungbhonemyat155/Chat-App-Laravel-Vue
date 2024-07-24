<script setup>
import { useForm } from "@inertiajs/vue3"
import { ref } from "vue"
import InputLabel from "@/Components/InputLabel.vue"
import PrimaryButton from "@/Components/PrimaryButton.vue";
import DangerButton from "@/Components/DangerButton.vue";
import InputError from "@/Components/InputError.vue"

defineProps({
    user: {
        type: Object
    }
})

const form = useForm({
    file: null
});

const temporaryPhotoUrl = ref("")

const mimeError = ref("");

const fileValue = ref(null)

const clearForm = () => {
    form.reset()
    if(temporaryPhotoUrl.value){
        temporaryPhotoUrl.value = ""
        URL.revokeObjectURL(temporaryPhotoUrl.value)
    }
    mimeError.value = ""
}

const formFun = () => {
    if(form.file){
        form.post(route('profilePic.update'), {
            onFinish: () => {
                form.reset()

                URL.revokeObjectURL(temporaryPhotoUrl.value);
                temporaryPhotoUrl.value = ""

                location.reload()
            },
        });
    }
}

const fileEvent = (event) => {
    fileValue.value = event.target.files[0]

    const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];

    if (fileValue.value && allowedMimeTypes.includes(fileValue.value.type)) {
        if(temporaryPhotoUrl){
            URL.revokeObjectURL(temporaryPhotoUrl.value)
        }
        temporaryPhotoUrl.value = URL.createObjectURL(fileValue.value)
        form.file = fileValue.value
        mimeError.value = '';
    } else {
        if(temporaryPhotoUrl){
            URL.revokeObjectURL(temporaryPhotoUrl.value)
            temporaryPhotoUrl.value = ""
        }
        form.file = null
        mimeError.value = 'Please upload a valid image file (JPEG, PNG, JPG or GIF).';
        event.target.value = null;
    }
}
</script>

<template>
  <section>
    <header>
        <h2 class="text-lg font-medium text-slate-200">Profile Picture</h2>

        <p class="mt-1 text-sm text-slate-300">
            Update your account's profile picture here.
        </p>

        <div v-if="user.profile_photo" class="flex items-center">
            <img :src="'/storage/'+user.profile_photo" alt="Your Profile Photo" style="width: 200px; height: 200px; object-fit: cover;">
            <p class="text-slate-300 ms-5">(This is your profile picture)</p>
        </div>

        <form @submit.prevent="formFun" class="mt-6 space-y-6" enctype="multipart/form-data">
            <InputLabel for="file" value="Choose your photo from your device"></InputLabel>

            <div v-if="temporaryPhotoUrl" class="flex items-center">
                <img :src="temporaryPhotoUrl" alt="Selected photo" style="width: 200px; height: 200px; object-fit: cover;" />
                <p class="text-slate-300 ms-5">(This is selected photo)</p>
            </div>

            <input id="file" type="file" name="photo" @change="fileEvent" class="bg-gray-700">

            <div>
                <PrimaryButton class="me-10">Save</PrimaryButton>
                <DangerButton type="button" @click="clearForm">Clear Form</DangerButton>
            </div>

            <div><InputError class="mt-2" :message="mimeError" /></div>
        </form>
    </header>
  </section>
</template>

