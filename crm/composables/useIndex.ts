export default function<T, F extends object = object>(
  apiUrl: string,
  options: {
    defaultFilters?: F
    instantLoad?: boolean
    scrollContainerSelector?: string
  } = {},
) {
  const {
    instantLoad = true,
    defaultFilters = {},
    scrollContainerSelector = 'main',
  } = options

  // данные для компонента UiIndexPage
  const indexPageData = ref<IndexPageData>({
    loading: true,
    noData: false,
  })

  // любая загрузка
  const loading = ref(false)

  const items = ref<T[]>([]) as Ref<T[]>
  let filters: F = defaultFilters as F
  let scrollContainer: HTMLElement | null = null
  let page = 0
  let isLastPage = false

  async function loadData(localFilters = {}) {
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
        ...filters,
        ...localFilters,
      },
    })
    if (data.value) {
      const { meta, data: newItems } = data.value
      if (page === 1) {
        items.value = newItems
        indexPageData.value = {
          loading: false,
          noData: newItems.length === 0,
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

  function onFiltersApply(f: F) {
    filters = f
    if (scrollContainer) {
      scrollContainer.scrollTop = 0
    }
    reloadData()
  }

  function reloadData(localFilters = {}) {
    page = 0
    isLastPage = false
    loadData(localFilters)
  }

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
    loading,
    indexPageData,
    onFiltersApply,
    reloadData,
  }
}
