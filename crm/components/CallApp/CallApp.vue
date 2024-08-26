<script setup lang="ts">
import { callAppDialog, hasIncoming, isMissed } from '~/components/CallApp/index'

const { $addSseListener } = useNuxtApp()
const activeCalls = ref<CallEvent[]>([])
const banners = ref<CallEvent[]>([])

function handleCallEvent(ce: CallEvent) {
  console.log(ce)
  console.log(ce.state, ce.type, ce.user)
  const index = activeCalls.value.findIndex(e => e.number === ce.number)
  const bannerIndex = banners.value.findIndex(e => e.number === ce.number)
  switch (ce.state) {
    case 'Appeared':
      activeCalls.value.unshift(ce)
      banners.value.unshift(ce)
      break

    case 'Connected':
      index !== -1
        ? activeCalls.value.splice(index, 1, ce)
        : activeCalls.value.unshift(ce)
      if (bannerIndex !== -1) {
        banners.value.splice(bannerIndex, 1, ce)
        setTimeout(() => closeBanner(ce), 3000)
      }
      break

    case 'Disconnected':
      if (index !== -1) {
        activeCalls.value.splice(index, 1)
      }
      if (bannerIndex !== -1) {
        // если звонок пропущен, закрываем не сразу
        if (isMissed(ce)) {
          banners.value.splice(bannerIndex, 1, ce)
          setTimeout(() => closeBanner(ce), 3000)
        }
        else {
          banners.value.splice(bannerIndex, 1)
        }
      }
  }
}

function closeBanner(ce: CallEvent) {
  const index = banners.value.findIndex(e => e.number === ce.number)
  banners.value.splice(index, 1)
}

function onBannerClick(ce: CallEvent) {
  callAppDialog.value = true
  closeBanner(ce)
}

watch(activeCalls.value, (newVal) => {
  hasIncoming.value = false
  nextTick(() => hasIncoming.value = newVal.some(e => e.state === 'Appeared'))
})

onMounted(() => {
  $addSseListener('CallEvent', handleCallEvent)
})
</script>

<template>
  <CallAppDialog />
  <div class="call-app__banners">
    <v-slide-y-reverse-transition group>
      <CallAppBanner
        v-for="item in banners"
        :key="item.number"
        :item="item"
        @click="onBannerClick(item)"
        @close="closeBanner(item)"
      />
    </v-slide-y-reverse-transition>
  </div>
</template>

<style lang="scss">
.call-app {
  &__banners {
    position: fixed;
    bottom: 0;
    left: 256px;
    z-index: 101;
    width: calc(100vw - 256px);
  }
}
</style>
