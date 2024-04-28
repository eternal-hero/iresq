module.exports = {
  purge: false,
  prefix: 'tw-',
  darkMode: false,
  theme: {
    colors: {
      white: '#fff',
      primary: '#b2111d',
      gold: '#ffd700',
      gray: {
        100: '#fafafa',
        400: '#e8e8e8'
      }
    },
    container: {
      center: true,
      padding: '1.25rem',
    },
    extend: {
      spacing: {
        72: '18rem',
        80: '20rem',
        88: '22rem',
      },
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
};
