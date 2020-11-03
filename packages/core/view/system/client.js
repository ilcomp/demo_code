function getURLPath() {
	return String(document.location.pathname);
}

function getURLVar(key) {
	var value = [];

	var part = String(document.location.search).substring(1).split('&');

	for (i = 0; i < part.length; i++) {
		var data = part[i].split('=');

		if (data[0] && data[1] && data[0] == key)
			return data[1];
	}

	return '';
}

// $(document).ready(function() {
// 	// Add new div on each page
// 	$('body').append('<div id="alert-box"></div>');

// 	$('#alert-box').on('click', '.close', function(){
// 		$('#alert-box').removeClass('open');
// 	});

// 	// Highlight any found errors
// 	$('.text-danger').each(function() {
// 		var element = $(this).parent().find(':input');

// 		if (element.hasClass('form-control')) {
// 			element.addClass('is-invalid');
// 		}
// 	});

// 	if ('tooltip' in $.fn) {
// 		// tooltips on hover
// 		$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});

// 		// Makes tooltips work on ajax generated content
// 		$(document).ajaxStop(function() {
// 			$('[data-toggle=\'tooltip\']').tooltip({container: 'body'});
// 		});
// 	}
// });

// /* Agree to Terms */
// $(document).delegate('.agree', 'click', function(e) {
// 	e.preventDefault();

// 	$('#modal-agree').remove();

// 	var element = this;

// 	$.ajax({
// 		url: $(element).attr('href'),
// 		type: 'get',
// 		dataType: 'html',
// 		success: function(data) {
// 			html  = '<div id="modal-agree" class="modal fade">';
// 			html += '  <div class="modal-dialog">';
// 			html += '    <div class="modal-content">';
// 			html += '      <div class="modal-header">';
// 			html += '        <h4 class="modal-title">' + $(element).text() + '</h4>';
// 			html += '        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
// 			html += '      </div>';
// 			html += '      <div class="modal-body">' + data + '</div>';
// 			html += '    </div>';
// 			html += '  </div>';
// 			html += '</div>';

// 			$('body').append(html);

// 			$('#modal-agree').modal('show');
// 		}
// 	});
// });

// class Chain {
// 	constructor() {
// 		this.start = false;
// 		this.data = [];
// 	}

// 	attach(call) {
// 		this.data.push(call);

// 		if (!this.start) {
// 			this.execute();
// 		}
// 	}

// 	execute() {
// 		if (this.data.length) {
// 			this.start = true;

// 			(this.data.shift())().done(function() {
// 				chain.execute();
// 			});
// 		} else {
// 			this.start = false;
// 		}
// 	}
// }

// var chain = new Chain();

// // Autocomplete */
// (function($) {
// 	$.fn.autocomplete = function(option) {
// 		return this.each(function() {
// 			this.timer = null;
// 			this.items = new Array();

// 			$.extend(this, option);

// 			$(this).attr('autocomplete', 'off');

// 			// Focus
// 			$(this).on('focus', function() {
// 				this.request();
// 			});

// 			// Blur
// 			$(this).on('blur', function() {
// 				setTimeout(function(object) {
// 					object.hide();
// 				}, 200, this);
// 			});

// 			// Keydown
// 			$(this).on('keydown', function(event) {
// 				switch(event.keyCode) {
// 					case 27: // escape
// 						this.hide();
// 						break;
// 					default:
// 						this.request();
// 						break;
// 				}
// 			});

// 			// Click
// 			this.click = function(event) {
// 				event.preventDefault();

// 				value = $(event.target).parent().attr('data-value');

// 				if (value && this.items[value]) {
// 					this.select(this.items[value]);
// 				}
// 			};

// 			// Show
// 			this.show = function() {
// 				var pos = $(this).position();

// 				$(this).siblings('ul.dropdown-menu').css({
// 					top: pos.top + $(this).outerHeight(),
// 					left: pos.left
// 				});

// 				$(this).siblings('ul.dropdown-menu').show();
// 			};

// 			// Hide
// 			this.hide = function() {
// 				$(this).siblings('ul.dropdown-menu').hide();
// 			};

// 			// Request
// 			this.request = function() {
// 				clearTimeout(this.timer);

// 				this.timer = setTimeout(function(object) {
// 					object.source($(object).val(), $.proxy(object.response, object));
// 				}, 200, this);
// 			};

// 			// Response
// 			this.response = function(json) {
// 				html = '';

// 				if (json.length) {
// 					for (i = 0; i < json.length; i++) {
// 						this.items[json[i]['value']] = json[i];
// 					}

// 					for (i = 0; i < json.length; i++) {
// 						if (!json[i]['category']) {
// 							html += '<li data-value="' + json[i]['value'] + '"><a href="#">' + json[i]['label'] + '</a></li>';
// 						}
// 					}

// 					// Get all the ones with a categories
// 					var category = new Array();

// 					for (i = 0; i < json.length; i++) {
// 						if (json[i]['category']) {
// 							if (!category[json[i]['category']]) {
// 								category[json[i]['category']] = new Array();
// 								category[json[i]['category']]['name'] = json[i]['category'];
// 								category[json[i]['category']]['item'] = new Array();
// 							}

// 							category[json[i]['category']]['item'].push(json[i]);
// 						}
// 					}

// 					for (i in category) {
// 						html += '<li class="dropdown-header">' + category[i]['name'] + '</li>';

// 						for (j = 0; j < category[i]['item'].length; j++) {
// 							html += '<li data-value="' + category[i]['item'][j]['value'] + '"><a href="#">&nbsp;&nbsp;&nbsp;' + category[i]['item'][j]['label'] + '</a></li>';
// 						}
// 					}
// 				}

// 				if (html) {
// 					this.show();
// 				} else {
// 					this.hide();
// 				}

// 				$(this).siblings('ul.dropdown-menu').html(html);
// 			};

// 			$(this).after('<ul class="dropdown-menu"></ul>');
// 			$(this).siblings('ul.dropdown-menu').delegate('a', 'click', $.proxy(this.click, this));
// 		});
// 	};
// })(window.jQuery);

// +function ($) {
// 	'use strict';

// 	// BUTTON PUBLIC CLASS DEFINITION
// 	// ==============================

// 	var Button = function (element, options) {
// 		this.$element  = $(element)
// 		this.options   = $.extend({}, Button.DEFAULTS, options)
// 		this.isLoading = false
// 	}

// 	Button.VERSION  = '3.3.5'

// 	Button.DEFAULTS = {
// 		loadingText: 'loading...'
// 	}

// 	Button.prototype.setState = function (state) {
// 		var d    = 'disabled'
// 		var $el  = this.$element
// 		var val  = $el.is('input') ? 'val' : 'html'
// 		var data = $el.data()

// 		state += 'Text'

// 		if (data.resetText == null) $el.data('resetText', $el[val]())

// 		// push to event loop to allow forms to submit
// 		setTimeout($.proxy(function () {
// 			$el[val](data[state] == null ? this.options[state] : data[state])

// 			if (state == 'loadingText') {
// 				this.isLoading = true
// 				$el.addClass(d).attr(d, d)
// 			} else if (this.isLoading) {
// 				this.isLoading = false
// 				$el.removeClass(d).removeAttr(d)
// 			}
// 		}, this), 0)
// 	}

// 	Button.prototype.toggle = function () {
// 		var changed = true
// 		var $parent = this.$element.closest('[data-toggle="buttons"]')

// 		if ($parent.length) {
// 			var $input = this.$element.find('input')
// 			if ($input.prop('type') == 'radio') {
// 				if ($input.prop('checked')) changed = false
// 				$parent.find('.active').removeClass('active')
// 				this.$element.addClass('active')
// 			} else if ($input.prop('type') == 'checkbox') {
// 				if (($input.prop('checked')) !== this.$element.hasClass('active')) changed = false
// 				this.$element.toggleClass('active')
// 			}
// 			$input.prop('checked', this.$element.hasClass('active'))
// 			if (changed) $input.trigger('change')
// 		} else {
// 			this.$element.attr('aria-pressed', !this.$element.hasClass('active'))
// 			this.$element.toggleClass('active')
// 		}
// 	}


// 	// BUTTON PLUGIN DEFINITION
// 	// ========================

// 	function Plugin(option) {
// 		return this.each(function () {
// 			var $this   = $(this)
// 			var data    = $this.data('bs.button')
// 			var options = typeof option == 'object' && option

// 			if (!data) $this.data('bs.button', (data = new Button(this, options)))

// 			if (option == 'toggle') data.toggle()
// 			else if (option) data.setState(option)
// 		})
// 	}

// 	var old = $.fn.button

// 	$.fn.button             = Plugin
// 	$.fn.button.Constructor = Button


// 	// BUTTON NO CONFLICT
// 	// ==================

// 	$.fn.button.noConflict = function () {
// 		$.fn.button = old
// 		return this
// 	}


// 	// BUTTON DATA-API
// 	// ===============

// 	$(document)
// 		.on('click.bs.button.data-api', '[data-toggle^="button"]', function (e) {
// 			var $btn = $(e.target)
// 			if (!$btn.hasClass('btn')) $btn = $btn.closest('.btn')
// 			Plugin.call($btn, 'toggle')
// 			if (!($(e.target).is('input[type="radio"]') || $(e.target).is('input[type="checkbox"]'))) e.preventDefault()
// 		})
// 		.on('focus.bs.button.data-api blur.bs.button.data-api', '[data-toggle^="button"]', function (e) {
// 			$(e.target).closest('.btn').toggleClass('focus', /^focus(in)?$/.test(e.type))
// 		})

// }(jQuery);