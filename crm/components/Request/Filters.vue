<script lang="ts" setup>
export interface Filters {
  status?: RequestStatus
  direction: Direction[]
}

const requestStatusDescription: Record<RequestStatus, string> = {
  new: 'Необработанные заявки',
  finished: 'Заявки от реальных потенциальных клиентов',
  waiting: 'НБТ, дубли, невозможно связаться, бесплатное обучение и заявки с неподтвержденными номерами',
  refused: 'Отказы со стороны ЕГЭ-Центра потенциальным ученикам',
} as const

const model = defineModel<Filters>({ required: true })
</script>

<template>
  <UiClearableSelect
    v-model="model.status"
    label="Статус"
    :items="selectItems(RequestStatusLabel)"
    density="comfortable"
    expand
  >
    <template #item="{ item, props }">
      <v-list-item v-bind="props">
        <template #prepend />
        <template #title>
          <div style="" class="request-status-selector">
            <div>
              {{ item.title }}
            </div>
            <div class="gray-subtitle">
              {{ requestStatusDescription[item.value as RequestStatus] }}
            </div>
          </div>
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>

  <UiMultipleSelect
    v-model="model.direction"
    label="Направление"
    :items="selectItems(DirectionLabel)"
    density="comfortable"
  />
</template>

<style lang="scss">
.request-status-selector {
  padding: 2px 0;
  max-width: 400px;
  .gray-subtitle {
    white-space: break-spaces;
  }
}
</style>
