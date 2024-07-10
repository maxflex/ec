<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'
import type { LessonDialog } from '#build/components'

const { items } = defineProps<{
  items: TopicListResource[]
}>()

const lessonDialog = ref<InstanceType<typeof LessonDialog>>()
</script>

<template>
  <div class="table">
    <div v-for="l in items" :key="l.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="lessonDialog?.edit(l.id)"
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
          {{ formatNameShort(l.teacher) }}
        </nuxtlink>
      </div>
      <div style="flex: 1" class="text-truncate">
        {{ l.topic }}
      </div>
      <div class="text-right" style="width: 30px; flex: initial">
        <v-icon
          :class="{
            'opacity-2': !l.is_topic_verified,
          }"
          :icon="mdiCheckAll"
          :color="l.is_topic_verified ? 'secondary' : 'gray'"
        />
      </div>
    </div>
  </div>
  <LessonDialog ref="lessonDialog" />
</template>
