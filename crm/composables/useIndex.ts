export default function<T, F extends object | undefined = undefined>(
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
  const loading = ref(false)
  const items = ref<T[]>([]) as Ref<T[]>
  let filters: F = defaultFilters as F
  let scrollContainer: HTMLElement | null = null
  let page = 0
  let isLastPage = false

  async function loadData() {
    if (loading.value || isLastPage) {
      return
    }
    page++
    loading.value = true
    const { data } = await useHttp<ApiResponse<T[]>>(apiUrl, {
      params: {
        page,
        ...filters,
      },
    })
    if (data.value) {
      const { meta, data: newItems } = data.value
      items.value = page === 1 ? newItems : items.value.concat(newItems)
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

  function reloadData() {
    page = 0
    isLastPage = false
    loadData()
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
    onFiltersApply,
    reloadData,
  }
}
