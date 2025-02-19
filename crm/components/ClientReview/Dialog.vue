<script setup lang="ts">
import { clone } from 'rambda'
import { apiUrl, type ClientReviewListResource, type ClientReviewResource, modelDefaults } from '.'

const emit = defineEmits<{
  deleted: [id: number]
  updated: [cr: ClientReviewListResource]
  created: [cr: ClientReviewListResource, fakeItemId: string]
}>()
const { isTeacher } = useAuthStore()
const { dialog, width } = useDialog('default')
const itemId = ref<number>()
let fakeItemId: string = ''
const item = ref<ClientReviewResource>(modelDefaults)
const loading = ref(false)
// созданные отзывы препод может только просматривать
const isEditable = computed(() => isTeacher ? itemId.value === undefined : true)

async function edit(clientReviewId: number) {
  itemId.value = clientReviewId
  dialog.value = true
  loading.value = true
  const { data } = await useHttp<ClientReviewResource>(
    `${apiUrl}/${clientReviewId}`,
  )
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function create(cr: ClientReviewListResource) {
  itemId.value = undefined
  fakeItemId = cr.id as string
  item.value = clone({
    ...modelDefaults,
    teacher: cr.teacher,
    client: cr.client,
    program: cr.program,
  })
  dialog.value = true
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<ClientReviewListResource>(
      `${apiUrl}/${itemId.value}`,
      {
        method: 'put',
        body: item.value,
      },
    )
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<ClientReviewListResource>(
      `${apiUrl}`,
      {
        method: 'post',
        body: {
          ...item.value,
          client_id: item.value.client?.id,
          teacher_id: item.value.teacher?.id,
        },
      },
    )
    if (data.value) {
      emit('created', data.value, fakeItemId)
    }
  }
}

function onDeleted() {
  emit('deleted', item.value.id)
  dialog.value = false
}

defineExpose({ edit, create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="itemId">
          <template v-if="isEditable">
            Редактирование отзыва
            <div class="dialog-subheader">
              <template v-if="item.created_at && item.user">
                {{ formatName(item.user) }}
                {{ formatDateTime(item.created_at) }}
              </template>
            </div>
          </template>
          <template v-else>
            Просмотр отзыва
          </template>
        </div>
        <template v-else>
          Новый отзыв
        </template>
        <div v-if="isEditable">
          <DialogDeleteBtn
            :id="itemId"
            :api-url="apiUrl"
            confirm-text="Вы уверены, что хотите удалить отзыв?"
            @deleted="onDeleted()"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div class="text-center pb-2">
          <v-rating
            v-model="item.rating"
            hover
            active-color="orange"
            color="orange"
            :readonly="!isEditable"
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
            :disabled="!isEditable"
            label="Текст отзыва"
          />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_marked"
            label="Проведено"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
