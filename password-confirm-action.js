(function($){
	var wrap, form, user_pass, modalTabOrder,
	tababbleSelector = 'a[href], area[href], input:not([disabled]), button:not([disabled]),select:not([disabled]), textarea:not([disabled]), iframe, object, embed, *[tabindex], *[contenteditable]';

	function show() {
		wrap.removeClass('hidden')
			.attr('aria-hidden', false)
			.focus()
			.on('keydown.pca.modal', function ( e ) {
				if( e.which == 9 ){
					tabKeyDownHandler( e );
				}
			})
			.on('keyup.pca.modal', function ( e ) {
				if( e.which == 27 ){
					hide();	
				}
			});
	}
	
	function tabKeyDownHandler( e ){
		
		//Tab trapping - http://www.w3.org/TR/wai-aria-practices/#trap_focus_div

		if( !modalTabOrder ){
			//Create hierarachy of tabbable elements in the modal
			modalTabOrder = wrap.find( tababbleSelector );
		}
	
		var m = modalTabOrder.length;
        var first = modalTabOrder[0];
        var last = modalTabOrder[m-1];
        
		if( e.target === last && !e.shiftKey ){
			//If tabbing forward and we are on the last element, loop back to the first
            e.preventDefault();
            first.focus();
            return false;
		}else if( e.target === first && e.shiftKey ){
			//If tabbing(+shiftkey) backwards and we are on the first element, go to the last.
			e.preventDefault();
			last.focus();
			return false;
		}
		
	}
		
	function hide() {
		wrap.fadeOut( 200, function() {
			wrap.addClass('hidden')
				.css('display', '')
				.attr('aria-hidden', true)
				.off('keyup.pca.modal')
				.off('keydown.pca.modal');
		});
	}

	function onSubmit( ev ){
	
		if( user_pass.val() ){
			return true;
		}
			
		var new_email = form.find( '#email' ).val();
		var new_role  = form.find( '#role' ).val();
		var new_pass  = form.find( '#pass1' ).val();
		var prompt    = false;
		
		if( new_email !== pca.user.email ){
			prompt = true;
		}else if( new_role && -1 == pca.user.roles.indexOf( new_role ) ){
			prompt = true;
		}else if( new_pass ){
			prompt = true;
		}
		
		if( prompt ){
			ev.preventDefault();
			
			if( form.find('.form-invalid').length === 0 ){
				show();	
			}
			
			return false;
		}
		
		return true;

	}

	$( document ).on( 'ready', function() {
		
		wrap = $('#pca-auth-check-wrap');	
		
		$('#current-pass-modal').attr( 'name', 'current_user_pass' );
		$('#pca-fields').remove();
		
		wrap.find('.pca-auth-check-close').on( 'click', function() {
			hide();
		});
		
		form = $('#current-pass-modal').parents( 'form' );
		form.on( 'submit', onSubmit );
		user_pass = form.find( 'input[name=current_user_pass]' );
	
	});

}(jQuery));