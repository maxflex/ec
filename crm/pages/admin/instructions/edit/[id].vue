<script setup lang="ts">
const QuillEditor = defineAsyncComponent({
  loader: () =>
    import('@vueup/vue-quill').then(VueQuill => VueQuill.QuillEditor),
})

const saving = ref(false)
const route = useRoute()
const item = ref<InstructionBaseResource>()
// const instructionDiffDialog = ref<InstanceType<typeof InstructionDiffDialog>>()

async function save() {
  saving.value = true
  await useHttp<InstructionListResource>(`instructions/${item.value?.id}`, {
    method: 'put',
    body: item.value,
  })
  setTimeout(() => saving.value = false, 300)
}

async function loadData() {
  const { data } = await useHttp<InstructionResource>(`instructions/${route.params.id}`)
  item.value = data.value as InstructionResource
}

nextTick(loadData)
</script>

<template>
  <div v-if="item" class="instruction-edit">
    <div class="instruction-edit__title">
      <div>
        <v-text-field v-model="item.title" label="Заголовок" />
      </div>
      <div class="text-right">
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          :loading="saving"
          @click="save()"
        />
      </div>
    </div>
    <div>
      <QuillEditor
        v-model:content="item.text"
        theme="snow"
        content-type="html"
        :toolbar="[{ header: 1 }, 'bold', 'italic', 'underline']"
      />
    </div>
  </div>
</template>

<style lang="scss">
.instruction-edit {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 30px;
  &__title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    & > div {
      flex: 1;
    }
  }
}
</style>
