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

const year = currentAcademicYear()

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
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ProgramSelector v-model="filters.program" multiple />
      <UiClearableSelect v-model="filters.is_risk" label="Группа риска" :items="yesNo()" density="comfortable" />
    </template>
    <template #buttons>
      <UiQuestionTooltip>
        На данной странице отображаются клиенты, допущенные ко входу в личный кабинет.
        Доступ сохраняется до 30 июня {{ year + 1 }}.
        Если у клиента заключён договор на следующий учебный год, срок продлевается до 30 июня {{ year + 2 }}.
        Доступ также может быть закрыт раньше — в случае полного расторжения договора.
      </UiQuestionTooltip>
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
          <ClientDirections :item="item.directions" />
        </div>
        <div>
          <TeethBar :items="item.schedule!" />
        </div>
      </div>
    </div>
  </UiIndexPage>
  <ClientDialog ref="clientDialog" />
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
