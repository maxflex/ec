<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits(['updated'])

const saving = ref(false)
const { dialog, width } = useDialog('default')
const item = ref<ScholarshipScoreResource>()

function open(e: ScholarshipScoreResource) {
  item.value = clone(e)
  dialog.value = true
}

async function save() {
  saving.value = true
  if (item.value?.id) {
    await useHttp(`scholarship-scores/${item.value!.id}`, {
      method: 'put',
      body: item.value,
    })
  }
  else {
    await useHttp(`scholarship-scores`, {
      method: 'post',
      body: item.value,
    })
  }
  dialog.value = false
  saving.value = false
  emit('updated')
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" class="scholarship-score-dialog">
    <div class="dialog-wrapper">
      <div v-if="item" class="dialog-body">
        <div>
          <div>Ученик:</div>
          <div>
            <UiPerson :item="item.client" />
          </div>
        </div>
        <div>
          <div>Программа:</div>
          <div>
            {{ ProgramLabel[item.program] }}
          </div>
        </div>
        <div>
          <div>Месяц:</div>
          <div>
            {{ MonthLabel[item.month] }},
            {{ plural(item.lessons_count, ["занятие", "занятия", "занятий"]) }}
          </div>
        </div>
        <div>
          <div>Оценка:</div>
          <div>
            <p>
              Оценка должна отражать общее впечатление о работе ученика за весь месяц. Учитывайте успеваемость, выполнение домашних заданий, активность на уроках, поведение, отношение к учебному процессу и т.д.
            </p>
            <p>
              Старайтесь использовать весь диапазон шкалы. 10 должна выставляться только в исключительных случаях, когда ученик действительно достиг высоких результатов по всем критериям.
            </p>
            <p>
              Оценка используется администрацией внутри ЕГЭ-Центра для стипендиальных выплат. Оценка конфиденциальна и не будет доступна ученикам и их родителям.
            </p>
          </div>
          <div class="scholarship-score-dialog__scores">
            <ScholarshipScoreCircle
              v-for="i in 10"
              :key="i"
              :score="i"
              :class="{
                'scholarship-score--selected': i === item.score,
              }"
              @click="item.score = i"
            />
          </div>
          <v-btn
            block
            color="primary"
            :disabled="!item.score"
            :loading="saving"
            size="x-large"
            @click="save()"
          >
            {{ item.id ? "изменить" : "сохранить" }} оценку
          </v-btn>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.scholarship-score-dialog {
  .dialog-body {
    & > div {
      & > div {
        &:first-child {
          font-weight: bold;
        }
      }
    }
  }
  p {
    margin-bottom: 10px;
  }
  .scholarship-score {
    opacity: 0.3;
    cursor: pointer !important;
    &--selected,
    &:hover {
      opacity: 1 !important;
    }
    &--selected {
      transform: scale(1.2) !important;
      border-color: rgba(black, 0.1);
    }
  }
  &__scores {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 30px !important;
    margin-bottom: 60px !important;
  }
}
</style>
