<script setup lang="ts">
import type { GroupSelectorDialog } from '#build/components'

const { items, selectable } = defineProps<{
  items: SwampListResource[]
  selectable?: boolean
}>()
const emit = defineEmits<{
  select: [swamp: SwampListResource]
  add: []
}>()
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()
</script>

<template>
  <div class="table swamp-list" :class="{ 'table--hover': selectable }">
    <div v-for="swamp in items" :key="swamp.id" class="swamp-item" @click="emit('select', swamp)">
      <div style="width: 150px">
        <NuxtLink
          v-if="swamp.group_id"
          :to="{ name: 'groups-id', params: { id: swamp.group_id } }"
        >
          Группа {{ swamp.group_id }}
        </NuxtLink>
        <UiIconLink
          v-else
          @click="groupSelectorDialog?.open(swamp)"
        >
          Прикрепить
        </UiIconLink>
      </div>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: swamp.client.id } }">
          {{ formatName(swamp.client) }}
        </NuxtLink>
      </div>

      <div style="width: 120px">
        {{ ProgramShortLabel[swamp.program] }}
      </div>
      <div style="width: 110px">
        <template v-if="swamp.total_lessons">
          {{ plural(swamp.total_lessons, ['урок', 'урока', 'уроков']) }}
        </template>
      </div>
      <div style="width: 150px">
        {{ swamp.total_price_passed }} / {{ swamp.total_price }}
      </div>
      <div style="width: 170px">
        договор №{{ swamp.contract_id }}
      </div>
      <div>
        <SwampStatus :item="swamp" />
      </div>
    </div>
  </div>
  <GroupSelectorDialog ref="groupSelectorDialog" @select="emit('add')" />
</template>

<style lang="scss">
.swamp-list {
  &.table--hover {
    .swamp-item {
      cursor: pointer;
      & > div:first-child {
        display: none;
      }
    }
  }
}
</style>
