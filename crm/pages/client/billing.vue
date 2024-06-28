<script setup lang="ts">
import QRCodeVue3 from 'qrcode-vue3'

const contracts = ref<BillingResource[]>()
const selected = ref(0)
const qrSize = 600

async function loadData() {
  const { data } = await useHttp<BillingResource[]>(`billing`)
  if (data.value) {
    contracts.value = data.value
  }
}

const qrData: { [key in Company]: any } = {
  ip: {
    Name: 'ИП Горшкова Анастасия Александровна',
    PersonalAcc: '40802810401400004731',
    PayeeINN: '622709802712',
    KPP: '',
  },
  ooo: {
    Name: 'ООО "ЕГЭ-Центр"',
    PersonalAcc: '40702810801960000153',
    PayeeINN: '9701038111',
    KPP: '770101001',
  },
}

const selectedContract = computed(() => contracts.value![selected.value])

const qrValue = computed(() => {
  const currentQrData = qrData[selectedContract.value.company]
  const data = {
    Name: currentQrData.Name,
    PersonalAcc: currentQrData.PersonalAcc,
    BankName: 'АО "АЛЬФА-БАНК"',
    BIC: '044525593',
    CorrespAcc: '30101810200000000593',
    Purpose: `Платные образовательные услуги по договору №${selectedContract.value.id} 
            от ${formatDate(selectedContract.value.version.date)} г.`,
    PayeeINN: currentQrData.PayeeINN,
    KPP: currentQrData.KPP,
    LastName: selectedContract.value.parent.last_name,
    FirstName: selectedContract.value.parent.first_name,
    MiddleName: selectedContract.value.parent.middle_name,
  }
  return [
    'ST00012',
  ].concat(
    Object.entries(data).map(
      ([key, value]) => `${key}=${value}`,
    ),
  ).join('|')
})

function totalSum(payments: Array<{ sum: number, is_return?: boolean }>) {
  return payments.reduce(
    (carry, e) => carry + e.sum * (e.is_return ? -1 : 1)
    , 0,
  )
}

// const testValue = 'ST00012|Name=ИП Горшкова Анастасия Александровна|PersonalAcc=40802810401400004731|BankName=АО "АЛЬФА-БАНК"|BIC=044525593|CorrespAcc=30101810200000000593|Purpose=Платные образовательные услуги по договору № 14340 от 24.05.24 г.|PayeeINN=622709802712|KPP=|LastName=Мирошниченко|FirstName=Татьяна|MiddleName=Петровна'

nextTick(loadData)
</script>

<template>
  <UiLoaderr v-if="contracts === undefined" />
  <div v-else class="billing">
    <div class="filters">
      <div>
        <v-btn
          v-for="(contract, i) in contracts"
          :key="contract.id"
          class="tab-btn"
          :class="{ 'tab-btn--active': selected === i }"
          variant="plain"
          :ripple="false"
          @click="selected = i"
        >
          <div>
            Договор №{{ contract.id }}
          </div>
          <div>
            на {{ formatYear(contract.year) }}
          </div>
        </v-btn>
      </div>
    </div>
    <div class="billing__content">
      <div>
        <table>
          <tbody v-if="selectedContract.version.payments.length">
            <tr v-for="(p, i) in selectedContract.version.payments" :key="p.id">
              <td width="200">
                <b v-if="i === 0">График платежей</b>
              </td>
              <td width="150">
                {{ formatDate(p.date) }}
              </td>
              <td>{{ formatPrice(p.sum) }} руб.</td>
            </tr>
            <tr>
              <td colspan="2" />
              <td>
                <b>
                  {{ formatPrice(totalSum(selectedContract.version.payments)) }} руб.
                </b>
              </td>
            </tr>
            <tr>
              <td colspan="100" class="invisible">
                x
              </td>
            </tr>
          </tbody>
          <tbody v-if="selectedContract.payments.length">
            <tr v-for="(p, i) in selectedContract.payments" :key="p.id">
              <td width="200">
                <b v-if="i === 0">Оплачено</b>
              </td>
              <td width="150">
                {{ formatDate(p.date) }}
              </td>
              <td>
                <span v-if="p.is_return" class="text-red">
                  {{ formatPrice(p.sum) }} руб. (возврат)
                </span>
                <span v-else>
                  {{ formatPrice(p.sum) }} руб.
                </span>
              </td>
            </tr>
            <tr>
              <td colspan="2" />
              <td>
                <b>
                  {{ formatPrice(totalSum(selectedContract.payments)) }} руб.
                </b>
              </td>
            </tr>
            <tr>
              <td colspan="100" class="invisible">
                x
              </td>
            </tr>
          </tbody>
          <tbody>
            <tr class="billing__total">
              <td colspan="2">
                <b>
                  Итого остаток по договору
                </b>
              </td>
              <td>
                <b>
                  {{ formatPrice(totalSum(selectedContract.version.payments) - totalSum(selectedContract.payments)) }}
                  руб.
                </b>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <div>
        <QRCodeVue3
          :width="qrSize"
          :height="qrSize"
          image="/img/qr-billing.png"
          imgclass="billing__qr"
          :qr-options="{
            typeNumber: '0',
            mode: 'Byte',
            errorCorrectionLevel: 'Q',
          }"
          :dots-options="{
            type: 'extra-rounded',
            color: '#6a1a4c',
            gradient: {
              type: 'linear',
              rotation: 0.7853981633974483,
              colorStops: [
                { offset: 0, color: '#ffc423' },
                { offset: 1, color: '#916f5d' },
              ],
            },
          }"
          :background-options="{ color: '#ffffff' }"
          :corners-square-options="{
            type: 'extra-rounded',
            color: '#000000',
          }"
          :corners-dot-options="{ type: undefined, color: '#000000' }"
          :value="qrValue"
        />
        <p>
          Откройте приложение вашего банка. Выберите в меню "Оплата по QR-коду".
          Наведите на изображение. Все реквизиты будут подставлены автоматически.
        </p>
      </div>
    <!-- <v-textarea :value="qrValue" /> -->
    </div>
  </div>
</template>

<style lang="scss">
.billing {
  &__qr {
    --size: 300px;
    width: var(--size);
    height: var(--size);
  }
  &__content {
    display: flex;
    padding: 20px;
    width: 100%;
    h3 {
      margin-bottom: 20px;
    }
    & > div {
      &:first-child {
        width: 650px;
      }
      &:last-child {
        // text-align: center;
        p {
          color: rgb(var(--v-theme-gray));
          font-size: 14px;
          max-width: 300px;
          padding: 0 14px;
        }
      }
      table {
        margin-bottom: 50px;
        border-collapse: collapse;
      }
    }
  }
  &__total {
    td {
      border-top: 1px solid #e0e0e0;
      padding-top: 28px;
      font-size: 20px;
    }
  }
}
</style>
