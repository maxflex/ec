<script setup lang="ts">
import type { PassDialog } from '#components'

const route = useRoute()
const id = Number.parseInt(route.params.id as string)
const passes = ref<PassResource[]>([])
const loading = ref(true)
const passDialog = ref<InstanceType<typeof PassDialog>>()

// const request = ref<RequestResource>()
// async function loadRequest() {
//   const { data } = await useHttp<RequestResource>(`requests/${id}`)
//   if (data.value) {
//     request.value = data.value
//   }
// }

async function loadPasses() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<PassResource[]>>(`passes`, {
    params: {
      request_id: id,
    },
  })
  if (data.value) {
    passes.value = data.value.data
  }
  loading.value = false
}

function onPassCreated(pass: PassResource) {
  passes.value.push(pass)
  itemUpdated('pass', pass.id)
}

nextTick(loadPasses)
</script>

<template>
  <div class="show">
    <div class="show-title">
      <h2>
        Пропуски к заявке {{ id }}
      </h2>
    </div>
    <v-fade-transition>
      <div v-if="!loading" class="show__content">
        <div>
          <div />
          <PassList :items="passes">
            <tr class="hoverable cursor-pointer" @click="passDialog?.create(id)">
              <td colspan="10">
                <UiIconLink>
                  Добавить пропуск
                </UiIconLink>
              </td>
            </tr>
          </PassList>
        </div>
      </div>
    </v-fade-transition>
    <PassDialog
      ref="passDialog"
      @created="onPassCreated"
    />
  </div>
</template>
