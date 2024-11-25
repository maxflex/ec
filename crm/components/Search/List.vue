<script setup lang="ts">
const { items, q } = defineProps<{
  items: SearchResultResource[]
  q: string
}>()

const qWords = q.split(' ').filter(Boolean)

function getLink(item: SearchResultResource): string {
  switch (item.entity_type) {
    case EntityTypeValue.teacher:
      return `teachers/${item.id}`
    case EntityTypeValue.clientParent:
      return `clients/${item.client_id}`
  }
  return `clients/${item.id}`
}

// Подсветить результаты поиска. Временно
function highlight(text: string) {
  let result = text.trim()
  for (const word of qWords) {
    const newQ = word.replace(')', '\\)').replace('(', '\\(')
    result = result.replace(
      new RegExp(`${newQ}`, 'gi'),
      `<span class="highlight">${word}</span>`,
    )
  }
  return result
}
</script>

<template>
  <div class="table table--hover search-result">
    <RouterLink
      v-for="item in items"
      :key="`${item.entity_type}${item.id}`"
      :to="getLink(item)" class="table-item"
    >
      <div>
        <UiAvatar :item="item" :size="80" />
      </div>
      <div class="search-result__info">
        <div>
          <div v-html="highlight(formatFullName(item))" />
          <div :class="{ 'text-success': item.is_active }">
            <template v-if="item.entity_type === EntityTypeValue.teacher">
              {{ TeacherStatusLabel[item.status!] }}
            </template>
            <template v-else-if="item.is_active">
              активный клиент
            </template>
          </div>
          <div>
            {{ EntityTypeLabel[item.entity_type] }}
          </div>
        </div>
        <div v-if="item.contract_versions" class="search-result__contract-versions">
          <div v-if="item.contract_versions.length === 0" class="text-gray">
            нет договоров
          </div>
          <div v-for="cv in item.contract_versions" :key="cv.id">
            договор №{{ cv.contract.id }} на {{ YearLabel[cv.contract.year] }}
          </div>
        </div>

        <div v-if="item.subjects">
          {{ item.subjects.map(s => SubjectLabel[s]).join(', ') }}
        </div>
        <div v-if="item.phones.length === 0" class="text-gray">
          нет контактов
        </div>
        <PhoneList v-else :items="item.phones" :q="q" />
      </div>
    </RouterLink>
  </div>
</template>

<style lang="scss">
.search-result {
  .table-item {
    padding: 10px 20px !important;
    align-items: flex-start !important;
    display: inline-flex !important;
    width: 100%;
  }
  &__info {
    display: flex;
    flex-direction: column;
    gap: 8px;
    // первая строчка: имя, тип сущности
    & > div:first-child {
      display: flex;
      align-items: center;
      justify-content: space-between;
      width: 100%;
      gap: 18px;
      & > div {
        &:first-child {
          font-weight: 500;
        }
        &:last-child {
          flex: 1;
          text-align: right;
        }
        &:not(:first-child) {
          color: rgb(var(--v-theme-placeholder));
        }
      }
    }
  }
  .highlight {
    //background: rgba(var(--v-theme-orange), 0.1);
  }
}
</style>
