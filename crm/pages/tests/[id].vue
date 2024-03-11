<script setup lang="ts">
const answers = ref([])
</script>

<template>
  <!-- <UiTopPanel>
    <v-btn> добавить тест </v-btn>
  </UiTopPanel> -->
  <div class="test">
    <iframe src="https://research.google.com/pubs/archive/44678.pdf" />
    <div class="test__answers">
      <div v-for="i in 10">
        <h2>Вопрос {{ i }}</h2>
        <v-item-group selected-class="bg-secondary" v-model="answers[i - 1]">
          <v-item v-for="n in 6" :key="n">
            <template v-slot:default="{ toggle, selectedClass }">
              <v-btn
                height="48"
                width="48"
                variant="text"
                icon
                border
                @click="toggle"
                :class="selectedClass"
              >
                {{ n }}
              </v-btn>
            </template>
          </v-item>
        </v-item-group>
        <!-- <v-radio-group>
          <v-radio label="письменная речь" value="one"></v-radio>
          <v-radio label="записная речь" value="two"></v-radio>
          <v-radio label="устная речь" value="three"></v-radio>
        </v-radio-group> -->
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.test {
  height: 100vh;
  display: flex;
  overflow: hidden;
  iframe {
    width: 67%;
    border: 0;
    border-right: 1px solid #e0e0e0;
  }
  &__answers {
    flex: 1;
    padding: 20px;
    overflow: scroll;
    h2 {
      margin-bottom: 16px;
    }
    .v-radio-group {
      left: -9px;
      position: relative;
    }
    .v-item-group {
      gap: 10px;
      display: flex;
      // justify-content: center;
    }
    & > div {
      margin-bottom: 50px;
    }
  }
}
</style>
