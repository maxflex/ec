<script setup lang="ts">
import type { Contract, Group } from '~/utils/models'

const route = useRoute()
const group = ref<Group>()

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as Group
}

async function removeFromGroup(c: Contract) {
  await useHttp(`groups/remove-contract`, {
    method: 'post',
    params: {
      group_id: group.value?.id,
      contract_id: c.id,
    },
  })
  loadData()
}

nextTick(loadData)
</script>

<template>
  <h3 class="page-title">
    Группа {{ route.params.id }}
  </h3>
  <div v-if="group">
    <div class="table table--actions-on-hover">
      <div
        v-for="contract in group.contracts"
        :key="contract.id"
      >
        <div style="width: 300px">
          <NuxtLink
            :to="{
              name: 'clients-id',
              params: { id: contract.client.id },
            }"
          >
            {{ formatName(contract.client) }}
          </NuxtLink>
        </div>
        <div class="text-left table-actions">
          <v-btn
            icon="$close"
            variant="plain"
            color="red"
            :size="48"
            :ripple="false"
            @click="removeFromGroup(contract)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
