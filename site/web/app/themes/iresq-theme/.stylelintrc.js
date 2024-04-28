module.exports = {
  extends: ['stylelint-config-standard'],
  ignoreFiles: ['**/*.js', '**/*.md'],
  rules: {
    'no-empty-source': null,
    'string-quotes': 'double',
    'declaration-colon-newline-after': null,
    'no-descending-specificity': null,
    'at-rule-no-unknown': [
      true,
      {
        ignoreAtRules: [
          'extend',
          'at-root',
          'debug',
          'warn',
          'error',
          'if',
          'else',
          'for',
          'each',
          'while',
          'mixin',
          'include',
          'content',
          'return',
          'function',
          'tailwind',
          'apply',
          'responsive',
          'variants',
          'screen'
        ]
      }
    ]
  }
}
