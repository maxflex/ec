<script setup lang="ts">
import { ClientDialog } from '#components'

interface ControlLkResource extends PersonResource {
  last_seen_at: string | null
  phones: PhoneResource[]
  logs_count: number
  tg_logs_count: number
  comments_count: number
  directions: ClientDirections
  representative: PersonResource & {
    phones: PhoneResource[]
    last_seen_at: string | null
    logs_count: number
    tg_logs_count: number
    reports_read_count: number
    reports_published_count: number
  }
}

const filters = ref<{
  year: Year
  direction: Direction[]
}>({
  year: currentAcademicYear(),
  direction: [],
})

const clientDialog = ref<InstanceType<typeof ClientDialog>>()

const { indexPageData, items } = useIndex<ControlLkResource>(`control/lk`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiYearSelector v-model="filters.year" disabled density="comfortable" />
      <UiMultipleSelect
        v-model="filters.direction"
        density="comfortable"
        :items="selectItems(DirectionLabel)"
        label="Направление"
      />
    </template>
    <template #buttons>
      <UiQuestionTooltip>
        На данной странице отображаются клиенты, допущенные ко входу в личный кабинет.
        Доступ закрывается 30 июня {{ filters.year + 1 }} для договоров {{ YearLabel[filters.year] }} или в случае расторжения
      </UiQuestionTooltip>
    </template>
    <div class="control-lk table table--padding flex-start">
      <div v-for="item in items" :key="item.id" class="control-lk__item">
        <div class="control-lk__item-client">
          <div class="control-lk__name">
            <UiPerson :item="item" />
          </div>
          <div class="control-lk__phones">
            <PhoneList :items="item.phones" />
          </div>
          <div class="control-lk__last-seen-at">
            <UiLastSeenAt :item="item" />
          </div>
          <div class="control-lk__actions">
            {{ item.logs_count }} /
            <span class="text-secondary">
              {{ item.tg_logs_count }}
            </span>
          </div>
          <div class="control-lk__directions">
            <ClientDirections :items="item.directions" />
          </div>
        </div>
        <div class="control-lk__item-representative">
          <div class="control-lk__name">
            <UiPerson :item="item.representative" />
          </div>

          <div class="control-lk__phones">
            <PhoneList :items="item.representative.phones" />
          </div>
          <div class="control-lk__last-seen-at">
            <UiLastSeenAt :item="item.representative" />
          </div>
          <div class="control-lk__actions">
            {{ item.representative.logs_count }} /
            <span class="text-secondary">
              {{ item.representative.tg_logs_count }}
            </span>
          </div>
          <!-- <div class="control-lk__reports-read">
          {{ item.representative.reports_published_count }} /
          {{ item.representative.reports_read_count }}
        </div> -->
          <div>
            отчеты:
            {{ item.representative.reports_read_count }} /
            {{ item.representative.reports_published_count }}
          </div>
        </div>
        <div class="control-lk__comment">
          <CommentBtn
            :size="42"
            :class="{ 'no-items': item.comments_count === 0 }"
            :count="item.comments_count"
            :entity-id="item.id"
            :entity-type="EntityTypeValue.client"
            extra
          />
          <div class="vfn-1">
            <v-btn
              icon="$edit"
              :size="42"
              variant="plain"
              @click="clientDialog?.edit(item.id)"
            />
          </div>
        </div>
      </div>
    </div>
  </UiIndexPage>
  <ClientDialog ref="clientDialog" />
</template>
