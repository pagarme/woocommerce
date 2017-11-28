function getScriptsConcat(folder) {
	return {
		src : [
			'<%= paths.js %>/libs/*.js',
			'<%= paths.js %>/' + folder + '/libs/*.js',
			'<%= paths.js %>/vendor/*.js',
			'<%= paths.js %>/' + folder + '/vendor/*.js',
			'<%= paths.js %>/' + folder + 'templates/*.js',
			'<%= paths.js %>/' + folder + '/app/*.js',
			'<%= paths.js %>/boot.js'
		],
		dest : '<%= paths.js %>/' + folder + '/built.js',
	};
}

module.exports = {

	options : {
		separator : ';'
	},

	admin : getScriptsConcat( 'admin' ),
	front : getScriptsConcat( 'front' )
};