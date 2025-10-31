<script setup lang="ts">
import type { ClientDialog, PrintSpravkaDialog } from '#components'
import type { ClientResource } from '.'

const { item } = defineProps<{ item: ClientResource }>()
const emit = defineEmits<{ updated: [c: ClientResource] }>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()
const printSpravkaDialog = ref<InstanceType<typeof PrintSpravkaDialog>>()
</script>

<template>
  <div class="panel-info">
    <RouterLink :to="{ name: 'clients-id', params: { id: item.id } }">
      <UiAvatar :item="item" :size="120" />
    </RouterLink>
    <div>
      <div>
        ученик
        <ClientRiskLabel :item="item" />
      </div>
      <div class="text-truncate">
        {{ formatName(item) }}
        <div v-if="item.phones" class="mt-5">
          <PhoneList :items="item.phones" show-icons />
        </div>
      </div>
    </div>
    <div>
      <div>представитель</div>
      <div class="text-truncate">
        {{ formatName(item.representative) }}
        <div v-if="item.representative.phones" class="mt-5">
          <PhoneList :items="item.representative.phones" show-icons />
        </div>
      </div>
    </div>
    <div>
      <div>направления</div>
      <div>
        <ClientDirections :items="item.directions" />
      </div>
    </div>
    <div>
      <div>куратор</div>
      <UiPerson v-if="item.head_teacher" :item="item.head_teacher" />
      <div v-else>
        не установлено
      </div>
    </div>
    <div class="panel-actions">
      <CommentBtn
        :entity-id="item.id"
        :entity-type="EntityTypeValue.client"
        :count="item.comments_count"
      />
      <v-btn
        icon="$print"
        :size="48"
        variant="plain"
        @click="printSpravkaDialog?.open(item.id)"
      />
      <v-btn
        icon="$edit"
        :size="48"
        variant="plain"
        @click="clientDialog?.edit(item.id)"
      />
    </div>
  </div>
  <PrintSpravkaDialog ref="printSpravkaDialog" />
  <ClientDialog ref="clientDialog" @updated="c => emit('updated', c)" />
</template>
