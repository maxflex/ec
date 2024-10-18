<script setup lang="ts">
const model = defineModel<PhoneListResource[]>({ default: () => [] })
const phoneMask = { mask: '+7 (###) ###-##-##' }

function addPhone() {
  model.value.push({
    id: newId(),
    number: '',
    comment: '',
    telegram_id: null,
    entity_type: EntityTypeValue.request,
  })
}

function removePhone(p: PhoneListResource) {
  model.value.splice(
    model.value.findIndex(({ id }) => id === p.id),
    1,
  )
}
</script>

<template>
  <div
    v-for="p in model"
    :key="p.id"
    class="phone-editor double-input"
  >
    <div>
      <v-text-field
        v-model="p.number"
        v-maska:[phoneMask]
        label="Телефон"
      />
      <v-icon
        icon="$close"
        @click="removePhone(p)"
      />
    </div>
    <v-text-field
      v-model="p.comment"
      label="Комментарий"
    />
  </div>
  <div style="position: relative; top: -10px">
    <a
      class="cursor-pointer"
      @click="addPhone()"
    >
      добавить номер
    </a>
  </div>
</template>

<style lang="scss">
.phone-editor {
  & > div {
    &:first-child {
      position: relative;
    }
  }
  .v-icon {
    position: absolute;
    right: 10px;
    top: 19px;
    opacity: 0.25;
    z-index: 1;
    // transition: all ease-in-out 0.1s;
    cursor: pointer;
    font-size: 20px !important;
    &:hover {
      opacity: 1;
      color: rgb(var(--v-theme-error));
    }
  }
}
</style>
