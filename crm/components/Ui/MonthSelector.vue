<script setup lang="ts">
// Define model for two-way binding
const model = defineModel<Month>({ required: true })

// Current month (1-based index)
const currentMonth: Month = new Date().getMonth() + 1 as Month

// Months array (September to August)
const months: Month[] = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6, 7, 8]

// Filter months based on the current date
const availableMonths = months.filter((m) => {
  if (m >= 9) {
    // Include months from September to December
    return m <= currentMonth || currentMonth < 9
  }
  else {
    // Include months from January to August
    return currentMonth < 9 && m <= currentMonth
  }
})

const selectorItems = availableMonths.map(value => ({
  value,
  title: MonthLabel[value],
}))
</script>

<template>
  <v-select
    v-model="model"
    :items="selectorItems"
    label="Месяц"
    v-bind="$attrs"
  />
</template>
