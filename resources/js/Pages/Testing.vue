<script setup>
  import { ref } from 'vue';

  const firstElement = ref(null);
  const modalX = ref(0)
  const modalY = ref(0)
  const modalToggle = ref(false)

  const getCoordinates = () => {
      if (firstElement.value) {
        console.log(firstElement.value.offsetTop, firstElement.value.offsetLeft);


        const rect = firstElement.value.getBoundingClientRect();
        modalX.value = rect.left + window.scrollX;; // X coordinate
        modalY.value = rect.top + window.scrollY;  // Y coordinate

        modalToggle.value = true;
        console.log(`Top Left X: ${modalX.value}, Top Left Y: ${modalY.value}`);
      }
    };
</script>

<template>
    <section class="w-screen h-screen flex justify-center items-center bg-yellow-300 relative">
        <div @click="getCoordinates" style="height: 200px; width: 200px;" class="bg-gray-400" ref="firstElement">something</div>
        <div v-if="modalToggle" class="absolute w-20 h-20 bg-white" :style="{top: `343px`, left: `517px`}">modal</div>
    </section>
</template>



