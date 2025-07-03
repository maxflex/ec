interface UseIndexOptions {
  /**
   * Мгновенно загружать данные
   */
  instantLoad?: boolean

  /**
   * CSS-selector скролл-контейнера
   * false когда без infinite скролла (когда грузим сразу всё)
   */
  scrollContainerSelector?: string | boolean

  /**
   * Нерегулируемые предустановленные фильтры (отправляются всегда)
   */
  staticFilters?: object

  /**
   * Сохранять фильтры в localStorage
   */
  saveFilters?: boolean

  /**
   * Название вкладки для сохранения фильтров в localStorage
   */
  tabName?: string | null

  /**
   * Автоматически перезагружать данные при изменении фильтров
   */
  watchFilters?: boolean

  /**
   * Сначала загрузить доступные учебные годы для селектора
   */
  loadAvailableYears?: boolean
}

export default function<T, E extends object = object>(
  apiUrl: string,
  filters: Ref<object> = ref({}) as Ref<object>,
  options: UseIndexOptions = {},
) {
  const {
    instantLoad = true,
    scrollContainerSelector = 'main',
    staticFilters = {},
    tabName = null,
    saveFilters: isSaveFilters = true,
    watchFilters = true,
    loadAvailableYears = false,
  } = options

  // данные для компонента UiIndexPage
  const indexPageData = ref<IndexPageData>({
    loading: instantLoad,
    noData: false,
  })

  const extra = ref({}) as Ref<E>
  const total = ref<number>()

  // любая загрузка
  const loading = ref(false)
  const availableYears = ref<Year[]>()
  const items = ref<T[]>([]) as Ref<T[]>
  let scrollContainer: HTMLElement | null = null
  let page = 0
  let isLastPage = false
  let abortController: AbortController

  async function loadData() {
    abortController?.abort()

    if (isLastPage) {
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

    const params = transformArrayKeys({
      page,
      ...staticFilters,
      ...filters.value,
    })

    abortController = new AbortController()

    const { data } = await useHttp<ApiResponse<T>>(apiUrl, {
      params,
      signal: abortController.signal,
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
      total.value = meta.total
    }
    loading.value = false
    setScrollContainer()
  }

  async function loadAvailableYearsGo() {
    const params = transformArrayKeys({
      ...staticFilters,
      available_years: 1,
    })
    const { data } = await useHttp<Year[]>(apiUrl, { params })
    availableYears.value = data.value!
    // это установит самый первый год, если есть доступные год
    // + запустит watcher на фильтры и загрузит данные loadData
    if ('year' in filters.value && availableYears.value.length > 0) {
      filters.value.year = availableYears.value[0]
    }
    else {
      indexPageData.value = {
        loading: false,
        noData: true,
      }
    }
  }

  async function reloadData() {
    page = 0
    isLastPage = false
    if (loadAvailableYears && !availableYears.value?.length) {
      loadAvailableYearsGo()
    }
    else {
      await loadData()
    }
  }

  watchFilters && watch(filters, (newVal) => {
    reloadData()
    isSaveFilters && saveFilters(newVal, tabName)
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
    if (scrollContainerSelector === false) {
      return
    }
    scrollContainer = document.documentElement.querySelector(scrollContainerSelector as string)
    scrollContainer?.addEventListener('scroll', onScroll)
  }

  onUnmounted(() => {
    scrollContainer?.removeEventListener('scroll', onScroll)
  })

  if (loadAvailableYears) {
    nextTick(loadAvailableYearsGo)
  }
  else {
    instantLoad && nextTick(loadData)
  }

  return {
    items,
    extra,
    loading,
    total,
    indexPageData,
    availableYears,
    reloadData,
  }
}
