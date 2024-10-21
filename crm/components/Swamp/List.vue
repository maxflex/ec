<script setup lang="ts">
import type { GroupSelectorDialog } from '#build/components'

const { items, group } = defineProps<{
  items: SwampListResource[]
  group?: GroupResource
}>()
const emit = defineEmits(['add', 'selected'])
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()

async function onSelect(swamp: SwampListResource) {
  if (!group || !confirm(`Добавить ученика ${formatName(swamp.client)} в группу ${group.id}?`)) {
    return
  }
  await useHttp(`client-groups`, {
    method: 'post',
    body: {
      group_id: group.id,
      contract_version_program_id: swamp.id,
    },
  })
  emit('selected')
}
</script>

<template>
  <div class="table swamp-list" :class="{ 'table--hover': !!group }">
    <div v-for="swamp in items" :key="swamp.id" class="swamp-item" @click="onSelect(swamp)">
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
        <!--        <v-btn -->
        <!--          v-else color="secondary" -->
        <!--          density="compact" -->
        <!--          variant="tonal" -->
        <!--          @click="groupSelectorDialog?.open(swamp)" -->
        <!--        > -->
        <!--          Добавить -->
        <!--        </v-btn> -->
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
