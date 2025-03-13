<script setup lang="ts">
import type { ClientDialog, PrintSpravkaDialog } from '#build/components'

const tabs = {
  requests: 'заявки',
  contracts: 'договоры',
  schedule: 'расписание',
  groups: 'группы',
  payments: 'платежи',
  examScores: 'баллы на экзаменах',
  grades: 'оценки',
  reports: 'отчёты',
  clientReviews: 'отзывы',
  webReviews: 'отзывы на сайте',
  tests: 'тесты',
  logs: 'логи',
} as const

const selectedTab = ref<keyof typeof tabs>('requests')
const route = useRoute()
const client = ref<ClientResource>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()

const printSpravkaDialog = ref<InstanceType<typeof PrintSpravkaDialog>>()

async function loadData() {
  const { data } = await useHttp<ClientResource>(`clients/${route.params.id}`)
  client.value = data.value!
}

function onClientUpdated(c: ClientResource) {
  client.value = c
}

nextTick(loadData)
</script>

<template>
  <template v-if="client">
    <div class="panel">
      <div class="panel-info">
        <div>
          <UiAvatar :item="client" />
        </div>
        <div>
          <div>ученик</div>
          <div class="text-truncate">
            {{ formatName(client) }}
            <UiLastSeenAt :item="client" />
            <div v-if="client.phones">
              <PhoneList :items="client.phones" show-icons />
            </div>
          </div>
        </div>
        <div>
          <div>представитель</div>
          <div class="text-truncate">
            {{ formatName(client.parent) }}
            <UiLastSeenAt :item="client.parent" />
            <div v-if="client.parent.phones">
              <PhoneList :items="client.parent.phones" show-icons />
            </div>
          </div>
        </div>
        <div>
          <div>направления</div>
          <div>
            <template v-if="client.directions.length > 2">
              {{ plural(client.directions.length, ['направление', 'направления', 'направлений']) }}
            </template>
            <template v-else-if="client.directions.length === 0">
              не установлено
            </template>
            <template v-else>
              {{ client.directions.map(e => DirectionLabel[e]).join(', ') }}
            </template>
          </div>
        </div>
        <div>
          <div>куратор</div>
          <UiPerson v-if="client.head_teacher" :item="client.head_teacher" />
          <div v-else>
            не установлено
          </div>
        </div>
        <div class="panel-actions">
          <CommentBtn
            :entity-id="client.id"
            :entity-type="EntityTypeValue.client"
          />
          <v-btn
            v-bind="props"
            icon="$print"
            :size="48"
            variant="plain"
            @click="printSpravkaDialog?.open(client.id)"
          />
          <PreviewMode :client-id="client.id" />
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientDialog?.edit(client.id)"
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

    <RequestTab v-if="selectedTab === 'requests'" :client-id="client.id" />
    <ReportTab v-else-if="selectedTab === 'reports'" :client-id="client.id" />
    <ClientReviewTab v-else-if="selectedTab === 'clientReviews'" :client-id="client.id" />
    <ContractTab v-else-if="selectedTab === 'contracts'" :client-id="client.id" />
    <WebReviewTab v-else-if="selectedTab === 'webReviews'" :client-id="client.id" />
    <ExamScoreTab v-else-if="selectedTab === 'examScores'" :client-id="client.id" />
    <ClientPaymentTab v-else-if="selectedTab === 'payments'" :client-id="client.id" />
    <GradeTab v-else-if="selectedTab === 'grades'" :client-id="client.id" />
    <ClientGroupsTab v-else-if="selectedTab === 'groups'" :client-id="client.id" />
    <ClientTestTab v-else-if="selectedTab === 'tests'" :client-id="client.id" />
    <LogTab v-else-if="selectedTab === 'logs'" :client-id="client.id" />
    <Schedule v-else :client-id="client.id" show-teeth program-filter />
    <ClientDialog ref="clientDialog" @updated="onClientUpdated" />
  </template>
  <PrintSpravkaDialog ref="printSpravkaDialog" />
</template>

<style lang="scss">
.page-clients-id {
  .last-seen-at {
    font-size: 14px;
    margin-left: 2px;
  }
}
</style>
