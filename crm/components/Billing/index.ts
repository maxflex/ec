interface BankAccount {

  /**
   * Наименование организации
   */
  Name: string
  /**
   * Банк
   */
  BankName: string
  /**
   * БИК
   */
  BIC: string
  /**
   * Корреспондентский счёт (к/с)
   */
  CorrespAcc: string
  /**
   * Номер счёта (р/с)
   */
  PersonalAcc: string
  /**
   * ИНН банка
   */
  PayeeINN: string
  /**
   * КПП
   */
  KPP: string
}

export const bankAccounts: Record<Company, BankAccount> = {
  ip: {
    Name: 'ИП Горшкова Анастасия Александровна',
    BankName: 'АО "АЛЬФА-БАНК"',
    BIC: '044525593',
    CorrespAcc: '30101810200000000593',
    PersonalAcc: '40802810401400004731',
    PayeeINN: '622709802712',
    KPP: '',
  },
  ooo: {
    Name: 'ООО "ЕГЭ-Центр"',
    BankName: 'АО "АЛЬФА-БАНК"',
    BIC: '044525593',
    CorrespAcc: '30101810200000000593',
    PersonalAcc: '40702810801960000153',
    PayeeINN: '9701038111',
    KPP: '770101001',
  },
  ano: {
    Name: 'АНО ДО "Школа будущего"',
    BankName: 'ПАО Сбербанк',
    BIC: '044525225',
    CorrespAcc: '30101810400000000225',
    PersonalAcc: '40703810238720001128',
    PayeeINN: '9703186517',
    KPP: '770301001',
  },
}
