// number – показывать цифру, boolean – показывать только кружок
export type MenuCounts = Partial<Record<string, number | boolean>>

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

export async function updateMenuCounts() {
  const { data } = await useHttp<MenuCounts>(`menu-counts`)
  menuCounts.value = data.value!
}
