jQuery(document).ready(function($){
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

	// green placeholder
	$("body").on('focus', '.callback-input input, .callback-input textarea, .callback-input select', function(e) {		
		var input = $(this), div = input.parents('.callback-input').children('div');
		if(e.target.tagName == 'TEXTAREA'){
			div.hide();
		} else{
			div.addClass("active");
		}
		input.parents('.callback-input').addClass("active");
	});

	$("body").on('blur', '.callback-input input, .callback-input textarea, .callback-input select', function(e) {
		var input = $(this), div = input.parents('.callback-input').children('div');
		if(div.length > 0 && (input.val() == '' || input.val() == '+7 (___) ___-____')){
			if(e.target.tagName == 'TEXTAREA'){
				div.show();
			} else{
				div.removeClass("active");
			}
			$(this).parents('.callback-input').removeClass("active");
		}
	});

	$('.modal').on('DOMSubtreeModified', function (e) {
		var input = $(this), div = input.parents('.callback-input').children('div');
		if(!(div.length > 0 && (input.val() == '' || input.val() == '+7 (___) ___-____'))){
			if(e.target.tagName == 'TEXTAREA'){
				div.hide();
			} else{
				div.addClass("active");
			}
			$(this).parents('.callback-input').addClass("active");
		}
	});
	$(".callback-input input, .callback-input textarea").each(function(e) {
		var input = $(this), div = input.parents('.callback-input').children('div');
		if(!(div.length > 0 && (input.val() == '' || input.val() == '+7 (___) ___-____'))){
			if(e.target.tagName == 'TEXTAREA'){
				div.hide();
			} else{
				div.addClass("active");
			}
			$(this).parents('.callback-input').addClass("active");
		}
	});
	
	// убираем значек незаполненного поля в форме
	$('body').on('mouseenter', '.wrap_input', function() {
	    $(this).find(".wpcf7-not-valid-tip").css("display", "none");
	});

	// menu 
	$('header .main-menu__but').click(function() {
		$(".main-menu__but").toggleClass("but_hid");
		$("body, header").toggleClass("active");
	});


	// якоря
	$(".js_anchor").click(function() {
		var elementClick = $(this).attr("href");
		var destination = $(elementClick).offset().top;
		jQuery("html:not(:animated),body:not(:animated)").animate({
		  scrollTop: destination
		// }, 800);
		}, 0);
		return false;
	});

	// popover
	//$('[data-toggle="dropdown"]').popover();

	if($(".main-slider").length > 0){
		var owl = $('.main-slider .slider').owlCarousel({
			loop: true,
			nav:false,
			items: 1,
			dots: true,
			mouseDrag: false,
			autoplay: true,
			lazyLoad: true,
			//autoplaySpeed: 2000,
			//smartSpeed: 4000,
			autoplayTimeout: 7000,
			animateOut: 'fadeOut',
			animateIn: 'fadeIn',
		});
	}
	// слайдер товаров
	if($(".products-slider").length > 0){
		var owl2 = $('.products-slider .slider').owlCarousel({
			loop: true,
			nav:true,
			items: 4,
			dots: false,
			lazyLoad: true,
			autoplay: true,
			navText: ['<i class="slider-arrow"><svg id="arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 129 129" enable-background="new 0 0 129 129" width="100%" height="100%"><g><path d="m121.3,34.6c-1.6-1.6-4.2-1.6-5.8,0l-51,51.1-51.1-51.1c-1.6-1.6-4.2-1.6-5.8,0-1.6,1.6-1.6,4.2 0,5.8l53.9,53.9c0.8,0.8 1.8,1.2 2.9,1.2 1,0 2.1-0.4 2.9-1.2l53.9-53.9c1.7-1.6 1.7-4.2 0.1-5.8z" fill="rgb(255, 255, 255)"/></g></svg></i>', '<i class="slider-arrow"><svg id="arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 129 129" enable-background="new 0 0 129 129" width="100%" height="100%"><g><path d="m121.3,34.6c-1.6-1.6-4.2-1.6-5.8,0l-51,51.1-51.1-51.1c-1.6-1.6-4.2-1.6-5.8,0-1.6,1.6-1.6,4.2 0,5.8l53.9,53.9c0.8,0.8 1.8,1.2 2.9,1.2 1,0 2.1-0.4 2.9-1.2l53.9-53.9c1.7-1.6 1.7-4.2 0.1-5.8z" fill="rgb(255, 255, 255)"/></g></svg></i>'],
			responsive:{
				0:{
				  items:1,
				},
				768:{
				  items:2,
				},
				992:{
				  items:3,
				},
				1200:{
				  items: 4,
				},
			}
		});
	}

	//slider партнеров
	if($(".partners-slider").length > 0){
		var owl3 = $('.partners-slider .slider').owlCarousel({
			loop: true,
			nav:false,
			items: 6,
			dots: false,
			margin: 40,
			lazyLoad: true,
			autoplay: true,
			autoplaySpeed: 4000,
			responsive:{
				0:{
				  items:2,
				  autoplay: true,
				},
				768:{
				  items:4,
				},
				992:{
				  items:5,
				},
				1200:{
				  items: 6,
				},
			}
		});
	}

	// spoiler main
	$(".js-spoiler").click(function() {
		$(this).addClass("d-none");
		$(this).siblings(".js-spoiler-content").toggleClass("active");
		// 		var destination = window.pageYOffset;
		// console.log(destination);
		// $("html:not(:animated),body:not(:animated)").animate({
		//   scrollTop: destination
		// }, 0);
	});

	// collaps partners
	$(".js-spoiler-partners").click(function(e) {
		e.preventDefault();
		$(this).parents(".menu-sidebar").toggleClass("active");
		$(this).parents(".menu-sidebar").find(".js-spoiler-content").slideToggle(0);
	});
	
	// spoiler procedure
	$(".js-spoiler-procedure").click(function(e) {
		e.preventDefault();
		$(this).siblings(".js-spoiler-procedure-content").toggle();
		$(this).toggleClass("active");
	});

	// слайдер похожих товаров
	if($(".other-products-slider").length > 0){
		var owl4 = $('.other-products-slider .slider').owlCarousel({
			//loop: true,
			nav:true,
			items: 4,
			lazyLoad: true,
			dots: false,
			navText: ['<i class="slider-arrow"><svg id="arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 129 129" enable-background="new 0 0 129 129" width="100%" height="100%"><g><path d="m121.3,34.6c-1.6-1.6-4.2-1.6-5.8,0l-51,51.1-51.1-51.1c-1.6-1.6-4.2-1.6-5.8,0-1.6,1.6-1.6,4.2 0,5.8l53.9,53.9c0.8,0.8 1.8,1.2 2.9,1.2 1,0 2.1-0.4 2.9-1.2l53.9-53.9c1.7-1.6 1.7-4.2 0.1-5.8z" fill="rgb(255, 255, 255)"/></g></svg></i>', '<i class="slider-arrow"><svg id="arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 129 129" enable-background="new 0 0 129 129" width="100%" height="100%"><g><path d="m121.3,34.6c-1.6-1.6-4.2-1.6-5.8,0l-51,51.1-51.1-51.1c-1.6-1.6-4.2-1.6-5.8,0-1.6,1.6-1.6,4.2 0,5.8l53.9,53.9c0.8,0.8 1.8,1.2 2.9,1.2 1,0 2.1-0.4 2.9-1.2l53.9-53.9c1.7-1.6 1.7-4.2 0.1-5.8z" fill="rgb(255, 255, 255)"/></g></svg></i>'],
			responsive:{
				0:{
				  items:1,
				  //autoplay: true,
				},
				768:{
				  items:2,
				},
				992:{
				  items:3,
				},
				1200:{
				  items: 4,
				},
			}
		});
	}

	if($(".award-slider").length > 0){
		var owl5 = $('.award-slider').owlCarousel({
			//loop: true,
			nav:true,
			items: 4,
			dots: false,
			margin:30,
			autoWidth:true,
			navText: ['<i class="slider-arrow"><svg id="arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 129 129" enable-background="new 0 0 129 129" width="100%" height="100%"><g><path d="m121.3,34.6c-1.6-1.6-4.2-1.6-5.8,0l-51,51.1-51.1-51.1c-1.6-1.6-4.2-1.6-5.8,0-1.6,1.6-1.6,4.2 0,5.8l53.9,53.9c0.8,0.8 1.8,1.2 2.9,1.2 1,0 2.1-0.4 2.9-1.2l53.9-53.9c1.7-1.6 1.7-4.2 0.1-5.8z" fill="rgb(255, 255, 255)"/></g></svg></i>', '<i class="slider-arrow"><svg id="arrow" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 129 129" enable-background="new 0 0 129 129" width="100%" height="100%"><g><path d="m121.3,34.6c-1.6-1.6-4.2-1.6-5.8,0l-51,51.1-51.1-51.1c-1.6-1.6-4.2-1.6-5.8,0-1.6,1.6-1.6,4.2 0,5.8l53.9,53.9c0.8,0.8 1.8,1.2 2.9,1.2 1,0 2.1-0.4 2.9-1.2l53.9-53.9c1.7-1.6 1.7-4.2 0.1-5.8z" fill="rgb(255, 255, 255)"/></g></svg></i>'],
		});
	}


	// листалка изображений в карточке товаров
	if($(".cart-images").length > 0){
		function active_small_img(elem){
			var href = elem.find("img").attr("src");
			$(".cart-images__large a").attr("href", href);
			$(".cart-images__large a img").attr("src", href);
			$('.small-cart-img').removeClass('active');
			elem.addClass('active');
		}
		$('.small-cart-img').on('click',function () {
			active_small_img($(this));
		});
		$('.small-cart-img:eq(0)').click();
	}

	$('[data-toggle=tooltip]').tooltip();

	$(".js-focus-input").siblings('[type=checkbox]').on('click',function () {
		var el = $(this).siblings('.checkbox-block-count');
		if($(this).prop("checked")){
			el.prop('disabled', false);
			el.focus();
		} else{
			el.prop('disabled', true);
		}
	});

	//password visible change	
	$('.visible-password').on('click',function () {
		if($(this).hasClass("active")){
			$(this).removeClass("active");
			$(this).siblings('input').prop("type", "password");
		} else{
			$(this).addClass("active");
			$(this).siblings('input').prop("type", "text");
		}
	});

	// fixed header
	var hb = $('.header-bottom'),
		wc = $(".wrap-specialist"),
		h_head = $('header').height(),
		h_head_b = hb.outerHeight(true);
	function scroll_head(){
		if($(window).width() > 1199){
		  var scroll = $(window).scrollTop();
		  if(scroll > h_head){
		  	if(!hb.hasClass("fixed")){
		  		wc.appendTo(".header-bottom > .container");
			    hb.addClass('fixed animated slideInDown');
			    if($('.void-head').length == 0){
					$("header").append('<div class="void-head" style="height:'+h_head_b+'px;"></div>');
			    }
		    }
		  } else{
		  	if($(".header-bottom .wrap-specialist").length > 0){
				wc.appendTo(".header-center .js-wrap-cpec");
			}
			hb.removeClass('fixed animated slideInDown');
			if($('.void-head').length > 0){
				$(".void-head").remove();
			}
		  }
		} else{
		  hb.removeClass('fixed animated slideInDown');
		  $(".void-head").remove();
		  wc.appendTo(".header-center .js-wrap-cpec");
		}
	}
	scroll_head();
	$(window).scroll(function() {
		scroll_head();
	});
	$(window).resize(function() {
		h_head = $('header').height();
		h_head_b = $(".header-bottom").outerHeight(true);
		if($(window).width() > 1199){
		} else{
		  $(".header-bottom").removeClass('fixed animated slideInDown');
		 	$(".void-head").remove();
		 	$(".header-bottom .wrap-specialist").remove();
		}
	});

	// скрываем миниатюры товара на мобильнике если < 1
	if($('.cart-images__small .small-cart-img').length < 2 && window.innerWidth < 768){
		$('.cart-images__small').hide();
	}

	// поиск товаров в модалке
	function searchProducts(el){
		var value = el.val().toUpperCase();

		$('.modal .set-product-list__item').each(function(){
			var arr = $(this).find('.modal-set-p p').text().toUpperCase();
			if(arr.indexOf(value) > -1){
				$(this).parent('[class ^=col-]').show();
			} else{
				$(this).parent('[class ^=col-]').hide();
			}
		});
	}
	$(document).on('submit', '.cance-search-block form', function () {
		//searchProducts($('.modal .search-block__input'));
		return false;
	});
	$(document).on('input', '.modal .search-block__input', function () {
		searchProducts($(this));
	});
	
	function delTap(el){
		el.removeClass('set-product-exist-tap');
	}
	$('.set-product-exist').on('click',function () {
		$(this).addClass('set-product-exist-tap');
		setTimeout(delTap, 500, $(this));
	});
});


$(window).on("load", function (e) {
	// menu 
	var modalHeight;
	// адаптив модалки на мобильнике
	$('.main-menu__but').on('click',function (e) {
		e.preventDefault();
		$('.main-menu__menu2').toggleClass("active");
		modalHeight = $('.main-menu__menu2').outerHeight(true);
		
		if (window.innerWidth < 992) {
			$("body").toggleClass("body-hide");
			$("html").toggleClass("active");
			if($("body").hasClass('body-hide')){
				//console.log(modalHeight);
				$("body").css('height', (modalHeight + 102)+"px");
				//$("main").css('overflow-x',"initial");
			} else{
				$("body").css('height', 'auto');
				//$("main").css('overflow-x',"hidden");
			}
		}
	});
	
	if($(window).width() < 991){
		$('.main-menu__menu > .menu-item-has-children > a').click(function(e) {
			e.preventDefault();
			$(this).toggleClass("active");
			$(this).siblings(".sub-menu").toggleClass("active");
			modalHeight = $('.main-menu__menu2').outerHeight(true);
			modalHeight += 102;
			//console.log(modalHeight);
			$("body").css('height', modalHeight+"px");
		});
	}


	$(window).resize(function() {
		if (window.innerWidth > 991) {
			$("body").removeClass("body-hide");
			$("html").removeClass("active");
			$("body").css('height', 'auto');
			//$("main").css('overflow-x',"hidden");
		}
	});

});