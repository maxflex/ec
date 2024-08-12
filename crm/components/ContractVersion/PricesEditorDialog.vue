<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  saved: [p: ContractVersionProgramResource]
}>()
const { dialog, width, transition } = useDialog('default')
const priceInput = ref()
const item = ref<ContractVersionProgramResource>()
function open(cvp: ContractVersionProgramResource) {
  item.value = clone(cvp)
  dialog.value = true
}

function addPrices() {
  if (!item.value) {
    return
  }
  item.value.prices.push(['', ''])
  nextTick(() => {
    priceInput.value[item.value!.prices.length - 1].focus()
  })
}

/**
 * начиная с X занятия
 */
function getFromLessonLabel(index: number): number {
  if (!item.value) {
    return 0
  }
  return item.value.prices
    .filter((x, i) => i < index)
    .reduce((carry, x) => carry + Number.parseInt(x[0]), 0)
}

function save() {
  if (!item.value) {
    return
  }
  item.value.prices = item.value.prices
    .filter(x => x[0] !== '' && x[1] !== '')
    .map(x => [Number.parseInt(x[0]), Number.parseInt(x[1])])
  emit('saved', item.value!)
  dialog.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div v-if="item" class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          Цены {{ ProgramLabel[item.program] }}
        </div>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body pt-0">
        <table class="dialog-table cvp-prices-editor">
          <thead v-if="item.prices.length">
            <tr>
              <th width="220" />
              <th>
                уроков
              </th>
              <th>
                цена, руб.
              </th>
              <th width="48" />
            </tr>
          </thead>
          <tbody>
            <tr v-for="(p, index) in item.prices" :key="index">
              <td class="cursor-default">
                <template v-if="p[0]">
                  <template v-if="index === 0">
                    с {{ index + 1 }}
                    по {{ p[0] }} занятие
                  </template>
                  <template v-else>
                    с {{ getFromLessonLabel(index) + 1 }}
                    по {{ getFromLessonLabel(index) + Number.parseInt(p[0]) }} занятие
                  </template>
                </template>
              </td>
              <td>
                <v-text-field
                  ref="priceInput"
                  v-model="p[0]"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </td>
              <td>
                <v-text-field
                  v-model="p[1]"
                  type="number"
                  hide-spin-buttons
                  density="compact"
                />
              </td>
              <td class="text-right">
                <v-btn
                  icon="$close"
                  variant="plain"
                  color="red"
                  :size="48"
                  :ripple="false"
                  @click="item.prices.splice(index, 1)"
                />
              </td>
            </tr>
            <tr>
              <td colspan="3">
                <a
                  class="link-icon"
                  @click="addPrices()"
                >
                  добавить
                  <v-icon
                    :size="16"
                    icon="$next"
                  />
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </v-dialog>
</template>
