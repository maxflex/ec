<script setup lang="ts">
import type { TopicDialog } from '#build/components'

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
  <Table>
    <TableRow v-for="l in items" :id="`topic-${l.id}`" :key="l.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          color="gray"
          @click="topicDialog?.edit(l)"
        />
      </div>
      <TableCol :width="100">
        {{ formatDate(l.date) }}
      </TableCol>
      <TableCol :width="50">
        {{ formatTime(l.time) }}
      </TableCol>
      <TableCol :width="170">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: l.teacher.id } }">
          {{ formatNameInitials(l.teacher) }}
        </nuxtlink>
      </TableCol>
      <TableCol class="text-truncate pr-10">
        <span v-if="l.topic">
          {{ l.topic }}
        </span>
        <span v-else class="text-error">
          тема не установлена
        </span>
      </TableCol>
      <TableCol style="width: 140px; flex: initial">
        <span v-if="l.is_topic_verified" class="text-success">
          подтверждена
        </span>
        <span v-else class="text-gray">
          не подтверждена
        </span>
      </TableCol>
    </TableRow>
  </Table>
  <TopicDialog ref="topicDialog" @updated="onUpdated" />
</template>
