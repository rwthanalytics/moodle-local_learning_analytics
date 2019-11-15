/*eslint linebreak-style: ["error", "windows"]*/

// eslint-disable-next-line no-undef
module.exports = function(grunt) {

	// Configuration
	
	grunt.initConfig({
		watch: {
			files: 'amd/src/*.js',
			tasks: ['concat','uglify']
		},
		concat: {
			js: {
				src: ['amd/src/*.js'],
				dest: 'amd/build/scripts.js'
			}
		},
		uglify: {
			build: {
				files: [{
					src: 'amd/build/scripts.js',
					dest: 'amd/build/scripts.js',
				}]
			}
		}
		
		
	});
	
	// Load plugins
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	
	// Register tasks
	//grunt.registerTask('concat', ['concat:js']);
	//grunt.registerTask('uglify', ['uglify']);
	grunt.registerTask('all',['default','watch']);
	grunt.registerTask('default', ['concat','uglify']);
};