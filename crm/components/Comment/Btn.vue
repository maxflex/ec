<script setup lang="ts">
import { mdiComment } from '@mdi/js'
import type { CommentDialog } from '#build/components'

const { entityId, entityType } = defineProps<{
  entityId: number
  entityType: EntityString
}>()

const commentDialog = ref<InstanceType<typeof CommentDialog>>()

const count = ref(0)

onMounted(() => loadData())

async function loadData() {
  const { data } = await useHttp<number>('comments', {
    params: {
      count: 1,
      entity_id: entityId,
      entity_type: EntityType[entityType],
    },
  })
  count.value = data.value || 0
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
      v-if="count > 0"
      floating
      :content="count"
    />
  </div>
  <CommentDialog
    ref="commentDialog"
    :entity-id="entityId"
    :entity-type="entityType"
    @created="count++"
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
