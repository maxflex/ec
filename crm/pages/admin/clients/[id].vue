<script setup lang="ts">
import type {
  ClientDialog,
  GroupSelectorDialog,
  TestSelectorDialog,
} from '#build/components'
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
const testSelectorDialog = ref<InstanceType<typeof TestSelectorDialog>>()
const groupSelectorDialog = ref<InstanceType<typeof GroupSelectorDialog>>()
const reportFilters = ref<Filters>({
  year: currentAcademicYear(),
})
const groupFilters = ref<{ year: Year }>({
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

async function onGroupSelected(g: GroupListResource) {
  await useHttp(`groups/add-client`, {
    method: 'post',
    params: {
      group_id: g.id,
      client_id: client.value?.id,
    },
  })
  loadData()
}

function onTestsSaved(tests: TestResource[]) {
  if (!client.value) {
    return
  }
  client.value.tests = [...tests]
  useHttp(`tests/add-client/${client.value.id}`, {
    method: 'post',
    body: { ids: tests.map(t => t.id) },
  })
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
      <UiDataLoader
        v-else-if="selectedTab === 'webReviews'"
        url="web-reviews"
        :filters="{
          client_id: client.id,
          with: 'client',
        }"
      >
        <template #default="{ items }">
          <WebReviewList :items="items" />
        </template>
      </UiDataLoader>
      <ExamScoreTab v-else-if="selectedTab === 'examScores'" :client-id="client.id" />
      <ClientPaymentTab v-else-if="selectedTab === 'payments'" :client-id="client.id" />
      <LessonList
        v-else-if="selectedTab === 'schedule'"
        :id="client.id"
        entity="client"
      />
      <UiDataLoader
        v-else-if="selectedTab === 'groups'"
        :key="`groups${groupFilters.year}`"
        url="groups"
        :filters="{
          client_id: client.id,
          ...groupFilters,
        }"
      >
        <template #filters>
          <div class="filters">
            <div class="filters-inputs">
              <div>
                <v-select
                  v-model="groupFilters.year"
                  label="Учебный год"
                  :items="selectItems(YearLabel)"
                  density="comfortable"
                />
              </div>
            </div>
          </div>
        </template>
        <template #default="{ items }">
          <div class="table table--padding">
            <GroupList :items="items" />
            <GroupSwamp
              v-for="swamp in client.swamps"
              :key="swamp.id"
              :swamp="swamp"
              @attach="(s) => groupSelectorDialog?.open(s.program)"
            />
          </div>
        </template>
      </UiDataLoader>
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
      <div
        v-else
      >
        <ClientTestList :tests="client.tests" />
        <TestSelectorDialog
          ref="testSelectorDialog"
          @saved="onTestsSaved"
        />
        <div style="margin: 20px">
          <v-btn
            color="primary"
            @click="() => testSelectorDialog?.open()"
          >
            добавить тест
          </v-btn>
        </div>
      </div>
    </div>
    <ClientDialog
      ref="clientDialog"
      @updated="onClientUpdated"
    />
    <GroupSelectorDialog
      ref="groupSelectorDialog"
      @select="onGroupSelected"
    />
  </div>
</template>

<style lang="scss">
.client {
  // padding: 20px;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  &__content {
    flex: 1;
    display: flex;
    flex-direction: column;
    & > div {
      // padding: 20px;
      height: 100%;
    }
  }
  // & > div {
  //   &:not(:first-child) {
  //     margin-top: 60px;
  //   }
  // }

  // .request-list,
  // .table {
  //   left: -20px;
  //   position: relative;
  //   width: calc(100% + 40px);
  // }
  .request-list {
    padding-top: 0 !important;
    flex: 1;
    // background: red;
    // & > .request:first-child {
    //   border-top: 1px solid #e0e0e0;
    // }
  }
}
</style>
