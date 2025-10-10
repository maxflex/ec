<script setup lang="ts">
const { item } = defineProps<{
  item: {
    id: number
    is_confirmed: boolean
    teacher_id?: number
    contract_id?: number | null
  }
}>()

const isConfirmed = ref(item.is_confirmed)

const apiUrl = computed<string>(() => {
  if ('teacher_id' in item) {
    return 'teacher-payments'
  }
  return item.contract_id ? 'contract-payments' : 'other-payments'
})

function toggle() {
  isConfirmed.value = !isConfirmed.value
  useHttp(`${apiUrl.value}/${item.id}`, {
    method: 'put',
    body: {
      is_confirmed: isConfirmed.value,
    },
  })
}
</script>

<template>
  <span :class="isConfirmed ? 'text-success' : 'text-gray'" class="cursor-pointer unselectable" @click="toggle()">
    {{ isConfirmed ? 'подтверждён' : 'не подтверждён' }}
  </span>
</template>
