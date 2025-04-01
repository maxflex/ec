<script setup lang="ts">
const route = useRoute()
const event = ref<EventResource>()

async function loadData() {
  const { data } = await useHttp<EventResource>(
    `events/${route.params.id}`,
  )
  event.value = data.value!
}

nextTick(loadData)
</script>

<template>
  <GroupMessagePeopleSelector v-if="event" :event="event" />
</template>
