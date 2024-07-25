<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  deleted: [r: ClientReviewResource]
  updated: [r: RealClientReviewItem]
  created: [r: RealClientReviewItem, fakeItemId: string]
}>()
const modelDefaults: ClientReviewResource = {
  id: newId(),
  text: '',
  rating: 0,
}
const { dialog, width } = useDialog('default')
const itemId = ref<number>()
let fakeItemId: string = ''
const item = ref<ClientReviewResource>(modelDefaults)
const loading = ref(false)
const deleting = ref(false)

async function edit(clientReviewId: number) {
  itemId.value = clientReviewId
  dialog.value = true
  loading.value = true
  const { data } = await useHttp<ClientReviewResource>(
    `client-reviews/${clientReviewId}`,
  )
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function create(r: FakeClientReviewItem) {
  itemId.value = undefined
  fakeItemId = r.id
  item.value = clone({
    ...modelDefaults,
    teacher: r.teacher,
    client: r.client,
    program: r.program,
  })
  dialog.value = true
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отзыв?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`reports/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => (deleting.value = false), 300)
  }
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<RealClientReviewItem>(`reports/${itemId.value}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<RealClientReviewItem>('reports', {
      method: 'post',
      body: {
        ...item.value,
        client_id: item.value.client?.id,
      },
    })
    if (data.value) {
      emit('created', data.value, fakeItemId)
    }
  }
}

defineExpose({ edit, create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <tempate v-if="itemId">
          Редактирование отзыва
        </tempate>
        <template v-else>
          Новый отзыв
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div v-else class="dialog-body">
        <div class="text-center pb-2">
          <v-rating
            v-model="item.rating"
            hover
            active-color="orange"
            color="orange"
          />
        </div>
        <div class="double-input">
          <div v-if="item.teacher">
            <v-text-field
              :model-value="formatNameInitials(item.teacher)"
              label="Преподаватель"
              disabled
            />
          </div>
          <div v-if="item.client">
            <v-text-field
              :model-value="formatName(item.client)"
              label="Клиент"
              disabled
            />
          </div>
        </div>
        <div>
          <UiClearableSelect
            v-model="item.program"
            :items="selectItems(ProgramLabel)"
            label="Программа"
            disabled
          />
        </div>
        <div>
          <v-textarea
            v-model="item.text"
            rows="3"
            no-resize
            auto-grow
            label="Текст отзыва"
          />
        </div>
        <div
          v-if="itemId"
          class="dialog-bottom"
        >
          <span v-if="item.created_at && item.user">
            отзыв создан
            {{ formatName(item.user) }}
            {{ formatDateTime(item.created_at) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="deleting"
            @click="destroy()"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
