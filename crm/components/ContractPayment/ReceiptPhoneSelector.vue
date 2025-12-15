<script setup lang="ts">
const { contractId } = defineProps<{ contractId: number }>()
const input = ref()
const loading = ref(false)
const model = defineModel<string | undefined>({ required: true })
const items = ref<PhoneResource[]>([])
const isMenuOpen = ref(false)

// Обработчик "перехвата" нажатия
function handleInteraction(e: MouseEvent) {
  // Если данные уже есть, ничего не перехватываем - пусть Vuetify работает штатно
  if (items.value.length > 0)
    return

  // Если данных нет:
  // 1. Останавливаем стандартное открытие меню Vuetify
  e.preventDefault()
  e.stopPropagation()

  // 2. Запускаем загрузку
  loadData()
}

async function loadData() {
  if (loading.value || items.value.length > 0) {
    isMenuOpen.value = true
    return
  }

  loading.value = true
  const { data } = await useHttp<ApiResponse<PhoneResource>>(
    `phones`,
    {
      params: {
        contract_id: contractId,
      },
    },
  )
  items.value = data.value!.data
  loading.value = false
  isMenuOpen.value = true
}

function clear() {
  model.value = undefined
  nextTick(() => input.value.blur())
}
</script>

<template>
  <v-select
    ref="input"
    v-model="model"
    v-model:menu="isMenuOpen"
    :loading="loading"
    label="Отправить чек"
    item-value="number"
    :items="items"
    :menu-props="{
      openOnClick: false,
    }"
    @mousedown.capture="handleInteraction"
  >
    <template #prepend-item>
      <v-list-item
        base-color="gray"
        @click="clear()"
      >
        <v-list-item-title class="gray">
          не установлено
        </v-list-item-title>
        <template #prepend>
          <v-spacer />
        </template>
      </v-list-item>
    </template>
    <template #selection="{ item }">
      {{ formatPhone(item.value) }}
    </template>
    <template #item="{ item, props }">
      <v-list-item v-bind="props">
        <template #prepend />
        <template #title>
          <div class="d-flex align-center">
            <span style="width: 150px">
              {{ formatPhone(item.value) }}
            </span>
            <span v-if="item.raw.comment" class="text-gray">
              {{ item.raw.comment }}
            </span>
          </div>
        </template>
      </v-list-item>
    </template>
  </v-select>
</template>
