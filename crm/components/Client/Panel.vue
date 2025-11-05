<script setup lang="ts">
import type { ClientDialog, PrintSpravkaDialog } from '#components'
import type { ClientResource } from '.'
import { mdiOpenInNew } from '@mdi/js'

const { item } = defineProps<{ item: ClientResource }>()
const emit = defineEmits<{ updated: [c: ClientResource] }>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()
const printSpravkaDialog = ref<InstanceType<typeof PrintSpravkaDialog>>()
</script>

<template>
  <div class="panel-info">
    <UiAvatar :item="item" :size="120" />
    <div>
      <div>
        ученик
        <ClientRiskLabel :item="item" />
      </div>
      <div class="text-truncate">
        <div>
          {{ item.last_name }}
          {{ item.first_name }}
        </div>
        <div v-if="item.middle_name">
          {{ item.middle_name }}
          <!-- <a class="vfn-1">
            <v-icon :icon="mdiOpenInNew" :size="16" />
          </a> -->
        </div>
        <div v-if="item.phones" class="mt-5">
          <PhoneList :items="item.phones" show-icons />
        </div>
      </div>
    </div>
    <div>
      <div>представитель</div>
      <div class="text-truncate">
        <div>
          {{ item.representative.last_name }}
          {{ item.representative.first_name }}
        </div>
        <div v-if="item.representative.middle_name">
          {{ item.representative.middle_name }}
        </div>
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
