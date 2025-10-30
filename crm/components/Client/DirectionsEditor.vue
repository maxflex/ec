<script setup lang="ts">
import type { ClientDirection } from '.'

const model = defineModel<ClientDirection[]>({ required: true })

function addDirection() {
  model.value.push({
    id: newId(),
    year: currentAcademicYear(),
    // @ts-expect-error
    direction: undefined,
  })
  smoothScroll('dialog', 'bottom')
}

function removeDirection({ id }: ClientDirection) {
  const index = model.value.findIndex(e => e.id === id)
  model.value.splice(index, 1)
}
</script>

<template>
  <div v-for="item in model" :key="item.id" class="double-input">
    <div>
      <UiYearSelector v-model="item.year" />
      <div class="input-actions">
        <span class="phone-editor__remove" @click="removeDirection(item)">
          удалить
        </span>
      </div>
    </div>

    <UiClearableSelect v-model="item.direction" label="Направление" :items="selectItems(DirectionLabel)" />
  </div>
  <div class="double-input">
    <div>
      <v-btn color="primary" block @click="addDirection()">
        добавить направление
      </v-btn>
    </div>
    <v-spacer />
  </div>
</template>
