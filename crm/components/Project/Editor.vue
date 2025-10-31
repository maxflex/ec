<script setup lang="ts">
import type { Project, ProjectGroup, ProjectProgram, ProjectResource } from '.'
import type { ClientResource } from '../Client'
import { ContractVersionDialog, ProjectNameDialog } from '#components'
import { mdiChevronRight } from '@mdi/js'
import { Vue3SlideUpDown } from 'vue3-slide-up-down'
import { apiUrl, isGroupChangedInContract } from '.'

const { client, savedProject } = defineProps<{
  /**
   * Если загружаем конкретный ID проекта
   */
  savedProject?: ProjectResource
  client?: ClientResource
}>()

defineEmits<{ back: [] }>()

const router = useRouter()
const loading = ref(true)
const teeth = ref<Teeth>()
const project = ref<Project>()
const selectedContractId = ref<number>() // ID выбранной вкладки договора
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const nameDialog = ref<InstanceType<typeof ProjectNameDialog>>()
const key = ref(0)
// название проекта (можно добавлять к проектам без клиента)
const projectName = ref<string>()

// свернуть панель клиента (чтоб не занимало вертикальное пространство)
const isMinimized = ref(false)

// сохранённые проекты расписания (доступные для загрузки)
const savedProjects = ref<ProjectResource[]>([])

async function fromActualContracts() {
  loading.value = true
  const { data } = await useHttp<Project>(
    `${apiUrl}/from-actual-contracts`,
    {
      params: {
        id: savedProject?.id,
        client_id: client?.id,
      },
    },
  )
  project.value = data.value!
  // выбираем первую вкладку
  selectedContractId.value = Number.parseInt(Object.keys(project.value)[0])
  loading.value = false
  smoothScroll('main', 'top', 'instant')
  loadSavedProjects()

  if (savedProject) {
    selectedContractId.value = savedProject.contract_id || -1
  }
}

async function loadSavedProjects() {
  if (!client) {
    return
  }

  const { data } = await useHttp<ApiResponse<ProjectResource>>(apiUrl, {
    params: {
      client_id: client.id,
    },
  })
  savedProjects.value = data.value!.data
}

async function applyMoveGroups(contractId: number) {
  loading.value = true
  const { data, error } = await useHttp<Project>(
    `${apiUrl}/apply-move-groups`,
    {
      method: 'POST',
      body: {
        contract_id: contractId,
      },
    },
  )

  if (error.value) {
    useGlobalMessage(error.value.data?.message, 'error')
    loading.value = false

    return
  }

  project.value = data.value!
  loading.value = false
  useGlobalMessage('Перемещения в группах применены', 'success')
}

async function getTeeth() {
  const { data } = await useHttp<Teeth>(`${apiUrl}/get-teeth`)
  teeth.value = data.value!
}

async function addToGroup(p: ProjectProgram, g: ProjectGroup) {
  loading.value = true
  const { data, error } = await useHttp<Project>(
    `${apiUrl}/add-to-group`,
    {
      method: 'post',
      body: {
        program_id: p.id,
        group_id: g.id,
      },
    },
  )
  if (error.value) {
    useGlobalMessage(`<b>${ProgramShortLabel[p.program]}</b> – по этой программе ученик находится в другой группе`, 'error')
    loading.value = false

    return
  }

  project.value = data.value!
  loading.value = false
}

async function removeFromGroup(g: ProjectGroup) {
  loading.value = true
  const { data } = await useHttp<Project>(
    `${apiUrl}/remove-from-group`,
    {
      method: 'post',
      body: {
        program_id: g.swamp!.id,
      },
    },
  )
  project.value = data.value!
  loading.value = false
}

async function addPrograms(newPrograms: Program[], contractId: number) {
  selectedContractId.value = Number.parseInt(contractId)
  key.value++
  loading.value = true
  const { data } = await useHttp<Project>(
    `${apiUrl}/add-programs`,
    {
      method: 'post',
      body: {
        contract_id: contractId,
        new_programs: newPrograms,
      },
    },
  )
  project.value = data.value!
  loading.value = false
  smoothScroll('main', 'bottom', 'instant')
}

async function removeProgram(p: ProjectProgram) {
  loading.value = true
  const { data } = await useHttp<Project>(
    `${apiUrl}/remove-program`,
    {
      method: 'post',
      body: {
        id: p.id,
      },
    },
  )
  project.value = data.value!
  loading.value = false
}

async function save(contractId: number) {
  const { data } = await useHttp<ProjectResource>(
    `${apiUrl}/save`,
    {
      method: 'POST',
      body: {
        contract_id: contractId,
        name: projectName.value,
      },
    },
  )
  const id = data.value!.id
  const link = router.resolve({ name: 'projects-editor', query: { id } }).href
  useGlobalMessage(`<a href="${link}">Проект ${id}</a> сохранён`, 'success')
  loadSavedProjects()
}

function getChangesCnt(contractId: number | string) {
  const cId = Number.parseInt(contractId as string)
  let cnt = 0

  for (const program of project.value![cId]) {
    if (program.id < 0) {
      cnt++
    }

    for (const group of program.groups) {
      if (isGroupChangedInContract(group, cId)) {
        cnt++
      }
    }
  }

  return cnt
}

const savedProjectsByContract = computed<Record<number, ProjectResource[]>>(() => {
  const result: Record<number, ProjectResource[]> = {}
  for (const contractId in project.value) {
    const cId = Number.parseInt(contractId)
    result[cId] = savedProjects.value.filter(e => e.contract_id === (cId === -1 ? null : cId))
  }
  return result
})

async function loadProject(d: ProjectResource) {
  selectedContractId.value = d.contract_id || -1
  loading.value = true
  const { data } = await useHttp<Project>(
    `${apiUrl}/load/${d.id}`,
    {
      method: 'POST',
    },
  )
  project.value = data.value!
  loading.value = false
}

watch(project, getTeeth)
watch(selectedContractId, () => smoothScroll('main', 'top', 'instant'))

nextTick(fromActualContracts)
</script>

<template>
  <!-- <template #filters>
      <div class="project__header">
        <template v-if="client">
          <v-btn
            icon="$back" :size="44" variant="plain" color="black"
            :to="{ name: 'clients-id', params: { id: client.id }, hash: '#contracts' }"
          />
          {{ formatName(client) }}
        </template>
        <template v-else>
          <v-btn
            icon="$back" :size="44" variant="plain" color="black"
            :to="{ name: 'projects' }"
          />
          <template v-if="projectName">
            {{ projectName }}
          </template>
          <template v-else>
            {{ savedProject?.name || 'Новый проект' }}
          </template>
        </template>
      </div>
      <TeethBar v-if="teeth" :items="teeth" style="width: fit-content" />
      <div class="project__comments">
        <v-btn
          v-if="!client && (!savedProject || !savedProject.client)"
          icon="$edit"
          color="gray"
          :size="42"
          variant="plain"
          class="vfn-2"
          @click="nameDialog?.open()"
        />
        <CommentBtn
          v-if="savedProject"
          color="gray"
          :size="42"
          :class="{ 'no-items': savedProject.comments_count === 0 }"
          :count="savedProject.comments_count"
          :entity-id="savedProject.id"
          :entity-type="EntityTypeValue.project"
        />
      </div>
    </template> -->
  <UiLoader v-if="loading && !project" />
  <template v-else>
    <div class="panel" :class="{ 'panel--minimized': isMinimized }">
      <Vue3SlideUpDown :model-value="!isMinimized" :duration="150">
        <ClientPanel v-if="client" :item="client" />
        <div v-else class="panel-info panel-info__empty-project">
          <div class="font-weight-bold">
            <template v-if="projectName">
              {{ projectName }}
            </template>
            <template v-else>
              {{ savedProject?.name || 'Новый проект' }}
            </template>
          </div>
          <div class="panel-actions">
            <CommentBtn
              v-if="savedProject"
              color="gray"
              :size="42"
              :class="{ 'no-items': savedProject.comments_count === 0 }"
              :count="savedProject.comments_count"
              :entity-id="savedProject.id"
              :entity-type="EntityTypeValue.project"
            />
            <v-btn
              icon="$edit"
              color="gray"
              :size="42"
              variant="plain"
              class="vfn-2"
              @click="nameDialog?.open()"
            />
          </div>
        </div>
      </Vue3SlideUpDown>
      <div class="panel-schedule">
        <TeethBar v-if="teeth" :items="teeth" />
        <TeethBar v-else :items="[]" />
        <v-btn
          icon="$collapse" color="primary"
          :size="38"
          style=""
          @click="isMinimized = !isMinimized"
        />
      </div>
      <div v-if="project && selectedContractId" class="tabs">
        <div
          v-for="(_, contractId) in project"
          :key="contractId"
          class="tabs-item"
          :class="{
            'tabs-item--active': selectedContractId === Number.parseInt(contractId),
          }"
          @click="selectedContractId = Number.parseInt(contractId)"
        >
          <span v-if="contractId > 0">
            договор №{{ contractId }}
          </span>
          <span v-else>
            новый договор
          </span>
          <v-badge
            v-if="getChangesCnt(contractId)"
            color="orange-lighten-3"
            inline
            :content="getChangesCnt(contractId)"
          ></v-badge>
          <v-menu :key="key">
            <template #activator="{ props }">
              <v-icon icon="$next" v-bind="props" />
            </template>
            <v-list>
              <v-list-item link>
                добавить программы
                <template #append>
                  <v-icon :icon="mdiChevronRight" />
                </template>
                <ProgramSelectorNested
                  :pre-selected="project[contractId].map(e => e.program)"
                  @saved="newPrograms => addPrograms(newPrograms, contractId)"
                />
              </v-list-item>
              <v-list-item
                :disabled="!getChangesCnt(contractId) || !client"
                @click="contractVersionDialog?.fromProject({ contractId })"
              >
                {{ contractId < 0 ? 'создать новый договор' : 'добавить версию' }}
                на основе проекта
              </v-list-item>
              <v-list-item :disabled="contractId < 0 || !getChangesCnt(contractId)" @click="applyMoveGroups(contractId)">
                применить перемещения в группах
              </v-list-item>
              <v-list-item :disabled="!getChangesCnt(contractId)" @click="save(contractId)">
                сохранить проект
              </v-list-item>
              <v-list-item
                v-for="d in savedProjectsByContract[contractId]"
                :key="d.id"
                :disabled="d.is_archived"
                @click="loadProject(d)"
              >
                <div>
                  загрузить проект №{{ d.id }}
                  <v-badge
                    v-if="d.changes > 0"
                    color="orange-lighten-3"
                    inline
                    :content="d.changes"
                  ></v-badge>
                </div>
                <div class="text-caption text-gray">
                  Создал {{ formatName(d.user) }} {{ formatDateTime(d.created_at) }}
                </div>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </div>
    </div>
    <template v-if="project && selectedContractId">
      <UiNoData v-if="project[selectedContractId].length === 0" />
      <div
        v-for="p in project[selectedContractId]"
        v-else
        :key="p.id"
        class="project__programs"
        :class="{ 'element-loading': loading }"
      >
        <div class="project__program-info">
          <span>
            {{ ProgramLabel[p.program] }}
          </span>
          <v-chip v-if="p.id < 0" label density="comfortable" color="orange">
            добавлено в проекте
          </v-chip>
          <ContractVersionProgramStatus v-else :item="p.swamp!" />
          <v-btn
            v-if="p.id < 0"
            :size="30"
            icon="$plus"
            density="compact"
            class="project__remove"
            variant="plain"
            @click="removeProgram(p)"
          />
        </div>
        <ProjectGroupList
          v-if="p.groups.length"
          :items="p.groups"
          :contract-id="selectedContractId"
          @add-to-group="g => addToGroup(p, g)"
          @remove-from-group="removeFromGroup"
        />
        <div v-else class="project__no-groups">
          <UiNoData>
            не найдено групп
          </UiNoData>
        </div>
      </div>
    </template>
  </template>

  <ContractVersionDialog ref="contractVersionDialog" />
  <ProjectNameDialog ref="nameDialog" @saved="name => (projectName = name)" />
</template>

<style lang="scss">
.page-projects-editor {
  .panel-schedule {
    justify-content: space-between;
    .v-btn {
      transition: all ease-in-out 0.2s;
    }
  }
  .panel--minimized {
    .panel-schedule .v-btn {
      transform: rotate(180deg);
    }
  }

  .panel-info__empty-project {
    align-items: center;
    padding: 0 20px !important;
    $height: 50px !important;
    height: $height;
    min-height: $height;
    .panel-actions {
      position: relative;
      top: 1px;
      right: -1px;
      gap: 4px;
    }
  }

  .tabs {
    position: sticky;
    top: 64px;
    z-index: 1;
    background: white;
    .tabs-item {
      display: flex;
      align-items: center;
      gap: 8px;
      &a {
        color: black !important;
      }

      .v-icon {
        $size: 20px;
        height: $size;
        width: $size;
        font-size: 14px;
        transform: rotate(90deg);
        transition: transform ease-in-out 0.2s;
        &[aria-expanded='true'] {
          transform: rotate(-90deg);
        }
      }
    }

    .v-badge__wrapper {
      margin: 0 !important;
    }
  }
}

.project {
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

  &__program-info {
    padding: var(--padding);
    display: flex;
    align-items: center;
    gap: 20px;
    cursor: default;
    background-color: rgb(var(--v-theme-bg));
    border-bottom: 1px solid rgb(var(--v-theme-border));

    &:hover {
      .project__remove {
        opacity: 0.8;
      }
    }

    & > span:first-child {
      font-weight: bold;
    }
  }

  &__contract {
    background-color: rgb(var(--v-theme-bg));
    padding: var(--padding);
    padding-bottom: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  &__no-groups {
    height: 200px;
    position: relative;
    border-bottom: 1px solid rgb(var(--v-theme-border));
  }

  &__remove {
    transform: rotate(45deg);
    transition: opacity ease-in-out 0.2s;
    opacity: 0;
    left: -10px;
    color: rgb(var(--v-theme-gray));
    &:hover {
      opacity: 1 !important;
      color: rgb(var(--v-theme-error));
    }
  }

  &__changes-cnt {
    color: rgb(var(--v-theme-gray));
  }
}
</style>
