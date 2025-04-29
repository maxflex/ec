<script setup lang="ts">
import { BackButton } from 'vue-tg'

const { history, isGogingBack } = useHistory()

const router = useRouter()

function goBack() {
  isGogingBack.value = true
  history.value--
  router.back()
}
</script>

<template>
  <BackButton v-if="history > 1" @click="goBack()" />
</template>
