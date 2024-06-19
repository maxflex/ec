<script setup lang="ts">
const route = useRoute()
const answers = ref([])
const test = ref<TestResource>()

async function loadData() {
  const { data } = await useHttp<TestResource>(`tests/${route.params.id}`)
  if (data.value) {
    test.value = data.value
  }
}

nextTick(loadData)
</script>

<template>
  <!-- <UiTopPanel>
    <v-btn> добавить тест </v-btn>
  </UiTopPanel> -->
  <div
    v-if="test"
    class="test"
  >
    <iframe :src="test.file as string" />
    <div class="test__questions">
      <div
        v-for="(q, i) in test.questions"
        :key="i"
      >
        <h2>Вопрос {{ i + 1 }}</h2>
        <v-item-group
          v-model="answers[i]"
          selected-class="bg-primary"
        >
          <v-item
            v-for="n in 6"
            :key="n"
          >
            <template #default="{ toggle, selectedClass }">
              <v-btn
                height="48"
                width="48"
                variant="text"
                icon
                border
                :class="selectedClass"
                @click="toggle"
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
