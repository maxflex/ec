<script setup lang="ts">
import type { GroupSelectorDialog } from '#build/components'

const { items } = defineProps<{ items: SwampListResource[] }>()
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()
</script>

<template>
  <div class="table swamp-list">
    <div v-for="swamp in items" :key="swamp.id" class="swamp-item">
      <div style="width: 150px">
        <NuxtLink
          v-if="swamp.group_id"
          :to="{ name: 'groups-id', params: { id: swamp.group_id } }"
        >
          Группа {{ swamp.group_id }}
        </NuxtLink>
        <UiIconLink
          v-else
          @click="groupSelectorDialog?.open(swamp.program, swamp.year, swamp.contract_id)"
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
      <div style="width: 180px">
        договор №{{ swamp.contract_id }}
      </div>
      <div>
        <SwampStatus :item="swamp" />
      </div>
    </div>
  </div>
  <GroupSelectorDialog ref="groupSelectorDialog" />
</template>
