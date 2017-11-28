module.exports = function(grunt) {
	require('load-grunt-tasks')(grunt);
	require('time-grunt')(grunt);

	var options = {
		data: {
			paths: {
				style: 'assets/stylesheets',
				js: 'assets/javascripts',
			}
		}
	};

	require('load-grunt-config')(grunt, options);

	grunt.registerTask('default', ['concurrent:dev']);
	grunt.registerTask('deploy', ['concurrent:dist', 'uglify']);
};
