<script setup lang="ts">
import { mdiContentCopy } from '@mdi/js'
import type { InstructionDialog } from '#build/components'

const route = useRoute()
const instruction = ref<InstructionResource>()
const instructionDialog = ref<InstanceType<typeof InstructionDialog>>()

async function loadData() {
  const { data } = await useHttp<InstructionResource>(`instructions/${route.params.id}`)
  instruction.value = data.value as InstructionResource
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
            :disabled="instruction.versions[0].id === instruction.id"
            :to="{
              name: 'instructions-diff-id',
              params: {
                id: instruction.id,
              },
            }"
          />
          <!--     -->
          <v-btn
            variant="plain"
            icon="$edit"
            :size="48"
            :to="{
              name: 'instructions-edit-id',
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
      <div class="table table--hover instruction__signs">
        <div v-for="t in instruction.teachers" :key="t.id">
          <div style="flex: 1">
            <UiAvatar :item="t" :size="38" class="mr-4" />
            <NuxtLink :to="{ name: 'teachers-id', params: { id: t.id } }">
              {{ formatNameInitials(t) }}
            </NuxtLink>
          </div>
          <div style="width: 230px; flex: initial" class="text-gray">
            <template v-if="t.signed_at">
              подписано {{ formatDateTime(t.signed_at) }}
            </template>
          </div>
        </div>
      </div>
    </div>
    <div class="instruction__panel">
      <div class="table table--padding table--hover">
        <RouterLink
          v-for="(v, index) in instruction.versions"
          :key="v.id"
          :class="{
            selected: v.id === instruction.id,
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
          <div class="text-gray">
            <template v-if="v.signs_count">
              {{ v.signs_count }} подписали
            </template>
            <template v-else>
              нет подписей
            </template>
          </div>
        </RouterLink>
        <div>
          <a
            class="link-icon"
            @click="instructionDialog?.addVersion(instruction!)"
          >
            добавить версию
            <v-icon
              :size="16"
              icon="$next"
            />
          </a>
        </div>
      </div>
    </div>
  </div>
  <InstructionDialog ref="instructionDialog" @updated="i => (instruction = i)" />
</template>
