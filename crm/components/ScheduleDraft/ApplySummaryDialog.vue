<script lang="ts" setup>
import type { ScheduleDraft, ScheduleDraftContract } from '.'
import { mdiArrowRightThin } from '@mdi/js'
import { VDialogTransition } from 'vuetify/components'
import { apiUrl } from '.'

const loading = ref(false)
const dialog = ref(false)
const item = ref<ScheduleDraftContract>()

function open(scheduleDraft: ScheduleDraft) {
  item.value = scheduleDraft.find(e => e.is_active)
  dialog.value = true
}

async function apply() {
  loading.value = true
  const { error } = await useHttp(`${apiUrl}/apply`, { method: 'POST' })
  if (error.value) {
    useGlobalMessage(`<b>Ошибка применения проекта.</b> ${error.value.data.message}`, 'error')
    loading.value = false
    return
  }
  useGlobalMessage('Проект успешно применён', 'success')
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    max-width="600"
    location="center"
    :content-class="null"
    :transition="{ component: VDialogTransition }"
  >
    <v-card>
      <v-card-text v-if="item">
        <div class="font-weight-bold mb-4">
          <div v-if="item.contract_id">
            Будет создана новая версия к договору №{{ item.contract_id }}
          </div>
        </div>
        <div class="schedule-draft-changes">
          <template v-for="p in item.programs" :key="p.id">
            <div v-if="p.id < 0" class="text-success">
              добавлена программа: {{ ProgramLabel[p.program] }}
            </div>
            <template v-for="g in p.groups" :key="g.id">
              <template v-if="p.id > 0">
                <div v-if="g.draft_status === 'removed'" class="text-error">
                  {{ ProgramLabel[p.program] }}
                  <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                  убран из группы {{ g.id }}
                </div>
                <div v-if="g.draft_status === 'added'" class="text-success">
                  {{ ProgramLabel[p.program] }}
                  <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                  добавлен в группу {{ g.id }}
                </div>
              </template>
              <!-- p.id < 0 -->
              <div v-else-if="g.swamp" class="text-success">
                {{ ProgramLabel[p.program] }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                добавлен в группу {{ g.id }}
              </div>
            </template>
          </template>
        </div>
      </v-card-text>
      <v-card-actions class="pb-4 pt-6">
        <v-spacer />
        <v-btn
          color="primary"
          variant="flat"
          class="px-5"
          :loading="loading"
          @click="apply()"
        >
          применить проект
        </v-btn>
        <v-spacer />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
