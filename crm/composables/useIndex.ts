export default function<T, F extends object = object, E extends object = object>(
  apiUrl: string,
  filters: Ref<F> = ref({}) as Ref<F>,
  options: {
    instantLoad?: boolean
    scrollContainerSelector?: string
    staticFilters?: object
    tabName?: string | null
  } = {},
) {
  const {
    instantLoad = true,
    scrollContainerSelector = 'main',
    staticFilters = {},
    tabName = null,
  } = options

  // данные для компонента UiIndexPage
  const indexPageData = ref<IndexPageData>({
    loading: true,
    noData: false,
  })

  const extra = ref({}) as Ref<E>

  // любая загрузка
  const loading = ref(false)

  const items = ref<T[]>([]) as Ref<T[]>
  let scrollContainer: HTMLElement | null = null
  let page = 0
  let isLastPage = false

  async function loadData() {
    if (loading.value || isLastPage) {
      return
    }
    page++
    loading.value = true
    if (page === 1) {
      indexPageData.value = {
        loading: true,
        noData: false,
      }
    }
    const { data } = await useHttp<ApiResponse<T[]>>(apiUrl, {
      params: {
        page,
        ...staticFilters,
        ...filters.value,
      },
    })
    if (data.value) {
      const { extra: e, meta, data: newItems } = data.value
      if (e) {
        extra.value = e as E
      }
      if (page === 1) {
        items.value = newItems
        indexPageData.value = {
          loading: false,
          noData: newItems.length === 0,
        }
        if (scrollContainer) {
          scrollContainer.scrollTop = 0
        }
      }
      else {
        items.value = items.value.concat(newItems)
      }
      isLastPage = meta.current_page >= meta.last_page
    }
    loading.value = false
    setScrollContainer()
  }

  function reloadData() {
    page = 0
    isLastPage = false
    loadData()
  }

  watch(filters, (newVal) => {
    reloadData()
    saveFilters(newVal, tabName)
  }, { deep: true })

  function onScroll() {
    if (!scrollContainer || loading.value) {
      return
    }
    const { scrollTop, scrollHeight, clientHeight } = scrollContainer
    const scrollPosition = scrollTop + clientHeight
    const scrollThreshold = scrollHeight * 0.9

    if (scrollPosition >= scrollThreshold) {
      loadData()
    }
  }

  function setScrollContainer() {
    if (scrollContainer !== null) {
      return
    }
    scrollContainer = document.documentElement.querySelector(scrollContainerSelector)
    scrollContainer?.addEventListener('scroll', onScroll)
  }

  onUnmounted(() => {
    scrollContainer?.removeEventListener('scroll', onScroll)
  })

  instantLoad && nextTick(loadData)

  return {
    items,
    extra,
    loading,
    indexPageData,
    reloadData,
  }
}
