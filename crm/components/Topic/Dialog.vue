<script setup lang="ts">
import { clone } from 'lodash'

const emit = defineEmits<{
  updated: [l: TopicListResource]
}>()

const saving = ref(false)
const { dialog, width } = useDialog('default')
const item = ref<TopicListResource>()

async function edit(lesson: TopicListResource) {
  item.value = clone(lesson)
  dialog.value = true
}

async function save() {
  saving.value = true
  const { data } = await useHttp(`lessons/${item.value?.id}`, {
    method: 'put',
    body: item.value,
  })
  if (data.value) {
    emit('updated', item.value!)
    itemUpdated('topic', item.value!.id)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

defineExpose({ edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Редактирование темы
        <div>
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <div v-if="item" class="dialog-body">
        <div>
          <v-textarea
            v-model="item.topic"
            :disabled="item.is_topic_verified"
            label="Тема"
            no-resize
            auto-grow
            rows="5"
          />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_topic_verified"
            label="Тема подтверждена"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
