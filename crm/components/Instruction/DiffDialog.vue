<script setup lang="ts">
import { mdiArrowExpandVertical } from '@mdi/js'

const { dialog, width } = useDialog('x-large')
const item = ref<InstructionDiffResource>()

const expanded = ref(false)

async function open(i: InstructionResource) {
  item.value = undefined
  dialog.value = true
  const { data } = await useHttp<InstructionDiffResource>(`instructions/diff/${i.id}`)
  if (data.value) {
    item.value = data.value
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Сравнение версий
        <div>
          <v-btn
            :icon="mdiArrowExpandVertical"
            :size="48"
            :color="expanded ? 'primary' : 'bg'"
            @click="expanded = !expanded"
          />
          <v-btn
            icon="$close"
            :size="48"
            color="#fafafa"
            @click="dialog = false"
          />
        </div>
      </div>
      <UiLoaderr v-if="item === undefined" />
      <div v-else class="dialog-body pt-0" style="gap: 0">
        <div class="diff-header">
          <div>
            <b class="text-black">
              {{ item.prev.title }}
            </b>
            <br>
            версия от {{ formatDateTime(item.prev.created_at) }}
          </div>
          <div>
            <b class="text-black">
              {{ item.current.title }}
            </b>
            <br>
            версия от {{ formatDateTime(item.current.created_at) }}
          </div>
        </div>
        <div v-if="item.diff" v-html="expanded ? item.diff_all : item.diff" />
        <div v-else class="no-diff">
          <div>без изменений</div>
          <div>без изменений</div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>
