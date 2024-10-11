<script setup lang="ts">
const { phone } = defineProps<{
  phone: CallAppPhoneResource | null
}>()

function stopPropagation(e: MouseEvent) {
  e.stopPropagation()
}
</script>

<template>
  <div>
    <template v-if="phone === null">
      Неизвестный
    </template>
    <template v-else-if="phone.entity_type === EntityTypeValue.client">
      Клиент:
      <RouterLink :to="{ name: 'clients-id', params: { id: phone.person.id } }" @click="stopPropagation">
        {{ formatName(phone.person) }}
      </RouterLink>
    </template>
    <template v-else-if="phone.entity_type === EntityTypeValue.clientParent">
      Родитель:
      <RouterLink :to="{ name: 'clients-id', params: { id: phone.person.id } }" @click="stopPropagation">
        {{ formatName(phone.person) }}
      </RouterLink>
    </template>
    <template v-else>
      <RouterLink :to="{ name: 'teachers-id', params: { id: phone.person.id } }" @click="stopPropagation">
        {{ formatFullName(phone.person) }}
      </RouterLink>
    </template>
  </div>
</template>
