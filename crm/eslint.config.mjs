import antfu from '@antfu/eslint-config'

export default antfu({
  formatters: true,
}).overrideRules({
  'no-console': 'off',
  'no-alert': 'off',
  'no-case-declarations': 'off',
  'ts/ban-ts-comment': 'off',
  'eslint-comments/no-unlimited-disable': 'off',
  // '@typescript-eslint/ban-ts-comment': 'off',
  // '@typescript-eslint/no-dynamic-delete': 'off',
  // 'vue/no-multiple-template-root': 'off',
})
