<script setup lang="ts">
const { item } = defineProps<{
  item: ClientRequest
}>()
</script>

<template>
  <div class="request">
    <div class="request-title">
      <RequestStatus :status="item.status" />
      Заявка {{ item.id }}
    </div>
  </div>
</template>

<style lang="scss">
.request {
  background: #fff;
  border: 1px solid #e0e0e0;
  border-left: none;
  border-right: none;
  display: flex;
  justify-content: space-between;
  margin-bottom: 24px;
  padding: 20px;
}
</style>
