interface AvailableYearsFilter {
  year: Year | null
}

export default function () {
  const filters = ref<AvailableYearsFilter>({
    year: null,
  })

  return filters
}
