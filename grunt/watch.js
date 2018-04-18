module.exports =  {
	styles : {
		files : ['<%= paths.style %>/**/**/*.scss'],
		tasks : ['sass:dev']
	},
	scripts : {
		files : ['<%= concat.admin.src %>', '<%= concat.front.src %>'],
		tasks : ['jshint', 'concat:admin', 'concat:front']
	}
};