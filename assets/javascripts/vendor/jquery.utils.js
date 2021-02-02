;(function($) {

	$.fn.isEmptyValue = function() {
		return !( $.trim( this.val() ) );
	};

})( jQuery );