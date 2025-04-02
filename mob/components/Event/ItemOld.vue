<script setup lang="ts">
import { mdiAccountGroup, mdiCheckAll } from '@mdi/js'

const { item } = defineProps<{ item: EventListResource }>()

defineEmits<{
  edit: [id: number]
  participants: [e: EventListResource]
}>()

const router = useRouter()

const isEditable = useAuthStore().user?.entity_type === EntityTypeValue.user
const isConfirmed = ref<boolean>(item.participant?.confirmation === 'confirmed')

function confirmParticipant() {
  if (!confirm(`Вы подтверждаете участие в событии ${item.name}?`)) {
    return
  }
  isConfirmed.value = true
  useHttp(`event-participants/${item.participant!.id}`, {
    method: 'put',
    body: {
      confirmation: 'confirmed',
    },
  })
}

function onClick(e: EventListResource) {
  router.push({
    name: 'events-id',
    params: {
      id: e.id,
    },
  })
}
</script>

<template>
  <div
    :id="`event-${item.id}`"
    :class="{ 'event-item--study': !item.is_afterclass }"
    class="event-item"
    @click="onClick(item)"
  >
    <div v-if="isEditable" class="table-actionss">
      <v-btn
        icon="$edit"
        :size="48"
        variant="text"
        color="gray"
        @click.stop="$emit('edit', item.id)"
      />
    </div>
    <div style="width: 90px" />
    <div style="width: 120px">
      {{ formatTime(item.time!) }}
      <template v-if="item.time_end">
        – {{ item.time_end }}
      </template>
    </div>
    <div style="width: 500px">
      {{ item.name }}
      <div v-if="item.description" class="mt-4 pr-10">
        {{ filterTruncate(item.description, 100) }}
      </div>
      <div v-if="item.participant" class="mt-4">
        <span v-if="isConfirmed" class="text-gray">
          <v-icon :icon="mdiCheckAll" size="16" class="vfn-1 mr-1" />
          Вы подтвердили участие
        </span>
        <a v-else class="cursor-pointer" @click="confirmParticipant()">
          <v-icon icon="$complete" size="16" class="vfn-1 mr-1" />
          Подтвердить участие
        </a>
      </div>
    </div>
    <div style="width: 80px; display: flex; align-items: center">
      <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
      {{ item.participants_count }}
    </div>
    <div style="width: 100px">
    </div>
    <div style="width: 140px">
      <EventStatus :item="item" />
    </div>
  </div>
</template>
