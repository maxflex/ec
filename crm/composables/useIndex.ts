export default function<T, F>(apiUrl: string) {
  const loading = ref(false)
  const items = ref<T[]>([]) as Ref<T[]>
  const filters = ref<F>()
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
        ...filters.value,
      },
    })
    if (data.value) {
      const { meta, data: newItems } = data.value
      items.value = page === 1 ? newItems : items.value.concat(newItems)
      isLastPage = meta.current_page >= meta.last_page
    }
    loading.value = false
  }

  function onFiltersApply(f: F) {
    filters.value = f
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

  onMounted(() => {
    scrollContainer = document.documentElement.querySelector('main')
    scrollContainer?.addEventListener('scroll', onScroll)
  })

  onUnmounted(() => {
    scrollContainer?.removeEventListener('scroll', onScroll)
  })

  nextTick(loadData)

  return {
    items,
    loading,
    onFiltersApply,
    reloadData,
  }
}
