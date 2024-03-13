<script setup lang="ts">
import { cloneDeep } from "lodash"
import type { Program, Test } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"
import { humanFileSize } from "~/utils/filters"
import type { TestAnswersDialog } from "#build/components"

const { dialog, width } = useDialog()
const item = ref<Test>()
const input = ref()
const fileInput = ref()
const pdf = ref()
const loading = ref(false)
const answersDialog = ref<null | InstanceType<typeof TestAnswersDialog>>()
const programs = Object.keys(PROGRAM).map((value) => ({
  value,
  title: PROGRAM[value as Program],
}))
const emit = defineEmits<{
  (e: "updated"): void
}>()

function open(t: Test) {
  pdf.value = null
  item.value = cloneDeep(t)
  dialog.value = true
}

function create() {
  item.value = {
    id: 0,
    minutes: 30,
    name: "",
    file: "",
    program: null,
    answers: null,
    results: null,
    created_at: null,
    updated_at: null,
  }
  dialog.value = true
  nextTick(() => {
    input.value.reset()
    input.value.focus()
  })
}

async function storeOrUpdate() {
  loading.value = true
  const { data } = item.value?.id
    ? await useHttp(`tests/${item.value.id}`, {
        method: "PUT",
        body: item.value,
      })
    : await useHttp("tests", {
        method: "POST",
        body: item.value,
      })
  item.value = data.value as Test
  await uploadPdf()
  dialog.value = false
  loading.value = false
  emit("updated")
}

async function uploadPdf() {
  if (!pdf.value || !item.value?.id) {
    return
  }
  const formData = new FormData()
  formData.append("pdf", pdf.value)
  await useHttp(`tests/upload-pdf/${item.value.id}`, {
    method: "POST",
    body: formData,
  })
}

function onAnswersSaved(answers: TestAnswers) {
  if (!item.value) {
    return
  }
  item.value.answers = answers
}

function selectFile() {
  fileInput.value.click()
}

function onFileSelected(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files?.length) {
    pdf.value = target.files[0]
  }
}

defineExpose({ open, create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-content" v-if="item">
      <div class="dialog-header">
        <span v-if="item.id > 0"> Редактирование теста </span>
        <span v-else> Добавить тест </span>
        <div>
          <v-btn
            icon="$file"
            :size="48"
            @click="selectFile()"
            color="#fafafa"
          />
          <v-btn
            icon="$save"
            :size="48"
            :loading="loading"
            color="#fafafa"
            @click="storeOrUpdate()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field v-model="item.name" label="Название" ref="input" />
        </div>
        <div>
          <v-select
            :items="programs"
            v-model="item.program"
            label="Программа"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.minutes"
            label="Минут на выполнение"
            type="number"
            hide-spin-buttons
          />
        </div>
        <div>
          <a class="link-icon" @click="() => answersDialog?.open(item?.answers)"
            >редактировать ответы
            <template v-if="item.answers?.length"
              >({{ item.answers.length }})</template
            >
            <v-icon :size="16" icon="$next"></v-icon>
          </a>
        </div>
        <div>
          <input
            style="display: none"
            type="file"
            accept="application/pdf"
            ref="fileInput"
            @change="onFileSelected"
          />
          <v-slide-y-transition>
            <div class="pdf-file" v-if="pdf">
              <img src="/img/pdf.svg" />
              <div>
                <div class="text-gray">{{ pdf.name }}</div>
                <div class="text-gray">
                  {{ humanFileSize(pdf.size) }}
                </div>
              </div>
            </div>
            <div class="pdf-file" v-else-if="item.file">
              <img src="/img/pdf.svg" />
              <div>
                <a :href="item.file" target="_blank">{{
                  item.file.split("/")[5]
                }}</a>
                <div class="text-gray">1.2 Мб</div>
              </div>
            </div>
          </v-slide-y-transition>
        </div>
      </div>
    </div>
  </v-dialog>
  <TestAnswersDialog ref="answersDialog" @saved="onAnswersSaved" />
</template>

<style lang="scss">
.pdf-file {
  display: flex;
  gap: 20px;
  align-items: center;
  margin-top: 30px;
  img {
    width: 50px;
  }
  & > div {
    flex: 1;
    overflow: hidden;
    top: 4px;
    position: relative;
    line-height: 20px;
    & > div {
      width: 100%;
      text-overflow: ellipsis;
      overflow: hidden;
      display: inline-block;
      white-space: nowrap;
    }
  }
}
</style>
