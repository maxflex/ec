<script setup lang="ts">
import type { GroupSelectorDialog } from '#build/components'

const { items } = defineProps<{ items: SwampListResource[] }>()
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()
</script>

<template>
  <div class="table">
    <div v-for="swamp in items" :key="swamp.id">
      <div style="width: 200px">
        <NuxtLink
          v-if="swamp.group_id"
          :to="{ name: 'groups-id', params: { id: swamp.group_id } }"
        >
          Группа {{ swamp.group_id }}
        </NuxtLink>
        <a
          v-else
          class="link-icon"
          @click="groupSelectorDialog?.open(swamp.program, swamp.year, swamp.contract_id)"
        >
          прикрепить группу
          <v-icon
            :size="16"
            icon="$next"
          />
        </a>
      </div>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: swamp.client.id } }">
          {{ formatName(swamp.client) }}
        </NuxtLink>
      </div>

      <div style="width: 120px">
        {{ ProgramShortLabel[swamp.program] }}
      </div>
      <div style="width: 120px">
        <template v-if="swamp.lessons">
          {{ plural(swamp.lessons, ['урок', 'урока', 'уроков']) }}
        </template>
      </div>
      <div style="width: 200px">
        договор №{{ swamp.contract_id }}
      </div>
      <div>
        <span v-if="swamp.lessons_passed >= swamp.lessons">
          {{ swamp.group_id ? 'исполнено + в группе' : 'исполнено' }}
        </span>
        <span v-else-if="!swamp.group_id">
          к исполнению
        </span>
      </div>
      <div />
    </div>
  </div>
  <GroupSelectorDialog ref="groupSelectorDialog" />
</template>
