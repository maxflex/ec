<script setup lang="ts">
import type { ScheduleDraftGroup, ScheduleDraftProgram } from '.'
import type { SwampListResource } from '../Swamp'
import type { ClientResource } from '~/components/Client'
import { mdiArrowRightThin } from '@mdi/js'
import { apiUrl } from '.'

const { client, year } = defineProps<{
  swamps: SwampListResource[]
  client: ClientResource
  year: Year
}>()

defineEmits<{ back: [] }>()

const loading = ref(false)
const btnLoading = ref(false)
const teeth = ref<Teeth>()
const items = ref<ScheduleDraftProgram[]>()

// TODO: переделать в диалог
const panelElement = document.documentElement.querySelector('.panel')! as HTMLElement

const newPrograms = ref<Program[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/get-initial`,
    {
      method: 'POST',
      body: {
        client_id: client.id,
        year,
      },
    },
  )
  items.value = data.value!
  loading.value = false
  await loadTeeth()
}

async function loadTeeth() {
  const { data } = await useHttp<Teeth>(
    `teeth`,
    {
      params: {
        year,
        client_id: client.id,
      },
    },
  )
  teeth.value = data.value!
}

async function save() {
  btnLoading.value = true
  await useHttp(
    `${apiUrl}/save`,
    {
      method: 'POST',
      body: {
        year,
        client_id: client.id,
        data: items.value.map(p => ({
          ...p,
          group_id: items.value![p.id].swamp?.group_id || null,
        })),
      },
    },
  )
  btnLoading.value = false
}

async function addToGroup(p: ScheduleDraftProgram, g: ScheduleDraftGroup) {
  loading.value = true
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/add-to-group`,
    {
      method: 'post',
      body: {
        program_id: p.id,
        group_id: g.id,
      },
    },
  )
  items.value = data.value!
  loading.value = false
}

async function removeFromGroup(g: ScheduleDraftGroup) {
  loading.value = true
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/remove-from-group`,
    {
      method: 'post',
      body: {
        program_id: g.swamp!.id,
      },
    },
  )
  items.value = data.value!
  loading.value = false
}

async function onNewProgramsUpdated(newVal: Program[]) {
  loading.value = true
  const { data } = await useHttp<ScheduleDraftProgram[]>(
    `${apiUrl}/new-programs`,
    {
      method: 'post',
      body: {
        new_programs: newVal,
      },
    },
  )
  items.value = data.value!
  loading.value = false
  smoothScroll('main', 'bottom', 'instant')
}

watch(newPrograms, onNewProgramsUpdated)

onMounted(() => {
  panelElement.style.display = 'none'
})

onUnmounted(() => {
  panelElement.style.display = 'block'
})

nextTick(loadData)
</script>

<template>
  <UiIndexPage
    class="schedule-project"
    :data="{ loading: loading && !items, noData: false }"
    sticky
  >
    <template #filters>
      <!-- <div class="d-flex align-center ga-6">
        <UiAvatar :item="client" :size="60" />
        <div class="panel-info pa-0" style="border: none">
          <div>
            <div>
              ученик
            </div>
            <div>
              {{ formatName(client) }}
            </div>
          </div>
        </div>
      </div> -->
      <div class="schedule-project__header">
        <v-btn icon="$back" :size="44" variant="plain" @click="$emit('back')" />
        {{ formatName(client) }}
      </div>
      <TeethBar v-if="teeth" :items="teeth" style="width: fit-content" />
    </template>
    <template #buttons>
      <div class="d-flex align-center ga-4">
        <v-menu>
          <template #activator="{ props }">
            <v-btn color="primary" v-bind="props" :loading="btnLoading">
              действия
            </v-btn>
          </template>
          <v-list>
            <v-list-item @click="save()">
              сохранить проект
            </v-list-item>
            <v-list-item @click="() => contractVersionDialog?.newVersion(selectedContract!)">
              загрузить проект
            </v-list-item>
            <v-list-item @click="() => contractPaymentDialog?.create(selectedContract!)">
              применить проект
            </v-list-item>
          </v-list>
        </v-menu>
        <!-- <v-btn icon="$close" :size="44" color="primary" @click="$emit('back')" /> -->
      </div>
    </template>
    <template v-if="items">
      <div v-for="item in items" :key="item.id" class="schedule-project__data" :class="{ 'element-loading': loading }">
        <div class="schedule-project__contract">
          <div class="schedule-project__contract-header">
            <span>
              <template v-if="item.contract">
                Договор №{{ item.contract.id }}
              </template>
              <span v-else class="text-gray">
                Проект договора
              </span>
            </span>
            <span>
              {{ ProgramLabel[item.program] }}
            </span>
            <template v-if="item.swamp">
              <span :class="`swamp-status swamp-status--${item.swamp.status}`" style="background-color: transparent;">
                <span>
                  {{ SwampStatusLabel[item.swamp.status] }}
                </span>
              </span>
              <span v-if="item.contract">
                {{ item.swamp.lessons_conducted }}
                <v-icon :icon="mdiArrowRightThin" :size="20" class="vfn-1" />
                {{ item.swamp.total_lessons }}
              </span>
            </template>
          </div>
        </div>
        <ScheduleDraftList
          v-if="item.groups.length"
          :items="item.groups"
          @add-to-group="g => addToGroup(item, g)"
          @remove-from-group="removeFromGroup"
        />
        <div v-else class="schedule-project__no-groups">
          <UiNoData>
            не найдено групп
          </UiNoData>
        </div>
      </div>
      <div class="schedule-project__data" :class="{ 'element-loading': loading }">
        <div class="schedule-project__contract">
          <ProgramSelectorMenu
            :pre-selected="newPrograms"
            @saved="newVal => (newPrograms = newPrograms.concat(newVal))"
          />
        </div>
      </div>
    </template>
  </UiIndexPage>
</template>

<style lang="scss">
.schedule-project {
  &__header {
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 2px;
    width: fit-content !important;
    padding-right: 20px;
  }

  &__filters {
    --height: 64px !important;
    padding-left: 7px !important;
  }

  &__contract {
    // padding: var(--padding);
    padding: var(--padding);
    // background: rgba(var(--v-theme-bg), 0.1);
    background-color: rgb(var(--v-theme-bg));
    display: flex;
    align-items: center;
    gap: 20px;
    position: relative;
    justify-content: space-between;
    border-top: 1px solid rgb(var(--v-theme-border));
    border-bottom: 1px solid rgb(var(--v-theme-border));
    &-header {
      display: flex;
      align-items: center;
      gap: 20px;

      & > span:first-child {
        font-weight: bold;
      }
    }
    .program-selector {
      max-width: 256px !important;
      .v-input__append {
        display: none;
      }
    }
  }

  &__data {
    &:first-child {
      .schedule-project__contract {
        border-top: none !important;
      }
    }
  }

  &__no-groups {
    height: 200px;
    position: relative;
  }
}
</style>
