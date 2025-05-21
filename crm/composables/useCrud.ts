import type { CrudDialogData } from '~/components/Crud'
import { cloneDeep } from 'lodash-es'

interface CrudOptions {
  afterOpen?: () => void
  afterSave?: () => void
}

export default function<Resource extends object>(
  apiUrl: string,
  modelDefaults: Resource,
  options: CrudOptions = {},
) {
  const { dialog, width } = useDialog('default')
  const item = ref<Resource>(cloneDeep(modelDefaults))
  const saving = ref(false)
  const isEditing = computed<boolean>(() => item.value.id > 0)

  function create(overrideProps: object = {}) {
    item.value = cloneDeep({
      ...modelDefaults,
      ...overrideProps,
    })
    openDialog()
  }

  async function edit(id: number) {
    const { data } = await useHttp<Resource>(`${apiUrl}/${id}`)
    item.value = data.value!
    openDialog()
  }

  function openDialog() {
    dialog.value = true
    options.afterOpen && options.afterOpen()
  }

  function save() {
    item.value.id > 0 ? doUpdate() : doCreate()
  }

  async function doCreate() {
    saving.value = true
    const { error } = await useHttp(apiUrl, {
      method: 'post',
      body: {
        ...item.value,
      },
    })
    if (error.value) {
      if (error.value.statusCode === 422) {
        useGlobalMessage('не все обязательные поля заполнены', 'error')
      }
      return
    }
    dialog.value = false
    saving.value = false
    options.afterSave && options.afterSave()
  }

  function doUpdate() {}

  const dialogData: CrudDialogData = {
    width,
    save,
    isEditing,
    saving,
  }

  return {
    item,
    dialog,
    dialogData,
    expose: {
      create,
      edit,
    },
  }
}
