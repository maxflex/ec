<script setup lang="ts">
const emit = defineEmits(['verified'])

const otp = reactive({
  code: '',
  error: false,
  loading: false,
})

const otpInput = ref()
const isSent = ref(false)

function sendCode() {
  isSent.value = true
  nextTick(() => otpInput.value.focus())
  useHttp(
    `balance-verification/send-code`,
    {
      method: 'post',
    },
  )
}

async function onOtpFinish() {
  otp.error = false
  otp.loading = true
  const { data } = await useHttp<{ result: boolean }>(
    `balance-verification/check-code`,
    {
      method: 'post',
      body: {
        code: otp.code,
      },
    },
  )
  const { result } = data.value!
  if (result) {
    emit('verified')
  }
  else {
    otp.error = true
    otp.loading = false
  }
}
</script>

<template>
  <div class="balance-verification">
    <p>
      Для просмотра этой страницы<br>
      необходимо подтверждение доступа
    </p>
    <v-otp-input
      ref="otpInput"
      v-model="otp.code"
      :disabled="!isSent || otp.loading"
      :error="otp.error"
      :length="4"
      width="240"
      @finish="onOtpFinish"
    />
    <div class="mt-6">
      <v-btn
        :color="isSent ? 'gray' : 'primary'"
        width="336"
        block
        size="x-large"
        :disabled="isSent"
        @click="sendCode()"
      >
        <template v-if="isSent">
          Код отправлен в Telegram
        </template>
        <template v-else>
          Отправить код в Telegram
        </template>
      </v-btn>
    </div>
  </div>
</template>

<style lang="scss">
.balance-verification {
  flex: 1;
  align-items: center;
  justify-content: center;
  display: flex;
  flex-direction: column;
  font-size: 18px;
  gap: 20px;
  & > .v-icon {
    font-size: 80px;
    opacity: 0.5;
  }
  p {
    text-align: center;
  }
}
</style>
