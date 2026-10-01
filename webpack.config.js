const path = require('node:path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
    entry: {
        index: path.resolve(__dirname, 'blocks/src/index.js'),
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve(__dirname, 'blocks/build'),
    },
};
