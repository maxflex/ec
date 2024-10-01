<script setup lang="ts">
const { groupIds } = defineProps<{
  groupIds: number[]
}>()
const model = defineModel<PeopleSelectorFilters>({ required: true })

const modeLabel = {
  clients: 'клиенты',
  teachers: 'преподаватели',
} as const

const programPresets: Array<{
  label: string
  programs: Program[]
}> = [
  { label: 'курсы', programs: ['math9', 'phys9', 'chem9', 'bio9', 'inf9', 'rus9', 'lit9', 'soc9', 'his9', 'eng9', 'geo9', 'essay9', 'math10', 'phys10', 'chem10', 'bio10', 'inf10', 'rus10', 'lit10', 'soc10', 'his10', 'eng10', 'math11', 'phys11', 'chem11', 'bio11', 'inf11', 'rus11', 'lit11', 'soc11', 'his11', 'eng11', 'geo11', 'essay11'] },
  { label: 'курсы 9 класс', programs: ['math9', 'phys9', 'chem9', 'bio9', 'inf9', 'rus9', 'lit9', 'soc9', 'his9', 'eng9', 'geo9', 'essay9'] },
  { label: 'курсы 10 класс', programs: ['bio10', 'math10', 'phys10', 'chem10', 'inf10', 'rus10', 'lit10', 'soc10', 'his10', 'eng10'] },
  { label: 'курсы 11 класс', programs: ['math11', 'phys11', 'chem11', 'bio11', 'inf11', 'rus11', 'lit11', 'soc11', 'his11', 'eng11', 'geo11', 'essay11'] },
  { label: 'старшая школа', programs: ['mathSchool8', 'physSchool8', 'chemSchool8', 'bioSchool8', 'infSchool8', 'rusSchool8', 'litSchool8', 'socSchool8', 'hisSchool8', 'engSchool8', 'geoSchool8', 'mathSchool9', 'physSchool9', 'chemSchool9', 'bioSchool9', 'infSchool9', 'rusSchool9', 'litSchool9', 'socSchool9', 'hisSchool9', 'engSchool9', 'geoSchool9', 'mathSchool10', 'physSchool10', 'chemSchool10', 'bioSchool10', 'infSchool10', 'rusSchool10', 'litSchool10', 'socSchool10', 'hisSchool10', 'engSchool10', 'mathSchool11', 'physSchool11', 'chemSchool11', 'bioSchool11', 'infSchool11', 'rusSchool11', 'litSchool11', 'socSchool11', 'hisSchool11', 'engSchool11'] },
  { label: 'школа 8 класс', programs: ['mathSchool8', 'physSchool8', 'chemSchool8', 'bioSchool8', 'infSchool8', 'rusSchool8', 'litSchool8', 'socSchool8', 'hisSchool8', 'engSchool8', 'geoSchool8'] },
  { label: 'школа 9 класс', programs: ['mathSchool9', 'physSchool9', 'chemSchool9', 'bioSchool9', 'infSchool9', 'rusSchool9', 'litSchool9', 'socSchool9', 'hisSchool9', 'engSchool9', 'geoSchool9'] },
  { label: 'школа 10 класс', programs: ['mathSchool10', 'physSchool10', 'chemSchool10', 'bioSchool10', 'infSchool10', 'rusSchool10', 'litSchool10', 'socSchool10', 'hisSchool10', 'engSchool10'] },
  { label: 'школа 11 класс', programs: ['mathSchool11', 'physSchool11', 'chemSchool11', 'bioSchool11', 'infSchool11', 'rusSchool11', 'litSchool11', 'socSchool11', 'hisSchool11', 'engSchool11'] },
  { label: 'экстернат', programs: ['mathExt', 'physExt', 'chemExt', 'bioExt', 'infExt', 'rusExt', 'litExt', 'socExt', 'hisExt', 'engExt', 'geoExt'] },
]

const statusPresets: Array<{
  label: string
  statuses: SwampFilterStatus[]
}> = [
  { label: 'активные', statuses: ['toFulfil', 'inProcess'] },
  { label: 'архив', statuses: ['exceedNoGroup', 'completeNoGroup', 'exceedInGroup', 'completeInGroup'] },
]
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <v-select
    v-model="model.mode"
    label="Режим"
    :items="selectItems(modeLabel)"
    density="comfortable"
  />
  <template v-if="model.mode === 'clients'">
    <UiMultipleSelect
      v-model="model.programs"
      label="Программа"
      :items="selectItems(ProgramLabel)"
      density="comfortable"
    >
      <template #presets>
        <a
          v-for="(preset, i) in programPresets" :key="i"
          @click="model.programs = preset.programs"
        >
          {{ preset.label }}
        </a>
      </template>
    </UiMultipleSelect>
    <UiMultipleSelect
      v-model="model.statuses"
      label="Статус"
      :items="selectItems(SwampFilterStatusLabel)"
      density="comfortable"
    >
      <template #presets>
        <a
          v-for="(preset, i) in statusPresets" :key="i"
          @click="model.statuses = preset.statuses"
        >
          {{ preset.label }}
        </a>
      </template>
    </UiMultipleSelect>
    <UiClearableSelect
      v-model="model.group_id"
      :items="groupIds.map(value => ({
        value,
        title: `Группа ${value}`,
      }))"
      label="Группа"
      density="comfortable"
    />
  </template>
  <template v-else>
    <UiClearableSelect
      v-model="model.status"
      label="Статус"
      :items="selectItems(TeacherStatusLabel)"
      density="comfortable"
    />
    <UiMultipleSelect
      v-model="model.subjects"
      label="Предметы"
      :items="selectItems(SubjectLabel)"
      density="comfortable"
    />
    <v-spacer />
  </template>
</template>
