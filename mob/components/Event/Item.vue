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
    <div style="width: 140px">
      <EventStatus :item="item" />
    </div>
  </div>
</template>

<style lang="scss">
.event-item {
  position: relative;
  align-items: flex-start !important;
  // background: rgba(#9c27b0, 0.05);
  padding: 20px;
  cursor: pointer;
  .table-actionss {
    right: -10px !important;
    // top: -16px !important;
    height: 60px !important;
  }
  .v-chip {
    top: -4px !important;
  }
  &:after {
    content: '';
    background: rgba(#9c27b0, 0.05);
    // background: rgba(#337ab7, 0.05);
    width: calc(100% - 110px);
    height: 100%;
    position: absolute;
    left: 110px;
    top: 0;
    border-radius: 8px;
    pointer-events: none;
    transition: background-color linear 0.2s;
  }
  &--study {
    &:after {
      background: rgba(#fe8a1e, 0.05);
    }
    &:hover {
      &:after {
        background: rgba(#fe8a1e, 0.1) !important;
      }
    }
  }
  &:hover {
    &:after {
      background: rgba(#9c27b0, 0.1);
    }
  }
}
</style>
