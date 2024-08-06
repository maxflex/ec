<script setup lang="ts">
import { mdiWeb } from '@mdi/js'

const { items } = defineProps<{ items: WebReviewResource[] }>()
defineEmits<{
  edit: [item: WebReviewResource]
}>()
</script>

<template>
  <div class="table">
    <div
      v-for="item in items"
      :id="`web-review-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="$emit('edit', item)"
        />
      </div>
      <div style="width: 50px">
        {{ item.id }}
      </div>
      <div
        v-if="item.client"
        style="width: 200px"
      >
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client.id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div
        style="width: 200px"
        class="text-truncate"
      >
        {{ item.signature }}
      </div>
      <div
        style="flex: 1"
        class="text-truncate"
      >
        {{ item.text }}
      </div>
      <div
        style="width: 50px"
        class="text-center"
      >
        <v-icon
          :class="`web-review--${item.is_published ? 'published' : 'unpublished'}`"
          :icon="mdiWeb"
          :color="item.is_published ? 'secondary' : 'gray'"
        />
      </div>
      <div
        style="width: 100px; flex: initial !important"
      >
        <UiRating v-model="item.rating" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.web-review {
  &--unpublished {
    opacity: 0.2;
  }
}
</style>
