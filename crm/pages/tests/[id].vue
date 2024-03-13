<script setup lang="ts">
import type { Test } from "~/utils/models"

const route = useRoute()
const answers = ref([])
const test = ref<Test>()

async function loadData() {
  const { data } = await useHttp(`tests/${route.params.id}`)
  test.value = data.value as Test
}

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <!-- <UiTopPanel>
    <v-btn> добавить тест </v-btn>
  </UiTopPanel> -->
  <div class="test" v-if="test">
    <iframe :src="test.file" />
    <div class="test__answers">
      <div v-for="(a, i) in test.answers">
        <h2>Вопрос {{ i + 1 }}</h2>
        <v-item-group selected-class="bg-primary" v-model="answers[i]">
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
