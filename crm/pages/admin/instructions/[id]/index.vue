<script setup lang="ts">
import { mdiContentCopy } from '@mdi/js'

const route = useRoute()
const instruction = ref<InstructionResource>()

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
            :ripple="false"
            :to="{
              name: 'instructions-id-diff',
              params: {
                id: instruction.id,
              },
            }"
          />
          <v-btn
            variant="plain"
            icon="$edit"
            :size="48"
            :ripple="false"
            :to="{
              name: 'instructions-id-edit',
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
      <Table class="instruction__signs" hoverable>
        <TableRow v-for="t in instruction.teachers" :key="t.id">
          <TableCol>
            <UiAvatar :item="t" :size="38" class="mr-4" />
            <NuxtLink :to="{ name: 'teachers-id', params: { id: t.id } }">
              {{ formatNameInitials(t) }}
            </NuxtLink>
          </TableCol>
          <TableCol style="width: 230px; flex: initial" class="text-gray">
            <template v-if="t.signed_at">
              подписано {{ formatDateTime(t.signed_at) }}
            </template>
          </TableCol>
        </TableRow>
      </Table>
    </div>
    <div class="instruction__panel">
      <Table class="table--padding" hoverable>
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
        <TableRow>
          <NuxtLink
            class="icon-link"
            :to="{
              name: 'instructions-id-new-version',
              params: {
                id: instruction!.id,
              },
            }"
          >
            добавить версию
            <v-icon
              :size="16"
              icon="$next"
            />
          </NuxtLink>
        </TableRow>
      </Table>
    </div>
  </div>
</template>
