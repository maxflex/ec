<script setup lang="ts">
const { items } = defineProps<{
  items: GroupActResource[]
}>()
const emit = defineEmits<{
  edit: [item: GroupActResource]
}>()
</script>

<template>
  <div class="table">
    <div v-for="item in items" :id="`group-act-${item.id}`" :key="item.id">
      <div style="width: 150px">
        Акт №{{ item.id }}
      </div>
      <div style="width: 180px">
        <UiPerson :item="item.teacher!" />
      </div>
      <div style="width: 150px">
        {{ plural(item.lessons!, ['занятие', 'занятия', 'занятий']) }}
      </div>
      <div style="width: 150px">
        {{ formatPrice(item.sum!) }} руб.
      </div>
      <div>
        {{ formatDate(item.date) }}
      </div>
      <UiTableActions>
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', item)"
        />
</UiTableActions>
    </div>
  </div>
</template>
