<script setup lang="ts">
interface Props {
  item: PersonResource
  teacherFormat?: NameFormat
  noLink: boolean | undefined
}

const { item, teacherFormat, noLink } = withDefaults(defineProps<Props>(), {
  noLink: undefined,
})

const { isAdmin } = useAuthStore()
const isNoLink = noLink ?? !isAdmin
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
  <RouterLink v-else :to="{ name: to, params: { id: item.id } }" @click.stop>
    {{ name }}
  </RouterLink>
</template>
