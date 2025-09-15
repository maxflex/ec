<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const emit = defineEmits<{
  updated: [l: TopicListResource]
}>()

const saving = ref(false)
const { dialog, width } = useDialog('default')
const item = ref<TopicListResource>()
const isTopicError = ref(false)
const topicInput = ref()
const topicLength = computed(() => item.value?.topic?.replace(/\s+/g, '').length || 0)

async function edit(lesson: TopicListResource) {
  isTopicError.value = false
  item.value = cloneDeep(lesson)
  dialog.value = true
}

async function save() {
  if (topicLength.value < 50) {
    topicInput.value.focus()
    isTopicError.value = true
    return
  }
  isTopicError.value = false
  saving.value = true
  const { data } = await useHttp(
    `lessons/${item.value?.id}`,
    {
      method: 'put',
      body: item.value,
    },
  )
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
        <div class="input-with-counter">
          <v-textarea
            ref="topicInput"
            v-model="item.topic"
            :disabled="item.is_topic_verified"
            :error-messages="isTopicError ? 'Тема урока должна содержать не менее 50 символов' : ''"
            :hide-details="!isTopicError"
            label="Тема"
            no-resize
            auto-grow
            rows="5"
          />
          <v-fade-transition>
            <div v-if="topicLength" class="input-with-counter__counter">
              {{ topicLength }}
            </div>
          </v-fade-transition>
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
