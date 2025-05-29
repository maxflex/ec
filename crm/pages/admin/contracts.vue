<script setup lang="ts">
import type { ClientDialog, ContractVersionDialog } from '#build/components'
import type { ClientListResource } from '~/components/Client'
import type { ContractVersionFilters } from '~/components/ContractVersion/Filters.vue'

const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()
const router = useRouter()

const filters = ref<ContractVersionFilters>(loadFilters({
  year: currentAcademicYear(),
  direction: [],
}))

const { items, indexPageData } = useIndex<ContractVersionListResource>(
  `contract-versions`,
  filters,
)

function onUpdated(cv: ContractVersionListResource) {
  const index = items.value.findIndex(e => e.id === cv.id)
  if (index !== -1) {
    items.value[index] = cv
    itemUpdated('contract-version', cv.id)
  }
}

function onClientCreated(c: ClientListResource) {
  router.push({ name: 'clients-id', params: { id: c.id } })
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ContractVersionFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="clientDialog?.create()">
        добавить клиента
        <template #append>
          <v-icon icon="$next" />
        </template>
      </v-btn>
    </template>
    <ContractVersionList
      :items="items"
      @edit="contractVersionDialog?.edit"
    />
    <ContractVersionDialog ref="contractVersionDialog" @updated="onUpdated" />
    <ClientDialog ref="clientDialog" @created="onClientCreated" />
  </UiIndexPage>
</template>
