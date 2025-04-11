<script lang="ts" setup>
import type { TestResource } from '.'
import { clone } from 'rambda'

const { clientId } = defineProps<{ clientId: number }>()
const emit = defineEmits(['saved'])
const { dialog, width } = useDialog('default')
const tests = ref<TestResource[]>([])
const q = ref('')
const input = ref()
const saving = ref(false)

interface TestSelectorOptions {
  year: Year
  ids: number[]
}

const modelDefaults: TestSelectorOptions = {
  year: currentAcademicYear(),
  ids: [],
}

const model = ref<TestSelectorOptions>(modelDefaults)

function open() {
  dialog.value = true
  model.value = clone({ ...modelDefaults })
  loadData()
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<TestResource>>(`tests`)
  tests.value = data.value!.data
}

function searchFn(text: string): boolean {
  const words = q.value.split(' ').map(e => e.trim().toLocaleLowerCase())
  const name = text.toLocaleLowerCase()
  for (const word of words) {
    if (!name.includes(word)) {
      return false
    }
  }
  return true
}

const filtered = computed<TestResource[]>(() => tests.value.filter(t => searchFn(t.name)))

async function save() {
  if (model.value.ids.length === 0) {
    return
  }

  saving.value = true

  await useHttp(
    `client-tests`,
    {
      method: 'post',
      body: {
        ...model.value,
        client_id: clientId,
      },
    },
  )

  emit('saved')

  dialog.value = false
  saving.value = false
}

function onSelectorMenu(isOpen: boolean) {
  q.value = ''
  if (isOpen) {
    setTimeout(() => {
      input.value.focus()
    }, 150)
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-header">
      <span>
        Добавить тесты
      </span>
      <v-btn
        icon="$save"
        :size="48"
        variant="text"
        @click="save()"
      />
    </div>
    <div class="dialog-body">
      <div>
        <UiYearSelector
          v-model="model.year"
        />
      </div>
      <div>
        <v-select
          v-model="model.ids"
          :items="filtered"
          item-title="name"
          item-value="id"
          label="Тесты"
          multiple
          :menu-props="{
            offset: -62,
          }"
          @update:menu="onSelectorMenu"
        >
          <template
            v-if="model.ids.length > 1"
            #selection="{ index }"
          >
            <template v-if="index === 0">
              выбрано: {{ model.ids.length }}
            </template>
          </template>
          <template #prepend-item>
            <div class="test-selector__search">
              <v-text-field ref="input" v-model="q" placeholder="Поиск..." variant="underlined" density="default"></v-text-field>
            </div>
          </template>
          <template #item="{ props, item }">
            <v-list-item v-bind="props">
              <template #prepend />
              <template #title>
                <div class="test-selector">
                  <div class="test-selector__checkbox">
                    <v-icon v-if="model.ids.includes(item.value)" icon="$checkboxOn" color="secondary"></v-icon>
                    <v-icon v-else icon="$checkboxOff" style="opacity: 0.6;"></v-icon>
                  </div>
                  <div>
                    <div class="test-selector__title">
                      {{ item.raw.name }}
                    </div>
                    <div class="test-selector__description">
                      {{ item.raw.description }}
                    </div>
                  </div>
                </div>
              </template>
            </v-list-item>
          </template>
          <template #no-data>
            <v-list-item>
              <template #prepend />
              <template #title>
                <span class="text-gray">
                  ничего не найдено
                </span>
              </template>
            </v-list-item>
          </template>
        </v-select>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.test-selector {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  padding: 8px 0;
  &__checkbox {
    position: relative;
    top: -1px;
  }
  &__description {
    color: rgb(var(--v-theme-gray));
  }
  &__search {
    padding: 0 18px 30px;
    input {
      padding-bottom: 8px !important;
    }
  }
  // & > div:last-child {
  //   line-height: 20px;
  // }
}
</style>
