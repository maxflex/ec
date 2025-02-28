<script setup lang="ts">
interface BusyCabinet {
  cabinet: Cabinet
  is_busy: boolean
}

const { label = 'Кабинет', date, dateEnd, time, groupId } = defineProps<{
  groupId: number
  label?: string
  date?: string
  dateEnd?: string
  time?: string
}>()

const modelDefaults: BusyCabinet[] = Object.keys(CabinetLabel).map(id => ({
  cabinet: id as Cabinet,
  is_busy: false,
}))

const items = ref<BusyCabinet[]>(modelDefaults)

const model = defineModel<Cabinet | null >()

// при установке даты и времени, загружаем свободные кабинеты
async function loadFreeCabinets() {
  console.log('watch triggered', date, time)
  if (date && time && time.length === 5) {
    const { data } = await useHttp<BusyCabinet[]>(
      `cabinets/free`,
      {
        params: {
          date,
          time,
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
    :item-title="({ cabinet }) => CabinetLabel[cabinet]"
    :label="label"
  >
    <template #item="{ props, item }">
      <v-list-item v-bind="props" :class="{ 'text-gray': item.raw.is_busy }">
        <template #prepend />
        <template #title>
          {{ item.title }}
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>
