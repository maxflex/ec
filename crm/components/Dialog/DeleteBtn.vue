<script setup lang="ts">
const { confirmText = 'Вы уверены, что хотите удалить запись?', apiUrl, id } = defineProps<{
  confirmText?: string
  apiUrl: string
  id: number | undefined
}>()
const emit = defineEmits(['deleted'])
const deleting = ref(false)
async function destroy() {
  if (!confirm(confirmText)) {
    return
  }
  deleting.value = true
  const { error } = await useHttp(`${apiUrl}/${id}`, {
    method: 'delete',
  })
  if (error.value) {
    deleting.value = false
    useGlobalMessage(`Невозможно удалить запись. ${error.value.data?.message}`, 'error')
  }
  else {
    useGlobalMessage(`Запись удалена`, 'success')
    emit('deleted')
    setTimeout(() => (deleting.value = false), 300)
  }
}
</script>

<template>
  <v-btn
    v-if="!!id"
    :loading="deleting"
    :size="48"
    class="remove-btn"
    icon="$delete"
    variant="text"
    @click="destroy()"
  />
</template>
