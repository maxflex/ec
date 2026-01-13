<script setup lang="ts">
const text = ref('')
const loading = ref(false)
const isSent = ref(false)

async function save() {
  if (!text.value) {
    return
  }
  loading.value = true
  await useHttp(`teacher-complaints`, {
    method: 'POST',
    body: {
      text: text.value,
    },
  })
  isSent.value = true
}
</script>

<template>
  <div class="teacher-complaint__wrapper">
    <div class="teacher-complaint">
      <v-fade-transition>
        <div v-if="isSent" class="teacher-complaint__sent">
          <h1>
            Жалоба отправлена
          </h1>
          <div>
            Спасибо, ваше обращение принято в работу
          </div>
          <v-btn size="x-large" color="primary" class="mt-6" :to="{ name: 'groups' }">
            понятно
          </v-btn>
        </div>
      </v-fade-transition>
      <h1>
        Оставить жалобу
      </h1>
      <div class="teacher-complaint__instructions">
        В поле ниже вы можете оставить любые жалобы. После отправки с вами свяжется сотрудник учебной части.
      </div>
      <div>
        <v-textarea
          v-model="text"
          rows="10"
          no-resize
          auto-grow
          label="Текст жалобы"
        />
      </div>
      <div style="text-align: center;">

      <v-btn size="x-large" color="primary" :loading="loading" @click="save()">
        отправить
      </v-btn>
      </div>
    </div>
  </div>

  <!-- <v-checkbox
    v-model="isSent"
    label="Жалоба отправлена"
    style="position: fixed; bottom: 0; left: 300px"
  /> -->
</template>

<style lang="scss">
.teacher-complaint {
  min-width: 700px;
  width: 700px;
  max-width: 700px;
  display: flex;
  flex-direction: column;
  gap: 30px;
  position: relative;

  &__sent {
    position: absolute !important;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 10px;
  }

  &__wrapper {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  }
}
</style>
