<script setup lang="ts">
import type { ClientDialog } from '#build/components'

const tabs = {
  requests: 'заявки',
  contracts: 'договоры',
  schedule: 'расписание',
  groups: 'группы',
  payments: 'платежи',
  examScores: 'баллы на экзаменах',
  grades: 'оценки',
  reports: 'отчеты',
  clientReviews: 'отзывы',
  webReviews: 'отзывы на сайте',
  tests: 'тесты',
} as const

const selectedTab = ref<keyof typeof tabs>('requests')
const route = useRoute()
const client = ref<ClientResource>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()

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
            <div v-if="client.phones">
              <PhoneList :items="client.phones" />
            </div>
          </div>
        </div>
        <div>
          <div>представитель</div>
          <div class="text-truncate">
            {{ formatName(client.parent) }}
            <div v-if="client.parent.phones">
              <PhoneList :items="client.parent.phones" />
            </div>
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
              entity_type: EntityTypeValue.client,
            }"
          />
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
    <Schedule v-else :client-id="client.id" show-teeth />
    <ClientDialog
      ref="clientDialog"
      @updated="onClientUpdated"
    />
  </template>
</template>
