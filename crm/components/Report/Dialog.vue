<script setup lang="ts">
const { dialog, width } = useDialog('medium')
const item = ref<ReportResource>()
const deleting = ref(false)
const saving = ref(false)
const router = useRouter()
const { isTeacher } = useAuthStore()

const availableTeacherStatuses: ReportStatus[] = [
  'draft',
  'toCheck',
  'empty',
]

const availableAdminStatuses: ReportStatus[] = [
  'refused',
  'published',
  'empty',
]

const availableStatuses = isTeacher ? availableTeacherStatuses : availableAdminStatuses

const isDisabled = computed(() => {
// Если статус = черновик или на проверку, или пустой отчет,
// то препод может редактировать все. Если остальные типы, то отчет нельзя редактировать
  if (isTeacher) {
    return !availableTeacherStatuses.includes(item.value!.status) && item.value!.status !== 'refused'
  }
  // админ не может редактировать статус "черновик"
  return item.value!.status === 'draft'
})

function open(report: ReportResource) {
  item.value = report
  dialog.value = true
}

async function save() {
  saving.value = true
  if (item.value!.id > 0) {
    await useHttp<RealReport>(
      `reports/${item.value!.id}`,
      {
        method: 'put',
        body: { ...item.value },
      },
    )
    saving.value = false
    dialog.value = false
  }
  else {
    const { data } = await useHttp<RealReport>(
      `reports`,
      {
        method: 'post',
        body: {
          ...item.value,
          client_id: item.value!.client?.id,
        },
      },
    )
    await router.push({ name: 'reports-id-edit', params: { id: data.value?.id } })
  }
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отчёт?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`reports/${item.value!.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    await router.push({ name: 'reports' })
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="item" class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="item.id === -1">
          Новый отчёт
        </div>
        <div v-else>
          Редактирование отчета
          <div class="dialog-subheader">
            {{ formatDateTime(item.created_at!) }}
          </div>
        </div>
        <div>
          <v-btn
            v-if="item.id > 0 && !isDisabled"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            :loading="saving"
            :disabled="isDisabled"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div class="double-input">
          <v-select
            v-model="item.status"
            label="Статус"
            :items="availableStatuses.map(value => ({
              value,
              title: ReportStatusLabel[value],
            }))"
            :disabled="isDisabled"
          >
            <template v-if="!(item.status in availableStatuses)" #selection>
              {{ ReportStatusLabel[item.status] }}
            </template>
          </v-select>
          <v-select
            v-model="item.grade"
            label="Оценка"
            :items="selectItems(LessonScoreLabel)"
            :disabled="isDisabled"
          >
            <template #selection="{ item: { raw: { value } } }">
              <span :class="`text-score text-score--${value}`" style="position: absolute;">
                {{ value }}
              </span>
            </template>
            <template #item="{ props, item: { raw: { value } } }">
              <v-list-item v-bind="props">
                <template #title>
                  <span :class="`text-score text-score--${value}`" class="mr-2">
                    {{ value }}
                  </span>
                </template>
                <template #prepend>
                  <v-spacer />
                </template>
              </v-list-item>
            </template>
          </v-select>
          <v-text-field
            v-model="item.price"
            :disabled="isDisabled || isTeacher"
            label="Цена"
            type="number"
            suffix="руб."
            hide-spin-buttons
          />
        </div>
        <div>
          <v-textarea
            v-model="item.homework_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Выполнение домашнего задания"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.cognitive_ability_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Способность усваивать новый материал"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.knowledge_level_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Текущий уровень знаний"
          />
        </div>
        <div>
          <v-textarea
            v-model="item.recommendation_comment"
            :disabled="isDisabled"
            rows="3"
            no-resize
            auto-grow
            label="Рекомендации родителям"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
