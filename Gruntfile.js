module.exports = function (grunt) {
    grunt.initConfig({
        eslint: {
            target: ['amd/src/**/*.js']
        },
        uglify: {
            build: {
                files: [{
                    expand: true,
                    cwd: 'amd/src/',
                    src: ['*.js'],
                    dest: 'amd/build/',
                    ext: '.min.js',
                    extDot: 'last'
                }],
                options: {
                    sourceMap: true,
                    sourceMapName: function(dest) {
                        return dest + '.map';
                    }
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-eslint');
    grunt.loadNpmTasks('grunt-contrib-uglify');

    grunt.registerTask('default', ['eslint', 'uglify']);
    grunt.registerTask('amd', ['eslint', 'uglify']);
};
