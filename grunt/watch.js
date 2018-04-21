module.exports =  {
	styles : {
		files : ['<%= paths.style %>/**/**/*.scss'],
		tasks : ['sass:dev']
	},
	scripts : {
		files : ['<%= concat.admin.src %>', '<%= concat.front.src %>'],
		tasks : ['concat:admin', 'concat:front']
	}
};