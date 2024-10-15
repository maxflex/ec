<script setup lang="ts">
import { mdiAccountCircle, mdiCheckAll } from '@mdi/js'

const { items } = defineProps<{
  items: PassResource[]
}>()

defineEmits<{
  edit: [pass: PassResource]
}>()
</script>

<template>
  <div class="pass-list-2">
    <div v-for="item in items" :key="item.id">
      <v-icon :icon="mdiAccountCircle" />
      <div>
        <div class="pass-list-2__title">
          <span>
            Пропуск на {{ formatDate(item.date) }}
          </span>
          <v-tooltip location="bottom">
            <template #activator="{ props }">
              <v-icon :class="item.used_at ? 'text-success' : 'text-gray'" :icon="mdiCheckAll" v-bind="props" />
            </template>
            {{ item.used_at ? formatDate(item.used_at) : 'не использован' }}
          </v-tooltip>
          <v-btn color="gray" icon="$edit" :size="20" variant="plain" @click="$emit('edit', item)" />
        </div>
        <div>
          {{ item.comment }}
        </div>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.pass-list-2 {
  margin-left: -4px;
  display: flex;
  flex-direction: column;
  gap: 16px;
  & > div {
    display: flex;
    gap: 12px;
    cursor: default;
    & > .v-icon {
      font-size: 50px;
      color: rgb(var(--v-theme-gray));
    }
  }
  &__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
    .v-icon {
      font-size: 16px;
    }
    .v-btn {
      top: -1px;
      opacity: 0;
    }
    &:hover {
      .v-btn {
        opacity: 1;
      }
    }
  }
}
</style>
