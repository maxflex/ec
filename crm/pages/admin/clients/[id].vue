<script setup lang="ts">
import type { ClientDialog, PrintSpravkaDialog } from '#build/components'
import type { ClientResource } from '~/components/Client'
import { mdiTable } from '@mdi/js'

const tabs = {
  requests: 'заявки',
  contracts: 'договоры',
  schedule: 'расписание',
  events: 'события',
  groups: 'группы',
  payments: 'платежи',
  examScores: 'баллы',
  markSheet: 'ведомость',
  grades: 'оценки',
  reports: 'отчёты',
  webReviews: 'отзывы',
  tests: 'тесты',
  logs: 'логи',
  clientComplaints: 'жалобы',
  clientReviews: 'отзывы',
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
        <div class="client-avatar">
          <UiAvatar :item="client" :size="140" />
          <!-- <v-tooltip :width="450">
            <template #activator="{ props }">
              <div
                v-bind="props"
                :class="`client-avatar-status client-avatar-status--${client.can_login ? 'active' : 'inactive'}`"
              >
              </div>
            </template>
            <template v-if="client.can_login">
              У ученика и представителя есть доступ в личный кабинет и активный пропуск.
              Доступ предоставляется до 30 июня при наличии нерасторгнутого договора на {{ currentYear }} или {{ currentYear + 1 }} учебный год
            </template>
            <template v-else>
              Пропуск неактивен
            </template>
          </v-tooltip> -->
        </div>
        <div>
          <div>ученик</div>
          <div class="text-truncate">
            {{ formatName(client) }}
            <div v-if="client.phones" class="mt-5">
              <PhoneList :items="client.phones" show-icons />
            </div>
          </div>
        </div>
        <div>
          <div>представитель</div>
          <div class="text-truncate">
            {{ formatName(client.parent) }}
            <div v-if="client.parent.phones" class="mt-5">
              <PhoneList :items="client.parent.phones" show-icons />
            </div>
          </div>
        </div>
        <div>
          <div>направления</div>
          <div>
            <ClientDirections :item="client.directions" />
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
    <ContractTab v-else-if="selectedTab === 'contracts'" :client-id="client.id" />
    <WebReviewTab v-else-if="selectedTab === 'webReviews'" :client-id="client.id" />
    <ExamScoreTab v-else-if="selectedTab === 'examScores'" :client-id="client.id" />
    <ClientPaymentTab v-else-if="selectedTab === 'payments'" :client-id="client.id" />
    <GradeTab v-else-if="selectedTab === 'grades'" :client-id="client.id" />
    <ClientGroupsTab v-else-if="selectedTab === 'groups'" :client="client" />
    <ClientTestTab v-else-if="selectedTab === 'tests'" :client-id="client.id" />
    <EventTab v-else-if="selectedTab === 'events'" :client-id="client.id" />
    <LogTab v-else-if="selectedTab === 'logs'" :client-id="client.id" />
    <ClientMarkSheetTab v-else-if="selectedTab === 'markSheet'" :client="client" />
    <ClientComplaintTab v-else-if="selectedTab === 'clientComplaints'" :client-id="client.id" />
    <ClientReviewTab v-else-if="selectedTab === 'clientReviews'" :client-id="client.id" />
    <Schedule v-else :client-id="client.id" program-filter />
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

.client-avatar {
  position: relative;
  &-status {
    $size: 18px;
    height: $size;
    width: $size;
    position: absolute;
    top: 10px;
    right: 12px;
    border-radius: 50%;
    border: 3px solid white;
    cursor: default;

    &--active {
      background-color: rgb(var(--v-theme-success));
    }

    &--inactive {
      background-color: rgb(var(--v-theme-gray));
    }
  }
}
</style>
