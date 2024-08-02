<script setup lang="ts">
const { items } = defineProps<{ items: SwampListResource[] }>()
</script>

<template>
  <div class="table">
    <div v-for="swamp in items" :key="swamp.id">
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: swamp.client.id } }">
          {{ formatName(swamp.client) }}
        </NuxtLink>
      </div>
      <div style="width: 200px">
        договор №{{ swamp.contract_id }}
      </div>
      <div style="width: 200px" :class="{ 'text-error': swamp.is_closed }">
        {{ ProgramShortLabel[swamp.program] }}
      </div>
      <div style="width: 200px">
        <NuxtLink
          v-if="swamp.group_id"
          :to="{ name: 'groups-id', params: { id: swamp.group_id } }"
        >
          Группа {{ swamp.group_id }}
        </NuxtLink>
      </div>
      <div>
        <v-chip v-if="!swamp.group_id && !swamp.is_closed" color="success">
          к исполнению
        </v-chip>
        <v-chip v-else-if="swamp.group_id && swamp.is_closed" color="error">
          в группе с закрытым договором
        </v-chip>
        <v-chip v-else-if="swamp.group_id && swamp.cvp_id === null" color="error">
          в группе без договора
        </v-chip>
      </div>
    </div>
  </div>
</template>
