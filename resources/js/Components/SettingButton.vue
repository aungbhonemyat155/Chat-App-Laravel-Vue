<script setup>
import { useMainStore } from "@/stores/MainStore"

const store = useMainStore()

</script>

<template>
    <button @click="store.settingFun" class="mr-5 text-2xl"><i class="fa-solid fa-bars"></i></button>
</template>
