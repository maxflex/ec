<script setup lang="ts">
import QrcodeVue from 'qrcode.vue'

const selected = ref(0)

const { items, indexPageData } = useIndex<BillingResource>(`billing`)

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

const selectedContract = computed(() => items.value[selected.value])

const qrValue = computed(() => {
  const currentQrData = qrData[selectedContract.value.company]
  const data = {
    Name: currentQrData.Name,
    PersonalAcc: currentQrData.PersonalAcc,
    BankName: 'АО "АЛЬФА-БАНК"',
    BIC: '044525593',
    CorrespAcc: '30101810200000000593',
    Purpose: `Платные образовательные услуги по договору №${selectedContract.value.id} от ${formatDate(selectedContract.value.version.date)} г.`,
    PayeeINN: currentQrData.PayeeINN,
    KPP: currentQrData.KPP,
    LastName: selectedContract.value.representative.last_name,
    FirstName: selectedContract.value.representative.first_name,
    MiddleName: selectedContract.value.representative.middle_name,
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
    (carry, e) => carry + e.sum * (e.is_return ? -1 : 1),
    0,
  )
}

const showQr = computed<boolean>(() => {
  if (selectedContract.value.year < currentAcademicYear()) {
    return false
  }
  return !selectedContract.value.is_closed
})

// const testValue = 'ST00012|Name=ИП Горшкова Анастасия Александровна|PersonalAcc=40802810401400004731|BankName=АО "АЛЬФА-БАНК"|BIC=044525593|CorrespAcc=30101810200000000593|Purpose=Платные образовательные услуги по договору № 14340 от 24.05.24 г.|PayeeINN=622709802712|KPP=|LastName=Мирошниченко|FirstName=Татьяна|MiddleName=Петровна'
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <div class="billing">
      <div class="filters">
        <div>
          <v-btn
            v-for="(contract, i) in items"
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
              на {{ YearLabel[contract.year] }}
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
                    {{ formatPrice(totalSum(selectedContract.payments)) || 0 }} руб.
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
                  <b v-if="totalSum(selectedContract.version.payments) - totalSum(selectedContract.payments)">
                    {{ formatPrice(totalSum(selectedContract.version.payments) - totalSum(selectedContract.payments)) }}
                    руб.
                  </b>
                  <b v-else class="text-gray">
                    0 руб.
                  </b>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-if="showQr" class="billing__qr">
          <QrcodeVue
            :value="qrValue"
            :size="300"
            level="Q"
            :image-settings="{
              width: 80,
              height: 80,
              src: '/img/logo-for-qr.svg',
              excavate: true,
            }"
          />
          <p>
            Откройте приложение банка <br />и наведите камеру на QR-код
          </p>
        </div>
      </div>
    </div>
  </UiIndexPage>
</template>

<style lang="scss">
.billing {
  &__qr {
    p {
      // color: rgb(var(--v-theme-gray));
      font-size: 14px;
      max-width: 300px;
      padding: 0 14px;
      text-align: center;
    }
  }
  &__content {
    display: flex;
    justify-content: space-between;
    padding: 20px;
    width: 100%;
    h3 {
      margin-bottom: 20px;
    }
    & > div {
      &:first-child {
        width: 650px;
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
