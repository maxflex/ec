import { cloneDeep } from 'lodash-es'

export interface CrudDialogData {
  width: number
  saving: Ref<boolean>
  deleting: Ref<boolean>
  isEditing: ComputedRef<boolean>
  save: () => void
  destroy: () => void
}

interface CrudDialogOptions {
  afterOpen?: () => void
}

interface HasId {
  id: number
}

export default function<Resource extends HasId, ListResource extends HasId>(
  apiUrl: string,
  modelDefaults: Resource,
  items: Ref<ListResource[]>,
  options: CrudDialogOptions = {},
) {
  const { dialog, width } = useDialog('default')
  const item = ref<Resource>(cloneDeep(modelDefaults)) as Ref<Resource>
  const saving = ref(false)
  const deleting = ref(false)
  const isEditing = computed<boolean>(() => item.value.id > 0)

  function create(overrideProps: object = {}) {
    item.value = cloneDeep({
      ...modelDefaults,
      ...overrideProps,
    })
    openDialog()
  }

  async function edit(id: number) {
    const { data } = await useHttp(`${apiUrl}/${id}`)
    item.value = data.value! as Resource
    openDialog()
  }

  function openDialog() {
    dialog.value = true
    options.afterOpen && options.afterOpen()
  }

  function save() {
    isEditing.value
      ? _doUpdate()
      : _doCreate()
  }

  async function _doCreate() {
    const newItem = await _sendRequest()
    items.value.unshift(newItem)
    itemUpdated(apiUrl, newItem.id)
  }

  async function _doUpdate() {
    const newItem = await _sendRequest()
    findIndex(newItem).then((index) => {
      items.value.splice(index, 1, newItem)
      itemUpdated(apiUrl, newItem.id)
    })
  }

  async function _sendRequest(): Promise<ListResource> {
    let url: string
    let method: 'PUT' | 'POST'

    if (isEditing.value) {
      url = `${apiUrl}/${item.value.id}`
      method = 'PUT'
    }
    else {
      url = apiUrl
      method = 'POST'
    }

    saving.value = true
    const { data, error } = await useHttp(
      url,
      {
        method,
        body: {
          ...item.value,
        },
      },
    )
    if (error.value) {
      if (error.value.statusCode === 422) {
        useGlobalMessage('не все обязательные поля заполнены', 'error')
      }
    }

    dialog.value = false
    saving.value = false

    return data.value as ListResource
  }

  function findIndex({ id }: HasId): Promise<number> {
    const index = items.value.findIndex(e => e.id === id)
    if (index === -1) {
      return Promise.reject(new Error('index not found'))
    }
    return Promise.resolve(index)
  }

  async function destroy() {
    if (!confirm('Вы уверены, что хотите удалить запись?')) {
      return
    }
    deleting.value = true
    const { error } = await useHttp(
      `${apiUrl}/${item.value.id}`,
      {
        method: 'DELETE',
      },
    )
    if (error.value) {
      deleting.value = false
      useGlobalMessage(`Невозможно удалить запись. ${error.value.data?.message}`, 'error')
      return
    }
    useGlobalMessage(`Запись удалена`, 'success')
    dialog.value = false
    deleting.value = false
    findIndex(item.value).then(index => items.value.splice(index, 1))
  }

  const dialogData: CrudDialogData = {
    width,
    isEditing,
    saving,
    deleting,
    save,
    destroy,
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
