<script setup lang="ts">
import type { TopicDialog } from '#build/components'
import { mdiCheckAll } from '@mdi/js'

const { items } = defineProps<{
  items: TopicListResource[]
}>()

const topicDialog = ref<InstanceType<typeof TopicDialog>>()

function onUpdated(item: TopicListResource) {
  const index = items.findIndex(e => e.id === item.id)
  // eslint-disable-next-line
  items.splice(index, 1, item)
}
</script>

<template>
  <div class="table">
    <div v-for="l in items" :id="`topic-${l.id}`" :key="l.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="topicDialog?.edit(l)"
        />
      </div>
      <div style="width: 100px">
        {{ formatDate(l.date) }}
      </div>
      <div style="width: 50px">
        {{ formatTime(l.time) }}
      </div>
      <div style="width: 170px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: l.teacher.id } }">
          {{ formatNameInitials(l.teacher) }}
        </nuxtlink>
      </div>
      <div style="flex: 1" class="text-truncate pr-10">
        <span v-if="l.topic">
          {{ l.topic }}
        </span>
        <span v-else class="text-error">
          тема не установлена
        </span>
      </div>
      <div style="width: 30px; flex: initial">
        <v-icon
          v-if="l.topic"
          :class="{
            'opacity-2': !l.is_topic_verified,
          }"
          :icon="mdiCheckAll"
          :color="l.is_topic_verified ? 'secondary' : 'gray'"
        />
      </div>
    </div>
  </div>
  <TopicDialog ref="topicDialog" @updated="onUpdated" />
</template>
