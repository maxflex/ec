<script lang="ts">
const PassFilterLabel = {
  hasUsedPasses: 'есть хотя бы 1 использованный пропуск',
  hasPasses: 'есть хотя бы 1 пропуск',
  noPasses: 'нет пропусков',
} as const

type PassFilter = keyof typeof PassFilterLabel

interface Filters {
  direction: Direction[]
  status: RequestStatus[]
  responsible_user_id: number[]
  is_from_internet?: number
  is_verified?: number
  passes: PassFilter[]
}

const filterDefaults: Filters = {
  direction: [],
  status: [],
  passes: [],
  responsible_user_id: [],
}

export default {
  label: 'Заявки',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div>
    <UiMultipleSelect
      v-model="filters.direction"
      :items="selectItems(DirectionLabel)"
      label="Направление"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.status"
      :items="selectItems(RequestStatusLabel)"
      label="Пропуски в заявке"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_from_internet"
      :items="yesNo('интернет', 'звонок')"
      label="Тип"
    />
  </div>
  <div>
    <UserSelector v-model="filters.responsible_user_id" label="Ответственный" multiple />
  </div>
  <div>
    <UiClearableSelect
      v-model="filters.is_verified"
      :items="yesNo()"
      label="Подтвержднённый номер телефона"
    />
  </div>
  <div>
    <UiMultipleSelect
      v-model="filters.passes"
      :items="selectItems(PassFilterLabel)"
      label="Пропуски в заявке"
    />
  </div>
</template>
