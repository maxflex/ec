export const usePaginator = (): Paginator =>
  reactive({
    page: 0,
    loading: false,
    isLastPage: false,
  })
