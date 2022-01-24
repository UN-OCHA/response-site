import resolve from 'rollup-plugin-node-resolve';

export default {
  input: ['src/ocha-assessments-map.js'],
  output: {
    file: 'build/ocha-assessments-map.js',
    format: 'es',
    sourcemap: false
  },
  plugins: [
  resolve()
  ]
};
