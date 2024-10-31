<script setup lang="ts">
import type { ClientDialog } from '#build/components'

const filters = ref<ClientFilters>(loadFilters({
  q: '',
  year: currentAcademicYear(),
}))

const clientDialog = ref<InstanceType<typeof ClientDialog>>()
const { items, indexPageData } = useIndex<ClientListResource, ClientFilters>(
  `clients`,
  filters,
)

function onClientCreated(c: ClientListResource) {
  items.value.unshift(c)
  itemUpdated('clients', c.id)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="clientDialog?.create()">
        добавить клиента
        <template #append>
          <v-icon icon="$next" />
        </template>
      </v-btn>
    </template>
    <ClientList :items="items" />
    <ClientDialog ref="clientDialog" @created="onClientCreated" />
  </UiIndexPage>
</template>
