<script setup lang="ts">
const { items, meta } = defineProps<{
  items: PersonResource[]
  meta: Meta
}>()
const isSelectAll = ref(false)
const selected = ref<number[]>([])

function toggleSelect(item: PersonResource) {
  const index = selected.value.findIndex(id => id === item.id)
  index === -1
    ? selected.value.push(item.id)
    : selected.value.splice(index, 1)
}
</script>

<template>
  <v-table hover class="people-selector-list">
    <tbody>
      <tr @click="isSelectAll = !isSelectAll">
        <td colspan="100">
          <v-icon
            v-if="isSelectAll"
            color="secondary"
            icon="$checkboxOn"
          />
          <v-icon
            v-else
            icon="$checkboxOff"
            class="opacity-6"
          />
          <span>
            всего: {{ meta.total }}
          </span>
        </td>
      </tr>
      <tr v-for="item in items" :key="item.id" @click="toggleSelect(item)">
        <td>
          <v-icon
            v-if="isSelectAll || selected.some(id => id === item.id)"
            color="secondary"
            icon="$checkboxOn"
          />
          <v-icon
            v-else
            icon="$checkboxOff"
            class="opacity-6"
          />
          <RouterLink :to="{ name: 'clients-id', params: { id: item.id } }">
            {{ formatName(item) }}
          </RouterLink>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.people-selector-list {
  user-select: none;
  td {
    &:first-child {
      display: flex;
      align-items: center;
      gap: 12px;
    }
  }
}
</style>
