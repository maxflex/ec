<script setup lang="ts">
import type { ClientDialog } from '#components'
import type { ClientListResource } from '~/components/Client'

interface Filters {
  program: Program[]
  is_risk?: boolean
}

const filters = ref<Filters>(loadFilters({
  program: [],
}))

const router = useRouter()

const { items, indexPageData } = useIndex<ClientListResource>(
  `clients`,
  filters,
  {
    staticFilters: {
      can_login: 1,
    },
  },
)

const clientDialog = ref<InstanceType<typeof ClientDialog>>()

function onClientCreated(c: ClientListResource) {
  router.push({ name: 'clients-id', params: { id: c.id } })
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ProgramSelector v-model="filters.program" multiple />
      <UiClearableSelect v-model="filters.is_risk" label="Группа риска" :items="yesNo()" density="comfortable" />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="clientDialog?.create()">
        добавить клиента
      </v-btn>
    </template>
    <div class="active-clients table table--padding">
      <div
        v-for="item in items"
        :id="`clients-${item.id}`"
        :key="item.id"
      >
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientDialog?.edit(item.id)"
          />
        </div>
        <div style="width: 300px">
          <UiPerson :item="item" />
        </div>
        <div style="width: 300px">
          <ClientDirections :items="item.directions" />
        </div>
        <div>
          <TeethBar :items="item.schedule!" />
        </div>
      </div>
    </div>
  </UiIndexPage>
  <ClientDialog ref="clientDialog" @created="onClientCreated" />
</template>

<style lang="scss">
.active-clients {
  & > div {
    align-items: flex-start !important;
  }
  .teeth {
    position: relative;
    top: 4px;
  }
}
</style>
