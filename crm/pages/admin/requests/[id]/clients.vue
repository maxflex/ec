<script setup lang="ts">
import type { ClientDialog } from '#components'

const clientDialog = ref<InstanceType<typeof ClientDialog>>()

const router = useRouter()
const route = useRoute()
const id = Number.parseInt(route.params.id as string)
const clients = ref<ClientListResource[]>([])
const currentClientId = ref<number | null>(null)
const loading = ref(true)

async function loadClients() {
  const { data } = await useHttp<ApiResponse<ClientListResource[]>>(`clients`, {
    params: {
      request_id: id,
    },
  })
  if (data.value) {
    clients.value = data.value.data
  }
}

async function loadRequest() {
  const { data } = await useHttp<RequestResource>(`requests/${id}`)
  if (data.value) {
    const { client } = data.value
    if (client) {
      currentClientId.value = client.id
      const index = clients.value.findIndex(c => c.id === client.id)
      if (index === -1) {
        clients.value.push(client)
      }
    }
  }
}

async function loadData() {
  loading.value = true
  await loadClients()
  await loadRequest()
  loading.value = false
}

async function select(client: ClientListResource) {
  const isRemove = client.id === currentClientId.value
  const message = isRemove
    ? `Отвязать клиента ${formatName(client)} от заявки ${id}?`
    : `Присвоить клиента ${formatName(client)} к заявке ${id}?`

  if (!confirm(message)) {
    return
  }

  await useHttp(`requests/${id}`, {
    method: 'put',
    body: {
      client_id: isRemove ? null : client.id,
    },
  })

  await router.push({ name: 'clients-id', params: { id: client.id } })
}

function onClientCreated(client: ClientListResource) {
  router.push({ name: 'clients-id', params: { id: client.id } })
}

nextTick(loadData)
</script>

<template>
  <div class="show">
    <div class="show-title">
      <h2>
        Выберите клиента к заявке {{ id }}
      </h2>
    </div>
    <v-fade-transition>
      <div v-if="!loading" class="show__content">
        <div>
          <div />
          <v-table hover>
            <tbody>
              <tr v-for="client in clients" :key="client.id" class="cursor-pointer" @click="select(client)">
                <td class="pl-5" width="300">
                  <UiPerson :item="client" />
                </td>
                <td>
                  <span v-if="client.id === currentClientId" class="text-gray">
                    текущий клиент
                  </span>
                </td>
              </tr>
              <tr class="cursor-pointer" @click="clientDialog?.create(id)">
                <td class="pl-5" colspan="10">
                  <UiIconLink>
                    Новый клиент
                  </UiIconLink>
                </td>
              </tr>
            </tbody>
          </v-table>
        </div>
      </div>
    </v-fade-transition>
  </div>
  <ClientDialog ref="clientDialog" @created="onClientCreated" />
</template>
