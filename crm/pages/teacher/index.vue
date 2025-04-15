<script setup lang="ts">
const router = useRouter()

async function redirect() {
  await router.replace({ name: 'groups' })
}

nextTick(redirect)
</script>

<template>
  <div></div>
</template>
