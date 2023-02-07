(function ($) {
	jQuery(document).on('ready', function() {
		var funcs = {},
			readyFuncs = {},
			resizeEvent = window.document.createEvent('UIEvents'),
			$body = $('body');

		funcs.executeFuncs = function(obj) {
			for (var func in obj) {
				obj[func]();
			}
		}

		funcs.triggerResize = function() {
			resizeEvent.initUIEvent('resize', true, false, window, 0);
			$(window).resize()[0].dispatchEvent(resizeEvent);
		}

		funcs.resizeWindowOnTabClick = function() {
			funcs.triggerResize();
			$('.wpb_tabs_nav a').on('click', function() {
				setTimeout(function(){
					funcs.triggerResize();
				},750);
			});
		}
		readyFuncs.resizeWindowOnTabClick = funcs.resizeWindowOnTabClick;

		funcs.hideInputGroup = function() {
			$('.hideInputGroup').first().each(function() {
				var $tar = $(this).closest('.rmrow');
				$tar.addClass('d-none');
			});
		}
		readyFuncs.hideInputGroup = funcs.hideInputGroup;

		funcs.splitTopicsAndRegistration = function() {
			$('.splitTopicsAndRegistration:visible').first().each(function() {
				var $parent = $(this).closest('.rmrow'),
					$parentSiblings = $parent.siblings(),
					$topics = $parent.wrap('<div class="rmrow-wrapper rmrow-wrapper-topics">').parent().detach();
				$parentSiblings.wrapAll('<div class="rmrow-wrapper rmrow-wrapper-register">');
				$parentSiblings.parent().before($topics);
				funcs.triggerResize();
			});
		}
		readyFuncs.splitTopicsAndRegistration = funcs.splitTopicsAndRegistration;

		funcs.shrinkOnMobile = function() {
			$('.shrinkOnMobile:not(.shrunk)').each(function(){
				var bp = $(this).attr('data-breakpoint') || 768,
					windowWidth = window.innerWidth;
				if ( bp <= windowWidth ) {
					return;
				}
				var $original = $(this),
					$clone = $(this).clone().css({'visibility':'hidden','position':'absolute','top':'-100vh'}).appendTo('body');
				$clone.each(function(){
					var style = getComputedStyle(this),
						fontSize = parseInt(style.fontSize),
						lineHeight = style.lineHeight,
						lineHeightVal = parseInt(lineHeight),
						scale = $(this).attr('data-scale') || .8,
						shrunkenFontSize = fontSize * scale,
						shrunkenLineHeightVal = lineHeightVal * scale,
						// replace number in original lineHeight with shrunkenLineHeightVal while keeping unit.
						shrunkenLineHeight = lineHeight.replace(/[0-9]/, shrunkenLineHeightVal);
			
					$original.css({'font-size': shrunkenFontSize + 'px', 'line-height': shrunkenLineHeight}).addClass('shrunk');
					$(this).remove();
				});
			});
		}
		readyFuncs.shrinkOnMobile = funcs.shrinkOnMobile;

		funcs.createPWReEntryIcon = function() {
			$('.rmagic').each(function(){
				var $passwordInputs = $(this).find('input[type="password"]');
				if ( $passwordInputs.length !== 2 ) {
					return;
				}
				var $passwordInput = $($passwordInputs[0]),
					$passwordInputRow = $passwordInput.closest('.rmrow'),
					$passwordReEntry = $($passwordInputs[1]),
					$passwordReEntryLabel = $passwordReEntry.closest('.rmrow').find('label'),
					$icon = $passwordInputRow.find('.rm_front_field_icon'),
					$iconParent = $icon.parent();
					$iconParent.each(function(){
							var $clone = $(this).clone();
							$clone.each(function(){
								var $cloneIcon = $clone.find('.rm_front_field_icon'),
									$inner = $cloneIcon.wrapInner('<div class="rm_front_field_icon-inner">').children(),
									$innerClone = $inner.clone();
								$innerClone.addClass('rm_front_field_icon-inner-clone');
								$cloneIcon.append($innerClone);
							});
							$passwordReEntryLabel.prepend($clone);
					});
			}); 
		}
		readyFuncs.createPWReEntryIcon = funcs.createPWReEntryIcon;

		funcs.addTransitionFilter = function($elm) {
			var origTrans = $elm.css('transition'),
				filterTrans = 'filter 3s ease 2s',
				newTrans = origTrans ?
					origTrans + ',' + filterTrans :
					filterTrans;
				// console.log(origTrans);
			$elm.css({
				WebkitTransition : newTrans,
				transition       : newTrans
			});
			return $elm;
		}

		funcs.featuredImageLoaded = function() {
			var $featuredImage = $('.page-header-bg-image, .video-color-overlay, .nectar-recent-post-bg, .post-bg-img, .post-featured-img[style*="background-image"], .post-featured-img[data-nectar-img-src], .post-featured-img:not(img) img');
			$featuredImage.each(function(){
				// console.log('$(this)', $(this));
				// if ($(this).is('.post-featured-img:not(img) img')) {
				// 	console.log("$(this).is('.post-featured-img:not(img) img')", $(this).is('.post-featured-img:not(img) img'));
				// }
				var bgImage = $(this).css('background-image');
				funcs.addTransitionFilter($(this));
				if ( ! $(this).is('img') && bgImage !== 'none' ) {
					var bgImageURL =  bgImage.replace('url(','').replace(')','').replace(/\"/gi, ""),
						img = document.createElement('IMG'),
						$that = $(this);
					$(img).css({
						position : 'absolute',
						top : '-9999px',
						left : '-9999px',
						opacity : 0
					})
					.appendTo($body)
					.attr('src', bgImageURL)
					.load(function() {
						$that.addClass('loaded');
						$(this).remove();
					});
				} else if($(this).is('img')) {
					// console.log("$(this).closest('.calendar-column').length", $(this).closest('.calendar-column').length);
					// if ( $(this).closest('.calendar-column').length ) {
					// 	console.log('loaded');
					// }
					// if ( this.loading === 'lazy' && this.complete === true ) {
					// 	$(this).addClass('loaded');
					// } else {
						$(this).on('appear load', function(){
							$(this).addClass('loaded');
						});
					// }
				}
			});
		}
		readyFuncs.featuredImageLoaded = funcs.featuredImageLoaded;

		funcs.openRMPopupInsteadOfNavigate = function() {
			$('[href*="/login"]').on('click', function(e){
				var $rmPopup = $('#rm-login-open');
				if ( $rmPopup.length ) {
					e.preventDefault();
					$rmPopup.trigger('click');
				}
			});
			$('[href*="/subscribe"]').on('click', function(e){
				var $rmPopup = $('#rm-register-open-big');
				if ( $rmPopup.length ) {
					e.preventDefault();
					$rmPopup.trigger('click');
				}
			});
		}
		readyFuncs.openRMPopupInsteadOfNavigate = funcs.openRMPopupInsteadOfNavigate;

		funcs.executeFuncs(readyFuncs);
	});
})(jQuery);