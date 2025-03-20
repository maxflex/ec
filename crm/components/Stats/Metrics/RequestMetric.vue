<script lang="ts">
const PassFilterLabel = {
  hasUsedPasses: 'есть хотя бы 1 использованное разрешение',
  hasPasses: 'есть хотя бы 1 разрешение',
  noPasses: 'нет разрешений',
} as const

type PassFilter = keyof typeof PassFilterLabel

interface Filters {
  direction: Direction[]
  status: RequestStatus[]
  responsible_user_id: number[]
  is_from_internet?: number
  is_verified?: number
  pass?: PassFilter
}

const filterDefaults: Filters = {
  direction: [],
  status: [],
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
      label="Статус"
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
    <UiClearableSelect
      v-model="filters.pass"
      :items="selectItems(PassFilterLabel)"
      label="Разрешения на пропуска в заявке"
    />
  </div>
</template>
