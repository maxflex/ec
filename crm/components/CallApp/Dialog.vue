<script setup lang="ts">
import { callAppDialog } from '.'

const { items, loading, reloadData } = useIndex<CallListResource>('calls', {
  instantLoad: false,
  scrollContainerSelector: '.call-app-dialog .dialog-body',
})

watch(callAppDialog, isOpen => isOpen && reloadData())

// async function loadData() {
//   const { data } = await useHttp<ApiResponse<CallListResource[]>>(`calls`, {
//     params: {
//       paginate: 30,
//     },
//   })
//   if (data.value)  {
//     calls.value =
//   }
// }

const { width } = useDialog('default')
</script>

<template>
  <v-dialog v-model="callAppDialog" :width="width">
    <div class="dialog-wrapper call-app-dialog">
      <!--      <div class="dialog-header"> -->
      <!--        Call app -->
      <!--      </div> -->
      <UiLoader3 :loading="loading" />
      <div class="dialog-body pa-0">
        <CallAppCallsList :items="items" />
      </div>
    </div>
  </v-dialog>
</template>

<style scoped lang="scss">

</style>
