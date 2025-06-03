<script setup lang="ts">
const testEvents = ['appeared', 'connected', 'disconnected', 'summary'] as const
type TestEvent = typeof testEvents[number]

const index = ref(1)
const stage = ref<{ [key: number]: TestEvent | null }>({
  0: null,
  1: null,
})

function testEvent(e: TestEvent) {
  stage.value[index.value] = e === 'disconnected' ? null : e
  setTimeout(() => {
    useHttp(
      `mango-test/${e}`,
      {
        method: 'post',
        body: {
          index: index.value,
        },
      },
    )
  }, e === 'summary' ? 3000 : 0)
}

function getDisabled(s: TestEvent) {
  const currentStage = stage.value[index.value]
  switch (currentStage) {
    case null:
      return ['connected', 'disconnected'].includes(s)
    case 'appeared':
      return s === 'appeared'
    case 'connected':
      return ['appeared', 'connected'].includes(s)
  }
}
</script>

<template>
  <div class="mango-test">
    <div class="mango-test__buttons">
      <v-btn
        v-for="e in testEvents"
        :key="e" color="primary"
        :disabled="getDisabled(e)"
        @click="testEvent(e)"
      >
        {{ e }}
      </v-btn>
    </div>
    <v-select
      v-model="index"
      style="width: 300px" :items="[
        { value: 0, title: '+7 (916) 852-43-17' },
        { value: 1, title: '+7 (925) 272-72-10' },
      ]"
    />
  </div>
</template>

<style lang="scss">
.mango-test {
  padding: 20px;
  &__buttons {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
  }
}
</style>
