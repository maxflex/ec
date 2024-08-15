<script setup lang="ts">
import { mdiCheckAll, mdiContentCopy } from '@mdi/js'

const loading = ref(false)
const route = useRoute()
const instruction = ref<InstructionTeacherResource>()

async function loadData() {
  const { data } = await useHttp<InstructionTeacherResource>(`instructions/${route.params.id}`)
  instruction.value = data.value as InstructionTeacherResource
}

async function sign() {
  loading.value = true
  const { data } = await useHttp<InstructionTeacherResource>(`instructions/sign/${instruction.value?.id}`, {
    method: 'post',
  })
  instruction.value = data.value as InstructionTeacherResource
}

nextTick(loadData)
</script>

<template>
  <div v-if="instruction" class="instruction">
    <div class="instruction__content">
      <h1>
        {{ instruction.title }}
        <div>
          <v-btn
            variant="plain"
            :icon="mdiContentCopy"
            :size="48"
            :disabled="instruction.is_first_version"
            :ripple="false"
            :to="{
              name: 'instructions-id-diff',
              params: {
                id: instruction.id,
              },
            }"
          />
        </div>
      </h1>
      <div class="ql-snow">
        <div class="ql-editor pa-0">
          <div v-html="instruction.text" />
        </div>
      </div>
      <div class="instruction__sign">
        <v-btn v-if="instruction.signed_at" color="success" size="x-large" :width="400" disabled @click="sign()">
          <v-icon :icon="mdiCheckAll" :size="20" class="mr-2" />
          Подписано {{ formatDateTime(instruction.signed_at) }}
        </v-btn>
        <v-btn v-else color="primary" size="x-large" :width="400" @click="sign()">
          Подписать
        </v-btn>
      </div>
    </div>
    <div class="instruction__panel">
      <div class="table table--padding table--hover">
        <RouterLink
          v-for="(v, index) in instruction.versions"
          :key="v.id"
          :class="{
            'selected': v.id === instruction.id,
            'no-pointer-events': !v.signed_at && !v.is_last_version,
          }"
          :to="{
            name: 'instructions-id',
            params: { id: v.id },
          }"
          class="cursor-pointer table-item"
        >
          версия {{ index + 1 }}
          <div class="text-gray">
            создана {{ formatDateTime(v.created_at) }}
          </div>
          <div v-if="v.signed_at" class="text-success">
            <v-icon :icon="mdiCheckAll" :size="16" class="mr-1" />
            подписано
          </div>
          <div v-else-if="!v.is_last_version" class="text-gray">
            архив
          </div>
          <div v-else class="text-error">
            не подписано
          </div>
        </RouterLink>
      </div>
    </div>
  </div>
</template>
