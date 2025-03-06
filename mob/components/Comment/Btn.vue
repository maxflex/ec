<script setup lang="ts">
import type { CommentDialog } from '#build/components'
import { mdiComment } from '@mdi/js'

const {
  entityId,
  entityType,
  count,
  variant = 'plain',
  size = 48,
} = defineProps<{
  count?: number
  entityId: number
  entityType: EntityType
  size?: number
  variant?: 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
}>()

const commentDialog = ref<InstanceType<typeof CommentDialog>>()

const localCount = ref<number>(0)

onMounted(() => loadData())

async function loadData() {
  if (count !== undefined) {
    localCount.value = count
    return
  }
  const { data } = await useHttp<number>(
    `comments`,
    {
      params: {
        count: 1,
        entity_id: entityId,
        entity_type: entityType,
      },
    },
  )
  localCount.value = data.value || 0
}
</script>

<template>
  <div
    class="badge"
    @click="commentDialog?.open()"
  >
    <v-btn
      v-bind="$attrs"
      :icon="mdiComment"
      :size="size"
      :variant="variant"
    />
    <v-badge
      v-if="localCount > 0"
      floating
      :content="localCount"
    />
  </div>
  <CommentDialog
    ref="commentDialog"
    :entity-id="entityId"
    :entity-type="entityType"
    @created="localCount++"
  />
</template>
