<script setup lang="ts">
import type { ClientResource } from '.'
import type { SwampListResource } from '../Swamp'

const { client } = defineProps <{ client: ClientResource }>()

const filters = useAvailableYearsFilter()
const contractIds = ref<number[]>([])

const { items, indexPageData, availableYears } = useIndex<SwampListResource>(
  `swamps`,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      client_id: client.id,
    },
  },
)

async function loadContractIds() {
  const { data } = await useHttp<number[]>(
    `contracts`,
    {
      params: {
        client_id: client.id,
        year: filters.value.year,
        pluck: 'id',
      },
    },
  )
  contractIds.value = data.value!
}

watch(filters.value, loadContractIds)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template #buttons>
      <v-menu>
        <template #activator="{ props }">
          <v-btn v-if="filters.year" color="primary" v-bind="props">
            управление группами
          </v-btn>
        </template>
        <v-list>
          <v-list-item
            v-for="id in contractIds"
            :key="id"
            :to="{ name: 'schedule-drafts-editor', query: { contract_id: id } }"
          >
            договор №{{ id }}
          </v-list-item>
          <v-divider v-if="contractIds.length" />
          <v-list-item :to="{ name: 'schedule-drafts-editor', query: { client_id: client.id } }">
            новый договор
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <SwampList :items="items" />
  </UiIndexPage>
</template>
