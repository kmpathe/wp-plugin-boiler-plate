const { compileAssets, build, deploy } = require('./scripts/assets.cjs');

module.exports = (grunt) => {
    const watch = { options: { spawn: false } };

    ['admin', 'public'].forEach((context) => {
        watch[context] = {
            files: [
                'assets/' + context + '/src/scss/**/*.scss',
                'assets/' + context + '/src/scss/**/*.sass',
                'assets/' + context + '/src/js/**/*.js',
            ],
            tasks: ['assets'],
        };
    });

    watch.blocks = {
        files: [
            'blocks/src/**/*.js',
            'blocks/src/**/*.json',
            'blocks/src/**/*.scss',
            'blocks/src/**/*.sass',
            'webpack.config.js',
        ],
        tasks: ['assets'],
    };
    grunt.initConfig({ watch });
    grunt.loadNpmTasks('grunt-contrib-watch');

    const registerAsyncTask = (name, task) => {
        grunt.registerTask(name, function () {
            const done = this.async();
            task().then(() => done()).catch((error) => {
                grunt.log.error(error);
                done(false);
            });
        });
    };

    registerAsyncTask('assets', compileAssets);
    registerAsyncTask('build', build);
    registerAsyncTask('deploy', deploy);
    grunt.registerTask('dev', ['assets', 'watch']);
    grunt.registerTask('default', ['dev']);
};
