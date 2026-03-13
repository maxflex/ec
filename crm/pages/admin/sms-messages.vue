<script setup lang="ts">
import type { SmsMessageListResource } from '~/components/SmsMessage'

interface SmsMessageFilters {
  number?: string
  status?: number
}

const route = useRoute()
const { number: numberFromQuery } = route.query
const initialFilters: SmsMessageFilters = numberFromQuery
  ? {
      // При входе по ссылке с номером сразу подставляем фильтр из URL.
      number: formatPhone(numberFromQuery),
    }
  : {}

const filters = ref<SmsMessageFilters>(initialFilters)
const numberInput = ref(filters.value.number || '')

// Применяем номер только по Enter/blur, чтобы не стрелять запросом на каждую клавишу.
function applyNumberFilter() {
  filters.value.number = numberInput.value || undefined
}

function clearNumberFilter() {
  numberInput.value = ''
  filters.value.number = undefined
}

watch(() => filters.value.number, (value) => {
  numberInput.value = value || ''
})

const { items, indexPageData } = useIndex<SmsMessageListResource>(`sms-messages`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <div class="relative">
        <v-text-field
          v-model="numberInput"
          label="Номер телефона"
          density="comfortable"
          @keydown.enter="applyNumberFilter"
          @blur="applyNumberFilter"
        />
        <UiUnderInput v-if="numberInput" @click="clearNumberFilter" />
      </div>
      <UiClearableSelect v-model="filters.status" :items="yesNo('доставлено', 'не доставлено')" label="Статус доставки" density="comfortable" />
    </template>
    <SmsMessageList :items="items" />
  </UiIndexPage>
</template>
