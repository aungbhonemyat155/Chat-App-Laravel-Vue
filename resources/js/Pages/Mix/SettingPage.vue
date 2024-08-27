<script setup>
import DeleteUserForm from '@/Pages/Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '@/Pages/Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '@/Pages/Profile/Partials/UpdateProfileInformationForm.vue';
import UpdateProfilePicture from "@/Pages/Profile/Partials/UpdateProfilePicture.vue"
import { useMainStore } from '@/stores/MainStore';
import { storeToRefs } from 'pinia';

const store = useMainStore()
const { userData } = storeToRefs(store)

</script>

<template>
    <section class="h-screen overflow-y-scroll bg-gray-900 text-slate-300 relative">
        <button @click="store.backToSetting" class="absolute top-3 left-4 md:hidden"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
                    <UpdateProfilePicture class="max-w-xl" :user="userData.user"/>
                </div>

                <div class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
                    <UpdateProfileInformationForm
                        :must-verify-email="userData.mustVerifyEmail"
                        :status="userData.status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

                <div class="p-4 sm:p-8 bg-gray-800 shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </section>
</template>

