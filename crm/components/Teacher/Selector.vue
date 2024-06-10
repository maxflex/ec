<script setup lang="ts">
import type { Teachers } from '~/utils/models'

const { label } = defineProps<{ label: string }>()
const model = defineModel<number | null>()
const teachers = ref<Teachers>([])
const loading = ref(true)

async function loadData() {
  const { data } = await useHttp<ApiResponse<Teachers>>('teachers')
  if (data.value) {
    teachers.value = data.value.data
  }
  loading.value = false
}

onMounted(() => loadData())
</script>

<template>
  <v-select
    v-model="model"
    :label="label"
    :items="teachers"
    :item-title="(e) => formatFullName(e)"
    :item-value="(e) => e.id"
    :loading="loading"
  >
    <template #item="{ item, props }">
      <v-list-item
        :base-color="item.raw.status === 'active' ? undefined : 'gray'"
        v-bind="props"
      >
        <template #prepend />
      </v-list-item>
    </template>
  </v-select>
</template>
