import resolve from 'rollup-plugin-node-resolve';

export default {
  input: ['src/ocha-assessments-list.js'],
  output: {
    file: 'build/ocha-assessments-list.js',
    format: 'es',
    sourcemap: false
  },
  plugins: [
    resolve()
  ]
};
