<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'
import type { TelegramListDialog } from '#components'

const telegramListDialog = shallowRef<InstanceType<typeof TelegramListDialog>>()
const route = useRoute()

const item = ref<EventResource>()

async function loadData() {
  const { data } = await useHttp<EventResource>(
      `common/events/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

function sendMessage() {
  if (!item.value) {
    return
  }
  const { clients, teachers } = item.value.recipients!
  telegramListDialog.value?.open({
    clients: clients.map(e => e.entity.id),
    teachers: teachers.map(e => e.entity.id),
  }, item.value)
}

nextTick(loadData)
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="item === undefined" />
    <div v-else class="show">
      <h2>
        {{ item.name }}
      </h2>
      <div class="show__content">
        <div>
          <div>
            Описание
          </div>
          <div style="width: 900px">
            {{ item.description }}
          </div>
        </div>
        <div>
          <div>Дата и время</div>
          <div>
            {{ formatDate(item.date) }}
            <template v-if="item.time">
              {{ formatTime(item.time) }}
            </template>
          </div>
        </div>
        <div>
          <div>Тип</div>
          <div>
            {{ item.is_afterclass ? 'внеклассное' : 'учебное' }} событие
          </div>
        </div>
        <template v-for="(people, key) in item.recipients">
          <div v-if="people.length" :key="key">
            <div>
              {{ key === 'clients' ? 'Клиенты' : 'Преподаватели' }}
            </div>
            <div class="table table--padding">
              <div v-for="p in people" :key="p.id">
                <div style="width: 380px">
                  <UiPersonLink :item="p.entity" :type="key" />
                </div>
                <div>
                  <span v-if="p.confirmation === 'confirmed'" class="text-success">
                    <v-icon :icon="mdiCheckAll" size="16" class="vfn-1 mr-1" />
                    подтвердил участие
                  </span>
                  <span v-else-if="p.confirmation === 'rejected'" class="text-error">
                    <v-icon icon="$close" size="16" class="vfn-1 mr-1" />
                    отказался от участия
                  </span>
                  <span v-else class="text-gray">
                    <v-icon icon="$complete" size="16" class="vfn-1 mr-1" />
                    не подтвердил участие
                  </span>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
      <v-btn color="secondary" class="mt-8" @click="sendMessage()">
        сообщение участникам
        <template #append>
          <v-icon icon="$send" />
        </template>
      </v-btn>
    </div>
  </v-fade-transition>
  <TelegramListDialog ref="telegramListDialog" />
</template>
