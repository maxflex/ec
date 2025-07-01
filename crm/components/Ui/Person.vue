<script setup lang="ts">
const { item, teacherFormat, noLink = undefined } = defineProps<{
  item: PersonResource
  teacherFormat?: NameFormat
  noLink?: boolean | undefined
}>()

const { isAdmin } = useAuthStore()
const isNoLink = noLink ?? !isAdmin
let to: string
let format: NameFormat

switch (item.entity_type) {
  case EntityTypeValue.teacher:
    to = 'teachers-id'
    format = teacherFormat ?? 'initials'
    break

  case EntityTypeValue.clientParent:
    to = 'parents-id'
    format = 'last-first'
    break

  default:
    to = 'clients-id'
    format = 'last-first'
}

const name = formatName(item, format).trim() || 'имя не указано'
</script>

<template>
  <span v-if="isNoLink">{{ name }}</span>
  <RouterLink v-else :to="{ name: to, params: { id: item.id } }" @click.stop>
    {{ name }}
    <slot />
  </RouterLink>
</template>
