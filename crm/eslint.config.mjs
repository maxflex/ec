// @ts-check
import withNuxt from './.nuxt/eslint.config.mjs'
import stylistic from '@stylistic/eslint-plugin'


export default withNuxt(
  // Your custom configs here
).prepend(
  stylistic.configs['recommended-flat']
)
