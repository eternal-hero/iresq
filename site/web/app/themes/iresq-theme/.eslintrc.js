module.exports = {
  ignorePatterns: ['dist/**/*.js', 'node_modules/*'],
  parserOptions: {
    parser: 'babel-eslint',
    ecmaVersion: 2020,
    sourceType: 'module',
  },
  env: {
    browser: true,
    node: true,
    commonjs: true,
    serviceworker: true,
    webextensions: true,
  },
  extends: ['airbnb-base', 'plugin:vue/vue3-recommended'],
  globals: {
    __static: true,
    defineProps: 'readonly',
    defineEmits: 'readonly',
    defineExpose: 'readonly',
    withDefaults: 'readonly',
  },
  plugins: ['vue'],
  overrides: [
    {
      files: ['*.vue'],
      rules: {
        indent: 'off',
      },
    },
  ],
  rules: {
    radix: 'off',
    'no-throw-literal': 'off',
    'eol-last': 'off',
    'max-len': 'off',
    'vue/no-v-html': 'off',
    'no-use-before-define': 'off',
    'vue/component-name-in-template-casing': ['error', 'kebab-case'],
    'vue/html-closing-bracket-newline': [
      'error',
      {
        singleline: 'never',
        multiline: 'always',
      },
    ],
    'vue/html-closing-bracket-spacing': 'error',
    'vue/script-indent': [
      'error',
      2,
      {
        baseIndent: 1,
        switchCase: 1,
        ignores: [],
      },
    ],
    'vue/max-attributes-per-line': [
      2,
      {
        singleline: 20,
        multiline: {
          max: 1,
          allowFirstLine: true,
        },
      },
    ],
    'global-require': 0,
    'import/no-unresolved': 0,
    'no-param-reassign': 0,
    'no-shadow': 0,
    'import/extensions': 0,
    'import/newline-after-import': 0,
    'no-multi-assign': 0,
    'import/no-extraneous-dependencies': 0,
    'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0,
    'vue/order-in-components': [
      'error',
      {
        order: ['el', 'name', 'parent', 'functional', ['delimiters', 'comments'], ['components', 'directives', 'filters'], 'extends', 'mixins', 'inheritAttrs', 'model', ['props', 'propsData'], 'data', 'computed', 'watch', 'LIFECYCLE_HOOKS', 'methods', ['template', 'render'], 'renderError'],
      },
    ],
  },
};
