<script setup lang="ts">
const { item, teacherFormat, noLink } = defineProps<{
  item: PersonResource
  teacherFormat?: NameFormat
  noLink?: boolean
}>()

const { user } = useAuthStore()
const isNoLink = noLink ?? user?.entity_type !== EntityTypeValue.user
let to: string
let format: NameFormat

switch (item.entity_type) {
  case 'App\\Models\\Teacher':
    to = 'teachers-id'
    format = teacherFormat ?? 'initials'
    break

  case 'App\\Models\\ClientParent':
    to = 'parents-id'
    format = 'last-first'
    break

  default:
    to = 'clients-id'
    format = 'last-first'
}

const name = formatName(item, format)
</script>

<template>
  <span v-if="isNoLink">{{ name }}</span>
  <RouterLink v-else :to="{ name: to, params: { id: item.id } }">
    {{ name }}
  </RouterLink>
</template>
