// @ts-check
// import stylistic from '@stylistic/eslint-plugin'
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt(
  // Your custom configs here
).overrideRules({
  '@typescript-eslint/ban-ts-comment': 'off',
  '@typescript-eslint/no-dynamic-delete': 'off',
  'vue/no-multiple-template-root': 'off',
})
