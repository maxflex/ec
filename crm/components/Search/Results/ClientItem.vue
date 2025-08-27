<script setup lang="ts">
import type { SearchResultResource } from '..'

const { item } = defineProps<{
  item: SearchResultResource
}>()
const currentYear = currentAcademicYear()

const client = item.client!

function getYearAgo(year: Year): string {
  const diff = currentYear - year
  switch (diff) {
    case 0:
      return 'учился в этом году'

    case 1:
      return 'учился в прошлом году'

    case 2:
    case 3:
    case 4:
      return `учился ${diff} года назад`

    default:
      return `учился ${diff} лет назад`
  }
}
</script>

<template>
  <RouterLink
    :to="{
      name: item.entity_type === EntityTypeValue.client ? 'clients-id' : 'representatives-id',
      params: { id: item.id },
    }"
    class="align-start search-client-item"
  >
    <div style="width: 200px">
      <UiPerson :item="item" no-link />
    </div>
    <div style="width: 140px" class="text-lowercase">
      {{ EntityTypeLabel[item.entity_type] }}
    </div>
    <div style="width: 200px">
      <span v-if="Object.keys(client.directions).length === 0" class="text-gray">
        нет договоров
      </span>
      <span v-else>
        <ClientDirections :item="client.directions" />
      </span>
    </div>
    <div style="width: 250px">
      <span v-if="item.is_active" class="text-success"> активный клиент </span>
      <span v-else-if="client.max_contract_year">
        {{ getYearAgo(client.max_contract_year) }}
      </span>
    </div>
    <div style="flex: 1">
      <PhoneList :items="item.phones" show-comment />
    </div>
    <!-- <div style="width: 130px; flex: initial" class="text-lowercase text-gray opacity-5">
      {{ EntityTypeLabel[item.entity_type] }}
    </div> -->
  </RouterLink>
</template>

<style lang="scss">
.search-client-item {
  & > div {
    padding: 16px 0;
  }
}
</style>
