<script setup lang="ts">
import { mdiComment } from '@mdi/js'
import type { CommentDialog } from '#build/components'

const { entityId, entityType, count } = defineProps<{
  count?: number
  entityId: number
  entityType: EntityString
}>()

const commentDialog = ref<InstanceType<typeof CommentDialog>>()

const localCount = ref<number>(0)

onMounted(() => loadData())

async function loadData() {
  if (count !== undefined) {
    localCount.value = count
    return
  }
  const { data } = await useHttp<number>('comments', {
    params: {
      count: 1,
      entity_id: entityId,
      entity_type: EntityTypeValue[entityType],
    },
  })
  localCount.value = data.value || 0
}
</script>

<template>
  <div
    class="comment-btn"
    @click="commentDialog?.open()"
  >
    <v-btn
      :icon="mdiComment"
      :size="48"
      variant="plain"
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

<style lang="scss">
.comment-btn {
  position: relative;
  .v-badge {
    position: absolute;
    right: 20px;
    top: 20px;
    cursor: pointer;
  }
}
</style>
