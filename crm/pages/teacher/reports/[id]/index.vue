<script lang="ts" setup>
const { user } = useAuthStore()

if (!user?.is_head_teacher) {
  showError({
    statusCode: 404,
    statusMessage: 'Not found',
  })
}
</script>

<template>
  <ReportView />
</template>
