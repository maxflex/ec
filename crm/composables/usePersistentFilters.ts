interface VersionedFiltersOptions {
  tabName?: string | null
  routeName?: string | null
}

interface VersionedFiltersEntry {
  schema: string
  updatedAt: number
  data: object
}

type VersionedFiltersStore = Record<string, VersionedFiltersEntry>

const STORAGE_KEY = 'filters'
const MAX_ENTRIES = 120
const TTL_MS = 1000 * 60 * 60 * 24 * 180 // 180 дней

const runtimeSchemaCache = new Map<string, string>()

function isObjectRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}

function isVersionedEntry(value: unknown): value is VersionedFiltersEntry {
  if (!isObjectRecord(value)) {
    return false
  }

  return typeof value.schema === 'string'
    && typeof value.updatedAt === 'number'
    && isObjectRecord(value.data)
}

function toSchemaNode(value: unknown): unknown {
  if (Array.isArray(value)) {
    return 'array'
  }

  if (value === null) {
    return 'null'
  }

  if (isObjectRecord(value)) {
    return Object
      .keys(value)
      .sort()
      .reduce<Record<string, unknown>>((acc, key) => {
        acc[key] = toSchemaNode(value[key])
        return acc
      }, {})
  }

  return typeof value
}

function getSchema(filters: object): string {
  return JSON.stringify(toSchemaNode(filters))
}

function getEntityFromToken(): string {
  const previewToken = useCookie('preview-token').value
  const token = useCookie('token').value
  const value = previewToken || token
  return value ? String(value).split('|')[0] : ''
}

function getScopeId(options: VersionedFiltersOptions = {}): string {
  const route = useRoute()
  const routeName = options.routeName ?? String(route.name || '')
  return [
    getEntityFromToken(),
    `${options.tabName || ''}${routeName}`,
  ].join('-')
}

function readStore(): VersionedFiltersStore {
  if (!import.meta.client) {
    return {}
  }

  const rawStore = localStorage.getItem(STORAGE_KEY)
  if (!rawStore) {
    return {}
  }

  try {
    const parsedStore = JSON.parse(rawStore)
    if (!isObjectRecord(parsedStore)) {
      return {}
    }

    return Object
      .entries(parsedStore)
      .reduce<VersionedFiltersStore>((acc, [key, value]) => {
        if (isVersionedEntry(value)) {
          acc[key] = value
        }
        return acc
      }, {})
  }
  catch {
    localStorage.removeItem(STORAGE_KEY)
    return {}
  }
}

function removeLegacyByScope(scopeId: string): object | null {
  if (!import.meta.client) {
    return null
  }

  const suffix = `-${scopeId}`
  const legacyKeys: string[] = []

  // Собираем только legacy-ключи фильтров с префиксом filters-XX.
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i)
    if (key && /^filters-\d+-/.test(key) && key.endsWith(suffix)) {
      legacyKeys.push(key)
    }
  }

  if (!legacyKeys.length) {
    return null
  }

  // Берём ключ с максимальным номером версии префикса и удаляем все legacy.
  const sortedLegacyKeys = legacyKeys.sort((a, b) => {
    const aVersion = Number(a.match(/^filters-(\d+)-/)?.[1] || 0)
    const bVersion = Number(b.match(/^filters-(\d+)-/)?.[1] || 0)
    return bVersion - aVersion
  })

  let candidate: object | null = null
  const currentKey = sortedLegacyKeys[0]
  const currentValue = localStorage.getItem(currentKey)

  if (currentValue) {
    try {
      const parsed = JSON.parse(currentValue)
      if (isObjectRecord(parsed)) {
        candidate = parsed
      }
    }
    catch {
      candidate = null
    }
  }

  for (const key of sortedLegacyKeys) {
    localStorage.removeItem(key)
  }

  return candidate
}

function cleanupStore(store: VersionedFiltersStore): VersionedFiltersStore {
  const now = Date.now()
  const freshEntries = Object
    .entries(store)
    .filter(([, entry]) => now - entry.updatedAt <= TTL_MS)
    .sort(([, a], [, b]) => b.updatedAt - a.updatedAt)
    .slice(0, MAX_ENTRIES)

  return Object.fromEntries(freshEntries)
}

function writeStore(store: VersionedFiltersStore): void {
  if (!import.meta.client) {
    return
  }

  const normalizedStore = cleanupStore(store)
  localStorage.setItem(STORAGE_KEY, JSON.stringify(normalizedStore))
}

export function usePersistentFilters() {
  function load<T extends object>(defaultFilters: T, options: VersionedFiltersOptions = {}): T {
    if (!import.meta.client) {
      return defaultFilters
    }

    const scopeId = getScopeId(options)
    const schema = getSchema(defaultFilters)
    runtimeSchemaCache.set(scopeId, schema)

    const store = readStore()
    const entry = store[scopeId]

    if (entry && entry.schema === schema) {
      return entry.data as T
    }

    if (entry) {
      delete store[scopeId]
    }

    // Пытаемся забрать данные из старых ключей и сразу мигрировать в новый store.
    const legacyFilters = removeLegacyByScope(scopeId)
    if (legacyFilters && getSchema(legacyFilters) === schema) {
      store[scopeId] = {
        schema,
        updatedAt: Date.now(),
        data: legacyFilters,
      }
      writeStore(store)
      return legacyFilters as T
    }

    writeStore(store)
    return defaultFilters
  }

  function save(filters: object, options: VersionedFiltersOptions = {}): void {
    if (!import.meta.client) {
      return
    }

    const scopeId = getScopeId(options)
    const schema = runtimeSchemaCache.get(scopeId) || getSchema(filters)
    const store = readStore()

    store[scopeId] = {
      schema,
      updatedAt: Date.now(),
      data: filters,
    }

    // При сохранении тоже удаляем legacy-ключи, чтобы не раздувать localStorage.
    removeLegacyByScope(scopeId)
    writeStore(store)
  }

  function remove(options: VersionedFiltersOptions = {}): void {
    if (!import.meta.client) {
      return
    }

    const scopeId = getScopeId(options)
    const store = readStore()
    if (store[scopeId]) {
      delete store[scopeId]
      writeStore(store)
    }
    runtimeSchemaCache.delete(scopeId)
    removeLegacyByScope(scopeId)
  }

  return {
    getScopeId,
    getSchema,
    load,
    save,
    remove,
  }
}
