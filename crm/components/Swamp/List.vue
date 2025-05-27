<script setup lang="ts">
const { items, group } = defineProps<{
  items: SwampListResource[]
  group?: GroupResource
}>()

const emit = defineEmits(['attach', 'selected'])
const route = useRoute()
const canAttach = route.name === 'clients-id'

async function onSelect(swamp: SwampListResource) {
  if (!group || !confirm(`Добавить ученика ${formatName(swamp.client)} в группу ${group.id}?`)) {
    return
  }
  await useHttp(
    `client-groups`,
    {
      method: 'post',
      body: {
        group_id: group.id,
        contract_version_program_id: swamp.id,
      },
    },
  )
  emit('selected')
}

function attach(swamp: SwampListResource) {
  if (!canAttach) {
    return
  }
  emit('attach', swamp)
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
          :class="{ 'no-pointer-events': !canAttach }"
          @click="attach(swamp)"
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
</template>

<style lang="scss">
.swamp-list {
  .no-pointer-events {
    color: black !important;
  }
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
