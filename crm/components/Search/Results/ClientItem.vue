<script setup lang="ts">
import type { SearchResultResource } from '..'

const { item } = defineProps<{
  item: SearchResultResource
}>()

const client = item.client!
</script>

<template>
  <div class="align-start">
    <div style="width: 200px">
      <UiPerson :item="item" />
    </div>
    <div style="width: 140px" class="text-lowercase">
      {{ EntityTypeLabel[item.entity_type] }}
    </div>
    <div style="width: 200px">
      <span v-if="client.directions.length === 0" class="text-gray">
        нет договоров
      </span>
      <span v-else>
        {{ client.directions.map(e => DirectionLabel[e]).join(', ') }}
      </span>
    </div>
    <div style="width: 250px">
      <div v-for="cv in client.contract_versions" :key="cv.id">
        договор на {{ YearLabel[cv.contract.year] }}
      </div>
    </div>
    <div style="flex: 1">
      <PhoneList :items="item.phones" show-comment />
    </div>
    <!-- <div style="width: 130px; flex: initial" class="text-lowercase text-gray opacity-5">
      {{ EntityTypeLabel[item.entity_type] }}
    </div> -->
  </div>
</template>
