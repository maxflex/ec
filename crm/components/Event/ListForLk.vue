<script setup lang="ts">
import type { EventListResource } from '.'

const { items } = defineProps<{
  items: EventListResource[]
}>()
const router = useRouter()
</script>

<template>
  <Table class="table--padding event-list" hoverable>
    <TableRow v-for="item in items" :key="item.id" class="cursor-pointer" @click="router.push({ name: 'events-id', params: { id: item.id } })">
      <TableCol class="event-list__img">
        <div v-if="item.file" :style="{ backgroundImage: `url(${item.file.url})` }" />
      </TableCol>

      <TableCol :width="100">
        <span>
          {{ formatDate(item.date) }}
        </span>
        <div class="event-list__confirmation text-gray">
          {{ formatTime(item.time!) }}
          <template v-if="item.time_end">
            – {{ item.time_end }}
          </template>
        </div>
      </TableCol>

      <TableCol>
        {{ item.name }}
        <div class="event-list__description text-gray">
          {{ item.description }}
        </div>
      </TableCol>
      <TableCol style="width: 150px; flex: initial">
        <span v-if="item.participant_counts.confirmed === 0" class="text-gray">
          нет участников
        </span>
        <span v-else>
          {{ item.participant_counts.confirmed }} участников
        </span>
        <div
          v-if="item.me"
          class="event-list__confirmation"
          :class="{
            'text-success': item.me.confirmation === 'confirmed',
            'text-error': item.me.confirmation === 'rejected',
            'text-gray': item.me.confirmation === 'pending',
          }"
        >
          {{ EventParticipantConfirmationLkLabel[item.me.confirmation] }}
        </div>
      </TableCol>
    </TableRow>
  </Table>
</template>
