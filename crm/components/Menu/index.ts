// number – показывать цифру, boolean – показывать только кружок
export type MenuCounts = Partial<Record<string, number | boolean>>
export interface MenuCountsUpdatedPayload {
  request?: number
  call?: number
}

export interface MenuItem {
  to: string
  title: string
  icon?: string
  hide?: boolean
  count?: boolean
}

export interface Submenu {
  title: string
  icon: string
  items: MenuItem[]
  hide?: boolean
}

export type Menu = Array<MenuItem | Submenu>

export const menuCounts = ref<MenuCounts>({ })

/**
 * Получить исходный menu-counts
 */
export async function getMenuCounts() {
  const { data } = await useHttp<MenuCounts>(`menu-counts`)
  menuCounts.value = data.value!
}

/**
 * Обновляем только те счетчики, которые пришли в SSE-событии,
 * без дополнительного запроса на backend.
 */
export function onMenuCountsUpdated(payload: MenuCountsUpdatedPayload) {
  for (const key in payload) {
    const value = payload[key as keyof MenuCountsUpdatedPayload]
    if (typeof value === 'number') {
      menuCounts.value = {
        ...menuCounts.value,
        [key]: value,
      }
      console.log(key, value, menuCounts.value)
    }
  }
}
