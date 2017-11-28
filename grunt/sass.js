module.exports = {
	dist: {
		options: {
			style: 'compressed'
		},
		files: {
			'<%= paths.style %>/admin/style.css': '<%= paths.style %>/admin/style.scss',
			'<%= paths.style %>/front/style.css': '<%= paths.style %>/front/style.scss'
		}
	},
	dev: {
		options: {
			style: 'expanded',
		},
		files: {
			'<%= paths.style %>/admin/style.css': '<%= paths.style %>/admin/style.scss',
			'<%= paths.style %>/front/style.css': '<%= paths.style %>/front/style.scss'
		}
	},
};