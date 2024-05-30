<script setup lang="ts">
const items = ref([])
const paginator = usePaginator()
// const isLastPage = false

const loadData = async function () {
  console.log('loading data')
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<[]>>('client-payments', {
    params: { page: paginator.page },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value
      = paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page === meta.last_page
  }
}

async function onIntersect({
  done,
}: {
  done: (status: InfiniteScrollStatus) => void
}) {
  done('loading')
  await loadData()
  done('ok')
}

nextTick(loadData)
</script>

<template>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    class="table"
    side="end"
    @load="onIntersect"
  >
    <div
      v-for="payment in items"
      :key="payment.id"
    >
      <div style="width: 100px">
        <span
          v-if="payment.is_return"
          class="text-red"
        >
          возврат
        </span>
        <span v-else>
          платеж
        </span>
      </div>
      <div style="width: 120px">
        {{ formatDate(payment.date) }}
      </div>
      <div style="width: 120px">
        {{ ClientPaymentMethodLabel[payment.method] }}
      </div>
      <div style="width: 180px">
        {{ YearLabel[payment.year] }}
      </div>
      <div style="width: 200px">
        {{ formatPrice(payment.sum) }} руб.
      </div>
      <div>
        <span
          v-if="payment.is_confirmed"
          class="text-success"
        >
          подтверждён
        </span>
        <span
          v-else
          class="text-error"
        >
          не подтверждён
        </span>
      </div>
      <div class="text-right text-gray">
        {{ formatDateTime(payment.created_at) }}
      </div>
    </div>
  </v-infinite-scroll>
</template>
