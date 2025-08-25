export function useTabs<
  T extends Record<string, string>,
>(tabs: T) {
  type Tab = keyof T
  type TabCounts = Partial<Record<Tab, number>>

  const selectedTab = ref<Tab>(Object.keys(tabs)[0] as Tab) // первый ключ по умолчанию
  const tabCounts = ref<TabCounts>({})
  const tabCountsExtra = ref<TabCounts>({})

  return {
    tabs,
    selectedTab,
    tabCounts,
    tabCountsExtra,
  }
}
