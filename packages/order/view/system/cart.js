var cart = {
	'add': function(button, product_id, quantity, options) {
		quantity = quantity || 1;
		options = options || false;

		$(document).trigger('cart_load');

		var data = new FormData();
		data.append('product_id', product_id);
		data.append('quantity', quantity);
		if (options)
			options.each(function(){
				data.append(this.name, this.value);
			});

		fetch('index.php?route=order/cart/add', {
			credentials: 'include',
	        headers: {'Accept': 'application/json'},
	        cache: 'no-cache',
			method: 'post',
			body: data
		})
		.then(function(r){
			$(document).trigger('cart_loaded');

			return r.json();
		})
		.then(function(json) {
			$(document).trigger('cart_add', json);
		})
		.catch(function(error) {
			console.error(error);
		});
	},
	'update': function(cart_id, quantity) {
		quantity = quantity || 1;

		$(document).trigger('cart_load');

		var data = new FormData();
		data.append('quantity[' + cart_id + ']', quantity);

		fetch('index.php?route=order/cart/update', {
			credentials: 'include',
	        headers: {'Accept': 'application/json'},
	        cache: 'no-cache',
			method: 'post',
			body: data
		})
		.then(function(r){
			$(document).trigger('cart_loaded');

			return r.json();
		})
		.then(function(json) {
			$(document).trigger('cart_update', json);
		})
		.catch(function(error) {
			console.error(error);
		});
	},
	'remove': function(cart_id) {
		$(document).trigger('cart_load');

		var data = new FormData();
		data.append('cart_id', cart_id);

		fetch('index.php?route=order/cart/remove', {
			credentials: 'include',
	        headers: {'Accept': 'application/json'},
	        cache: 'no-cache',
			method: 'post',
			body: data
		})
		.then(function(r){
		$(document).trigger('cart_loaded');

			return r.json();
		})
		.then(function(json) {
			$(document).trigger('cart_remove', json);
		})
		.catch(function(error) {
			console.error(error);
		});
	}
};

var wishlist = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=account/wishlist/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['redirect']) {
					location = json['redirect'];
				}

				if (json['success']) {
					$('#alert-box').append('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#alert-box').addClass('open');
				}

				$('#wishlist-total span').html(json['total']);
				$('#wishlist-total').attr('title', json['total']);
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
};

var compare = {
	'add': function(product_id) {
		$.ajax({
			url: 'index.php?route=product/compare/add',
			type: 'post',
			data: 'product_id=' + product_id,
			dataType: 'json',
			success: function(json) {
				$('.alert-dismissible').remove();

				if (json['success']) {
					$('#alert-box').append('<div class="alert alert-success alert-dismissible"><i class="fa fa-check-circle"></i> ' + json['success'] + ' <button type="button" class="close" data-dismiss="alert">&times;</button></div>');

					$('#alert-box').addClass('open');

					$('#compare-total').html(json['total']);
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
			}
		});
	},
	'remove': function() {

	}
};