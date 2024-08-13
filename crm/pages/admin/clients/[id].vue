<script setup lang="ts">
import type { ClientDialog } from '#build/components'
import type { Filters } from '~/components/Report/TeacherFilters.vue'

const tabs = {
  requests: 'заявки',
  contracts: 'договоры',
  schedule: 'расписание',
  groups: 'группы',
  payments: 'платежи',
  examScores: 'баллы на экзаменах',
  grades: 'оценки',
  reports: 'отчеты',
  reviews: 'отзывы',
  webReviews: 'отзывы на сайте',
  tests: 'тесты',
} as const

const selectedTab = ref<keyof typeof tabs>('requests')
const route = useRoute()
const client = ref<ClientResource>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()
const reportFilters = ref<Filters>({
  year: currentAcademicYear(),
})
const gradeFilters = ref<{ year: Year }>({
  year: currentAcademicYear(),
})

async function loadData() {
  const { data } = await useHttp(`clients/${route.params.id}`)
  client.value = data.value as ClientResource
}

function onClientUpdated(c: ClientResource) {
  client.value = c
}

nextTick(loadData)
</script>

<template>
  <div
    v-if="client"
    class="client"
  >
    <div class="panel">
      <div class="panel-info">
        <div>
          <UiAvatar :item="client" />
        </div>
        <div>
          <div>ученик</div>
          <div class="text-truncate">
            {{ formatFullName(client) }}
            <PhoneActions :items="client.phones" :person="client" />
          </div>
        </div>
        <div>
          <div>представитель</div>
          <div class="text-truncate">
            {{ formatFullName(client.parent) }}
            <PhoneActions :items="client.parent.phones" :person="client.parent" />
          </div>
        </div>
        <div>
          <div>филиалы</div>
          <UiBranches :branches="client.branches" />
        </div>
        <div>
          <div>куратор</div>
          <!-- TODO: подумать, че делать с учителями -->
          <!-- <div v-if="client.head_teacher">
            <router-link
              :to="{
                name: 'teachers-id',
                params: { id: client.head_teacher_id },
              }"
            >
              {{ formatName(client.head_teacher) }}
            </router-link>
          </div> -->
          <!-- <div v-else> -->
          <div>
            не установлено
          </div>
        </div>
        <div class="panel-actions">
          <CommentBtn
            :entity-id="client.id"
            entity-type="client"
          />
          <PreviewModeBtn
            :user="{
              id: client.id,
              entity_type: EntityType.client,
            }"
          />
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientDialog?.edit(client!)"
          />
        </div>
      </div>
      <div class="tabs">
        <div
          v-for="(label, key) in tabs"
          :key="key"
          class="tabs-item"
          :class="{ 'tabs-item--active': selectedTab === key }"
          @click="selectedTab = key"
        >
          {{ label }}
        </div>
      </div>
    </div>
    <div class="client__content">
      <UiDataLoader
        v-if="selectedTab === 'requests'"
        url="requests"
        :filters="{ client_id: client.id }"
      >
        <template #default="{ items }">
          <RequestList :model-value="items" />
        </template>
      </UiDataLoader>
      <UiDataLoader
        v-else-if="selectedTab === 'contracts'"
        url="contracts"
        :filters="{ client_id: client.id }"
      >
        <template #default="{ items }">
          <ContractList :items="items" />
        </template>
      </UiDataLoader>
      <UiDataLoader
        v-else-if="selectedTab === 'reports'"
        url="reports"
        :filters="{ client_id: client.id, ...reportFilters }"
      >
        <template #filters>
          <div class="filters">
            <ReportTeacherFilters @apply="f => (reportFilters = f)" />
          </div>
        </template>
        <template #default="{ items }">
          <ReportList :items="items" />
        </template>
      </UiDataLoader>
      <UiDataLoader
        v-else-if="selectedTab === 'reviews'"
        url="client-reviews"
        :filters="{
          client_id: client.id,
          with: 'client',
        }"
      >
        <template #default="{ items }">
          <ClientReviewList :items="items" />
        </template>
      </UiDataLoader>
      <WebReviewTab v-else-if="selectedTab === 'webReviews'" :client-id="client.id" />
      <ExamScoreTab v-else-if="selectedTab === 'examScores'" :client-id="client.id" />
      <ClientPaymentTab v-else-if="selectedTab === 'payments'" :client-id="client.id" />
      <LessonList
        v-else-if="selectedTab === 'schedule'"
        :id="client.id"
        entity="client"
        show-teeth
      />
      <ClientGroupsTab v-else-if="selectedTab === 'groups'" :client-id="client.id" />
      <UiDataLoader
        v-else-if="selectedTab === 'grades'"
        :key="`grades${gradeFilters.year}`"
        url="grades"
        :filters="{
          client_id: client.id,
          ...gradeFilters,
        }"
      >
        <template #filters>
          <div class="filters">
            <div class="filters-inputs">
              <div>
                <v-select
                  v-model="gradeFilters.year"
                  label="Учебный год"
                  :items="selectItems(YearLabel)"
                  density="comfortable"
                />
              </div>
            </div>
          </div>
        </template>
        <template #default="{ items }">
          <GradeList :items="items" />
        </template>
      </UiDataLoader>
      <ClientTestTab
        v-else
        :client-id="client.id"
      />
    </div>
    <ClientDialog
      ref="clientDialog"
      @updated="onClientUpdated"
    />
  </div>
</template>

<style lang="scss">
.client {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  &__content {
    flex: 1;
    display: flex;
    flex-direction: column;
    & > div {
      height: 100%;
    }
  }
  .request-list {
    flex: 1;
  }
}
</style>
