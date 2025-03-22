<script setup lang="ts">
import type { ClientReviewDialog } from '#build/components'
import { apiUrl, type ClientReviewListResource } from '.'

const props = defineProps<{
  items: ClientReviewListResource[]
  clientId?: number
  teacherId?: number
}>()
const { $addSseListener, $removeSseListener } = useNuxtApp()
const { items } = toRefs(props)
const { clientId, teacherId } = props
const clientReviewDialog = ref<InstanceType<typeof ClientReviewDialog>>()
const { isTeacher } = useAuthStore()
const { showGlobalMessage } = useGlobalMessage()

function onUpdated(cr: ClientReviewListResource) {
  const index = items.value.findIndex(e => e.id === cr.id)
  if (index === -1) {
    return
  }
  items.value[index] = cr
  itemUpdated('client-review', cr.id as number)
}

function onCreated(cr: ClientReviewListResource, fakeId: string) {
  const index = items.value.findIndex(e => e.id === fakeId)
  if (index === -1) {
    return
  }
  items.value[index] = cr
  itemUpdated('client-review', cr.id as number)
}

function onDeleted(id: number) {
  const index = items.value.findIndex(e => e.id === id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}

async function sendMessage(cr: ClientReviewListResource) {
  const { error } = await useHttp(
    `${apiUrl}/send-message`,
    {
      method: 'post',
      body: {
        id: cr.id,
      },
    },
  )

  if (error.value) {
    showGlobalMessage('Ожидается ответ на предыдущий запрос или у клиента не добавлен Telegram бот', 'error')
    return
  }

  showGlobalMessage('Запрос отправлен клиенту в Telegram', 'success')
}

function toggleMarked(cr: ClientReviewListResource, value: boolean) {
  useHttp(
    `${apiUrl}/${cr.id}`,
    {
      method: 'put',
      body: {
        is_marked: value,
      },
    },
  )
}

onUnmounted(() => $removeSseListener('ClientReviewMessageEvent'))
onMounted(() => $addSseListener('ClientReviewMessageEvent', ({ item, fakeId }: {
  item: ClientReviewListResource
  fakeId: string | null
}) => {
  console.log('ClientReviewMessageEvent', item, fakeId)
  fakeId
    ? onCreated(item, fakeId)
    : onUpdated(item)
}))
</script>

<template>
  <div class="table table--padding">
    <div v-for="item in items" :id="`client-review-${item.id}`" :key="item.id" :class="{ 'client-review--waiting': item.ttl > 0 }">
      <div style="width: 30px">
        <v-checkbox
          v-model="item.is_marked"
          style="position: absolute; top: calc(50% - 24px)"
          color="secondary"
          @update:model-value="(val) => toggleMarked(item, val as boolean)"
        />
      </div>
      <div v-if="!isTeacher && !teacherId" style="width: 170px">
        <UiPerson :item="item.teacher" />
      </div>
      <div v-if="!clientId" style="width: 170px">
        <UiPerson :item="item.client" />
      </div>
      <div style="width: 110px">
        {{ ProgramShortLabel[item.program] }}
      </div>
      <div style="width: 120px">
        занятий: {{ item.lessons_count }}
      </div>
      <div style="width: 180px">
        <div v-for="year in item.years" :key="year">
          {{ YearLabel[year] }}
        </div>
      </div>
      <div style="flex: 1">
        <UiCountDown v-if="item.ttl > 0" :seconds="item.ttl" hours>
          ожидание
        </UiCountDown>
        <tempalte v-else>
          <div v-for="es in item.exam_scores" :key="es.id">
            {{ ExamLabel[es.exam] }}: {{ es.score }}
          </div>
        </tempalte>
      </div>
      <template v-if="typeof (item.id) === 'number'">
        <div v-if="clientId" style="width: 200px" class="text-truncate pr-2">
          {{ item.text }}
        </div>
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientReviewDialog?.edit(item.id)"
          />
        </div>
        <div style="width: 30px; flex: initial">
          <span :class="`text-score text-score--${item.rating}`">
            {{ item.rating }}
          </span>
        </div>
      </template>
      <template v-else>
        <div class="table-actionss">
          <v-menu>
            <template #activator="{ props }">
              <v-btn icon="$more" :size="48" v-bind="props" />
            </template>
            <v-list>
              <v-list-item @click="clientReviewDialog?.create(item)">
                создать отзыв
              </v-list-item>
              <v-list-item @click="sendMessage(item)">
                запросить у клиента
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
        <div style="flex: initial; width: 80px">
          <span class="text-error">
            требуется <br />
            создание
          </span>
        </div>
      </template>
    </div>
  </div>
  <ClientReviewDialog
    ref="clientReviewDialog"
    @updated="onUpdated"
    @created="onCreated"
    @deleted="onDeleted"
  />
</template>

<style lang="scss">
.client-review {
  &--waiting {
    background: rgba(var(--v-theme-primary), 0.1);
    .table-actionss {
      display: none !important;
    }
  }
}
</style>
