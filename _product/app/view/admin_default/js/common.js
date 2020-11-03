$(function() {
	// if ('ClassicEditor' in window) {
	// 	$('textarea[data-toggle=\'editor\']').each(function(){
	// 		var basehref = this.dataset.basehref || null,
	// 			language = this.dataset.language || null;

	// 		ClassicEditor
	// 		.create(this, {
	// 			baseHref: basehref,
	// 			language: language
	// 		})
	// 		.then( editor => {
	// 			//window.editor = editor;
	// 		})
	// 		.catch( err => {
	// 			console.error(err.stack);
	// 		});
	// 	});
	// }

	// if ('switcheditor' in $.fn) {
	// 	$('[data-toggle=editor]').switcheditor({editors : ['ckeditor', 'codemirror'], dialogs : ['image', 'link']});
	// }

	// Highlight any found errors
	$('.invalid-tooltip').each(function() {
		var element = $(this).parent().find(':input');

		if (element.hasClass('form-control')) {
			element.addClass('is-invalid');
		}
	});

	$('.invalid-tooltip').show();

	// tooltips on hover
	$('[data-toggle=\'tooltip\']').tooltip({container: 'body', html: true});

	// Makes tooltips work on replace generated content
	$('body').on('load', '[data-toggle=\'tooltip\']', function() {
		$(this).tooltip({container: 'body'});
	});

	// tooltip remove
	$('[data-toggle=\'tooltip\']').on('remove', function() {
		$(this).tooltip('dispose');
	});

	// Tooltip remove fixed
	$(document).on('click', '[data-toggle=\'tooltip\']', function(e) {
		$('body > .tooltip').remove();
	});

	// https://github.com/opencart/opencart/issues/2595
	$.event.special.remove = {
		remove: function(o) {
			if (o.handler) {
				o.handler.apply(this, arguments);
			}
		}
	}

	$('#button-menu').on('click', function(e) {
		e.preventDefault();

		$('#column-left').toggleClass('active');
	});

	// File Manager
	$(document).on('click', '[data-toggle=\'file\']', function(e) {
		var element = $(this);

		$('#modal-image').remove();

		element.button('loading');

		fetch(getURLPath() + '?route=common/file&user_token=' + getURLVar('user_token') + '&target=' + encodeURIComponent(element.data('target')))
		.then(function(r){
			element.button('reset');

			return r.text();
		})
		.then(function(html) {
			$('body').append(html);

			$('#modal-image').modal('show');
		})
		.catch(function(error) {
			console.error(error);
		});
	});

	// Image Manager
	$(document).on('click', '[data-toggle=\'image\']', function(e) {
		var element = $(this);

		$('#modal-image').remove();

		element.button('loading');

		fetch(getURLPath() + '?route=common/filemanager&user_token=' + getURLVar('user_token') + '&target=' + encodeURIComponent(element.data('target')) + '&thumb=' + encodeURIComponent(element.data('thumb')))
		.then(function(r){
			element.button('reset');

			return r.text();
		})
		.then(function(html) {
			$('body').append(html);

			$('#modal-image').modal('show');
		})
		.catch(function(error) {
			console.error(error);
		});
	});

	$(document).on('click', '[data-toggle=\'clear\']', function() {
		var element = this;

		$($(this).data('thumb')).attr('src', $($(this).data('thumb')).data('placeholder'));

		$($(this).data('target')).val('');
	});
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			var $this = $(this);
			var $dropdown = $('<div class="dropdown-menu"/>');

			this.timer = null;
			this.items = [];

			$.extend(this, option);

			$(this).wrap('<div class="dropdown">');

			$this.attr('autocomplete', 'off');
			$this.active = false;

			// Focus
			$this.on('focus', function() {
				this.request();
			});

			// Blur
			$this.on('blur', function(e) {
				if (!$this.active) {
					this.hide();
				}
			});

			$this.parent().on('mouseover', function(e) {
				$this.active = true;
			});

			$this.parent().on('mouseout', function(e) {
				$this.active = false;
			});

			// Keydown
			$this.on('keydown', function(event) {
				if (event.keyCode == 27) this.hide();
			});

			$this.on('input', function(event) {
				this.request();
			});

			// Click
			this.click = function(event) {
				event.preventDefault();

				var value = $(event.currentTarget).attr('href');

				if (value && this.items[value]) {
					this.select(this.items[value]);

					this.hide();
				}
			}

			// Show
			this.show = function() {
				$dropdown.addClass('show');
			}

			// Hide
			this.hide = function() {
				$dropdown.removeClass('show');
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					object.source($(object).val(), $.proxy(object.response, object));
				}, 50, this);
			}

			// Response
			this.response = function(json) {
				var html = '';
				var category = {};
				var name;
				var i = 0, j = 0;

				if (json.length) {
					for (i = 0; i < json.length; i++) {
						// update element items
						this.items[json[i]['value']] = json[i];

						if (!json[i]['category']) {
							// ungrouped items
							html += '<a href="' + json[i]['value'] + '" class="dropdown-item">' + json[i]['label'] + '</a>';
						} else {
							// grouped items
							name = json[i]['category'];

							if (!category[name]) {
								category[name] = [];
							}

							category[name].push(json[i]);
						}
					}

					for (name in category) {
						html += '<h6 class="dropdown-header">' + name + '</h6>';

						for (j = 0; j < category[name].length; j++) {
							html += '<a href="' + category[name][j]['value'] + '" class="dropdown-item">&nbsp;&nbsp;&nbsp;' + category[name][j]['label'] + '</a>';
						}
					}
				}

				if (html) {
					this.show();
				} else {
					this.hide();
				}

				$dropdown.html(html);
			}

			$dropdown.on('click', '> a', $.proxy(this.click, this));

			$dropdown.blur(function(){$this.hide()});

			$this.after($dropdown);
		});
	}
})(window.jQuery);

+function($) {
	'use strict';

	// BUTTON PUBLIC CLASS DEFINITION
	// ==============================

	var Button = function(element, options) {
		this.$element = $(element)
		this.options = $.extend({}, Button.DEFAULTS, options)
		this.isLoading = false
	}

	Button.VERSION = '3.3.5'

	Button.DEFAULTS = {
		loadingText: 'loading...'
	}

	Button.prototype.setState = function(state) {
		var d = 'disabled'
		var $el = this.$element
		var val = $el.is('input') ? 'val' : 'html'
		var data = $el.data()

		state += 'Text'

		if (data.resetText == null) $el.data('resetText', $el[val]())

		// push to event loop to allow forms to submit
		setTimeout($.proxy(function() {
			$el[val](data[state] == null ? this.options[state] : data[state])

			if (state == 'loadingText') {
				this.isLoading = true
				$el.addClass(d).attr(d, d)
			} else if (this.isLoading) {
				this.isLoading = false
				$el.removeClass(d).removeAttr(d)
			}
		}, this), 0)
	}

	Button.prototype.toggle = function() {
		var changed = true
		var $parent = this.$element.closest('[data-toggle="buttons"]')

		if ($parent.length) {
			var $input = this.$element.find('input')
			if ($input.prop('type') == 'radio') {
				if ($input.prop('checked')) changed = false
				$parent.find('.active').removeClass('active')
				this.$element.addClass('active')
			} else if ($input.prop('type') == 'checkbox') {
				if (($input.prop('checked')) !== this.$element.hasClass('active')) changed = false
				this.$element.toggleClass('active')
			}
			$input.prop('checked', this.$element.hasClass('active'))
			if (changed) $input.trigger('change')
		} else {
			this.$element.attr('aria-pressed', !this.$element.hasClass('active'))
			this.$element.toggleClass('active')
		}
	}


	// BUTTON PLUGIN DEFINITION
	// ========================

	function Plugin(option) {
		return this.each(function() {
			var $this = $(this)
			var data = $this.data('bs.button')
			var options = typeof option == 'object' && option

			if (!data) $this.data('bs.button', (data = new Button(this, options)))

			if (option == 'toggle') data.toggle()
			else if (option) data.setState(option)
		})
	}

	var old = $.fn.button

	$.fn.button = Plugin
	$.fn.button.Constructor = Button


	// BUTTON NO CONFLICT
	// ==================

	$.fn.button.noConflict = function() {
		$.fn.button = old
		return this
	}


	// BUTTON DATA-API
	// ===============

	$(document)
		.on('click.bs.button.data-api', '[data-toggle^="button"]', function(e) {
			var $btn = $(e.target)
			if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
			Plugin.call($btn, 'toggle')
			if (!($(e.target).is('input[type="radio"]') || $(e.target).is('input[type="checkbox"]'))) e.preventDefault()
		})
		.on('focus.bs.button.data-api blur.bs.button.data-api', '[data-toggle^="button"]', function(e) {
			$(e.target).closest('.btn').toggleClass('focus', /^focus(in)?$/.test(e.type))
		})

}(jQuery);