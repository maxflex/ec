export function usePaginator(): Paginator {
  return reactive({
    page: 0,
    loading: false,
    isLastPage: false,
  })
}
