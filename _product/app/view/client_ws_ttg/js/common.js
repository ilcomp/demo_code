var ua = window.navigator.userAgent.toLowerCase(),
	is_ie = (/trident/gi).test(ua) || (/msie/gi).test(ua);

var quantity_update = function(input) {
		var parent = input.parentNode,
			minus = parent.querySelector('.quantity-minus'),
			plus = parent.querySelector('.quantity-plus'),
			block = parent.querySelector('.quantity-value'),
			minimum = input.dataset.minimum;

		input.display = 'none';

		if (minimum == undefined)
			minimum = 1;

		if (minus == undefined) {
			minus = document.createElement('button');
			minus.type = 'button';
			minus.className = 'quantity-minus';
			minus.innerHTML = '-';

			parent.append(minus);
		}

		if (block == undefined) {
			block = document.createElement('span');
			block.className = 'quantity-value number';
			block.innerHTML = input.value || 0;

			parent.append(block);
		}

		if (plus == undefined) {
			plus = document.createElement('button');
			plus.type = 'button';
			plus.className = 'quantity-plus';
			plus.innerHTML = '+';

			parent.append(plus);
		}

		input.addEventListener('change', function() {
			input.value = Math.round(input.value / minimum) * minimum;

			block.innerHTML = input.value;

			$(this).change();
		});

		minus.onclick = function() {
			input.value = (Number(input.value) - Number(minimum)) > Number(minimum) ? Number(input.value) - Number(minimum) : Number(minimum);

			input.dispatchEvent(new Event('change'));
		};

		plus.onclick = function() {
			input.value = Number(input.value) < Number(minimum) ? Number(minimum) : Number(input.value) + Number(minimum);

			input.dispatchEvent(new Event('change'));
		};
	};

$(function(){
	$('[name^=quantity]').each(function() {
		quantity_update(this);
	});

	$('body').on('focus keyup input', 'input[type=tel]', function(){
		var value = '',
			plus = false,
			zero = false;

		this.value = this.value.replace(/[^\+\d\(\)\- ]/g, '').replace(/^[^\+\d]+/, '');

		for (var i = 0; i < this.value.length; i++) {
			var ch = this.value[i];

			if (zero) {
				value += String(ch);
			} else if (plus && Number(ch) > 0) {
				value += String(ch);
				zero = true;
			} else if (!plus && ch == '+') {
				value += String(ch);
				plus = true;
			} else if (Number(ch) > 0) {
				value += String(ch);
				zero = true;
			}
		}

		this.value = value;
	});

	$('body').on('blur', 'input[type=tel]', function(){
		if (!/^\+?\d/.test(this.value)) this.value = '';
	});

	//modal reopen
	var modal_show = false;
	$('.modal').on('show.bs.modal', function (e) {
		var modal = $(this);

		if ($('.modal.show').not(modal).length > 0) {
			e.preventDefault();

			modal_show = modal.modal('dispose');

			$('.modal.show').not(modal).modal('hide');
		} else {
			modal_show = false;
		}

		modal.find('form input[type!=checkbox][type!=radio][type!=hidden], form textarea, form select').val('').blur();
		modal.find('form .alert-dismissible').remove();
	});

	$('.modal').on('hidden.bs.modal', function (e) {
		if (modal_show != false) {
			modal_show.modal('show');
		}
	});
});

// Autocomplete */
(function($) {
	$.fn.autocomplete = function(option) {
		return this.each(function() {
			var el = this,
				$this = $(this),
				$dropdown = $('<div class="dropdown-menu d-none"/>');

			$.extend(this, option);

			this.timer = null;
			this.items = [];
			this.requests = 0;
			this.isData = false;

			$this.attr('autocomplete', 'off');

			$this.wrap('<div class="dropdown">');

			var $wrap = $this.parent();

			$wrap.on('focus', '*', function(e) {
				el.show();

				if (e.target.tagName == 'INPUT') {
					el.request();
				}
			});

			$wrap.on('click', '*', function(e) {
				e.preventDefault();

				var value = $(e.target).attr('href');

				if (value && el.items[value]) {
					el.select(el.items[value]);

					el.hide();
				}
			});

			$wrap.on('blur', '*', function(e) {
				if (!($wrap.find('*').is(e.relatedTarget)))
					setTimeout(function(object) {
						el.hide();
					}, 200, this);
			});

			// Keydown
			$this.on('keydown', function(e) {
				if (e.keyCode == 27) {
					el.hide();
				}
			});

			$this.on('input', function(e) {
				el.request();
			});

			// Show
			this.show = function() {
				if (el.isData && ($wrap.is(':focus') || $wrap.find('*').is(':focus')))
					$dropdown.addClass('show');
			}

			// Hide
			this.hide = function() {
				if (!(el.isData && ($wrap.is(':focus') || $wrap.find('*').is(':focus'))))
					$dropdown.removeClass('show');
			}

			// Request
			this.request = function() {
				clearTimeout(this.timer);

				this.timer = setTimeout(function(object) {
					el.isData = false;
					el.requests++;

					object.source($(object).val(), $.proxy(object.response, object, el.requests));
				}, 100, el);
			}

			// Response
			this.response = function(ind, json) {
				if (ind != el.requests) {
					return;
				}

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
					el.isData = true;

					$dropdown.removeClass('d-none');

					el.show();
				} else {
					el.isData = false;

					$dropdown.addClass('d-none');

					el.hide();
				}

				$dropdown.html(html);
			}

			$this.after($dropdown);
		});
	}
})(window.jQuery);