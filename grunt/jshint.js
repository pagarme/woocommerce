module.exports = {
	options: {
		jshintrc : true
	},

	beforeconcat : ['<%= concat.admin.src %>', '<%= concat.front.src %>']
};