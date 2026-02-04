module.exports = {
  parser: '@babel/eslint-parser',
  extends: ['airbnb-base', 'prettier'],
  env: {
    browser: true,
    jquery: true,
  },
  plugins: ['header'],
  rules: {
    'arrow-body-style': 'off',
    'class-methods-use-this': 'off',
    'header/header': [
      2,
      'block',
      [
        '!',
        ' * This file is part of sensiolabs-de/admin-bundle.',
        ' *',
        ' * (c) SensioLabs Deutschland <info@sensiolabs.de>',
        ' *',
        ' * For the full copyright and license information, please view the LICENSE',
        ' * file that was distributed with this source code.',
        ' ',
      ],
      2,
    ],
    'import/no-webpack-loader-syntax': 'off',
    'lines-between-class-members': 'off',
    'no-param-reassign': ['error', { 'props': false }],
  },
};
