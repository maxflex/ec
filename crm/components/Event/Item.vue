<script setup lang="ts">
import { mdiAccountGroup, mdiCheckAll } from '@mdi/js'

const { item } = defineProps<{ item: EventListResource }>()
defineEmits<{
  edit: [e: EventListResource]
  participants: [e: EventListResource]
}>()

const isConfirmed = ref<boolean>(!!item.participant?.is_confirmed)

function confirmParticipant() {
  if (!confirm(`Вы подтверждаете участие в событии ${item.name}?`)) {
    return
  }
  isConfirmed.value = true
  useHttp(`event-participants/${item.participant!.id}`, {
    method: 'put',
    body: {
      is_confirmed: true,
    },
  })
}
</script>

<template>
  <div :id="`event-${item.id}`" class="event-item" :class="{ 'event-item--study': !item.is_afterclass }">
    <div class="table-actionss">
      <v-btn
        :icon="mdiAccountGroup"
        :size="48"
        variant="text"
        color="gray"
        @click="$emit('participants', item)"
      />
      <v-btn
        icon="$edit"
        :size="48"
        variant="text"
        color="gray"
        @click="$emit('edit', item)"
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
      <div v-if="item.description" class="mt-2 pr-10">
        {{ filterTruncate(item.description, 100) }}
      </div>
      <div v-if="item.participant" class="mt-2">
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
      <div class="event-status">
        <div class="event-status__circle" />
        событие
      </div>
    </div>
    <div>
      <v-chip v-if="item.is_afterclass" class="text-purple">
        внеклассное
      </v-chip>
      <v-chip v-else class="text-deepOrange">
        учебное
      </v-chip>
    </div>
  </div>
</template>

<style lang="scss">
.event-item {
  position: relative;
  align-items: flex-start !important;
  // background: rgba(#9c27b0, 0.05);
  padding: 20px;
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
    border-radius: 4px;
    pointer-events: none;
  }
  &--study {
    &:after {
      background: rgba(#fe8a1e, 0.05);
    }
  }
}

.event-status {
  display: flex;
  align-items: center;
  gap: 4px;
  --color: #9c27b0;
  color: var(--color);
  &__circle {
    --size: 8px;
    height: var(--size);
    width: var(--size);
    border-radius: 50%;
    background-color: var(--color);
    top: 1px;
    position: relative;
  }
}
</style>
