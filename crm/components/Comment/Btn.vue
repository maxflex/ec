<script setup lang="ts">
import type { CommentDialog } from '#build/components'
import { mdiComment } from '@mdi/js'

const {
  entityId,
  entityType,
  count,
  color,
  extra: extraProp,
  variant = 'plain',
  size = 48,
} = defineProps<{
  count?: number
  entityId: number
  entityType: EntityType
  size?: number
  color?: string
  /**
   * Сохраняет/подгружает комменты ТОЛЬКО для текущей страницы
   * Берется текущая страница из useRoute и сохраняется в extra
   */
  extra?: boolean
  variant?: 'flat' | 'text' | 'elevated' | 'tonal' | 'outlined' | 'plain'
}>()

const commentDialog = ref<InstanceType<typeof CommentDialog>>()
const localCount = ref<number>(0)
const route = useRoute()
const extra = extraProp ? (route.name as string) : undefined

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
        extra,
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
    @click.stop="commentDialog?.open()"
  >
    <v-btn
      v-bind="$attrs"
      :icon="mdiComment"
      :size="size"
      :variant="variant"
      :color="color"
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
    :extra="extra"
    @created="localCount++"
  />
</template>
