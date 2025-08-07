<script setup lang="ts">
import { Cabinets } from '.'

interface BusyCabinet {
  cabinet: string
  is_busy: boolean
}

const { date, dateEnd, time, groupId, weekday } = defineProps<{
  groupId: number
  date?: string
  dateEnd?: string
  time?: string
  weekday?: Weekday
}>()

const modelDefaults: BusyCabinet[] = Object.keys(Cabinets)
  .filter(c => Cabinets[c].capacity > 0)
  .map(c => ({
    cabinet: c,
    is_busy: false,
  }))

const items = ref<BusyCabinet[]>(modelDefaults)

const model = defineModel<string | null>()

// при установке даты и времени, загружаем свободные кабинеты
async function loadFreeCabinets() {
  if (date && time && time.length === 5) {
    const { data } = await useHttp<BusyCabinet[]>(
      `cabinets/free`,
      {
        params: {
          date,
          time,
          weekday,
          date_end: dateEnd,
          group_id: groupId,
        },
      },
    )
    items.value = data.value!
  }
  else {
    items.value = modelDefaults
  }

  console.log('loadFreeCabinets', items.value)
}

watch(() => date, loadFreeCabinets)
watch(() => time, loadFreeCabinets)
watch(() => dateEnd, loadFreeCabinets)
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="items"
    item-value="cabinet"
  >
    <template #selection="{ item }">
      {{ Cabinets[item.value].label }}
    </template>
    <template #item="{ props, item }">
      <v-list-item
        v-bind="props"
        :class="{ 'text-gray': item.raw.is_busy }"
      >
        <template #prepend />
        <template #title>
          <span style="width: 50px; display: inline-block;">
            {{ Cabinets[item.value].label }}
          </span>
          <span class="text-gray">
            max: {{ Cabinets[item.value].capacity }}
          </span>
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>
