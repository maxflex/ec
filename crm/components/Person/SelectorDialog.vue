<script setup lang="ts">
const { title, filters } = withDefaults(defineProps<{
  title?: string
  filters?: any
}>(), {
  title: 'Выберите участников',
  filters: {},
})
const emit = defineEmits<{
  selected: [items: PersonListResource[]]
}>()
const { dialog, width, transition } = useDialog('default')
const items = ref<PersonListResource[]>([])
const selected = ref<PersonListResource[]>([])
const loading = ref(false)
const scrollContainer = ref<HTMLElement>()
let page = 0
let isLastPage = false

function open() {
  dialog.value = true
  selected.value = []
  loadData()
}

function select(p: PersonListResource) {
  const index = selected.value.findIndex(e => e === p)
  index !== -1 ? selected.value.splice(index, 1) : selected.value.push(p)
}

async function loadData() {
  if (loading.value || isLastPage) {
    return
  }
  loading.value = true
  page++
  const { data } = await useHttp<ApiResponse<PersonListResource[]>>(`persons`, {
    params: {
      ...filters,
      page,
    },
  })
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value = page === 1 ? newItems : items.value.concat(newItems)
    isLastPage = meta.current_page >= meta.last_page
  }
  loading.value = false
}

function onScroll() {
  if (!scrollContainer.value || loading.value) {
    return
  }
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer.value
  const scrollPosition = scrollTop + clientHeight
  const scrollThreshold = scrollHeight * 0.9

  if (scrollPosition >= scrollThreshold) {
    loadData()
  }
}

function save() {
  dialog.value = false
  emit('selected', selected.value)
}

watch(dialog, (isOpen) => {
  nextTick(() => isOpen
    ? scrollContainer.value?.addEventListener('scroll', onScroll)
    : scrollContainer.value?.removeEventListener('scroll', onScroll),
  )
})

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          {{ title }}
          <span
            v-if="selected.length"
            class="ml-1 text-gray"
          >
            {{ selected.length }}
          </span>
        </span>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <div ref="scrollContainer" class="dialog-body py-0">
        <UiLoader3 :loading="loading" />
        <div class="table table--hover">
          <div
            v-for="p in items"
            :key="`${p.entity_type}${p.id}`"
            class="cursor-pointer" @click="select(p)"
          >
            <div>
              <UiAvatar :item="p" :size="48" />
            </div>
            <div style="flex: 1">
              {{ formatName(p) }}
              <div class="text-gray" style="font-size: 14px">
                {{ EntityTypeLabel[p.entity_type] }}
              </div>
            </div>
            <div style="width: 23px; flex: initial">
              <v-icon
                v-if="selected.includes(p)"
                color="secondary"
                icon="$checkboxOn"
              />
              <v-icon
                v-else
                icon="$checkboxOff"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>
