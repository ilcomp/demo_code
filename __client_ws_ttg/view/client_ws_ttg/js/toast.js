// Toast - modal
var toast_modal_el = $('<div class="toast-block toast-center" id="toast_modal"></div>');

$('body').append(toast_modal_el);

var toast_modal = function(content, action){
  var toast = $('<div class="toast"><div class="toast-body"><button type="button" class="ml-auto mb-1 close" data-dismiss="toast" aria-label="{{ button_continue }}"><span class="os-icon os-icon-close"></span></button>' + content + '</div></div>');

  if (toast_modal_el.find('.toast-shadow').length == 0)
    toast_modal_el.append('<div class="toast-shadow" data-dismiss="toast"></div>');

  toast_modal_el.find('.toast').remove();
  toast_modal_el.append(toast);

  setTimeout(function(){
    toast.addClass('showing');
  },10);

  toast_modal_el.find('[data-dismiss="toast"]').click(function(){
    toast_modal_el.find('.toast, .toast-shadow').remove();
  });

  if (action)
    action();
};

// Toast - confirm action
var toast_confirm = function(content, action){
  var toast = $('<div class="toast"><div class="toast-body"><div class="mb-3">' + content + '</div><div class="d-flex justify-content-between"><button type="button" class="btn btn-danger" data-toast_submit data-dismiss="toast"><i class="os-icon os-icon-ui-21"></i><span>{{ button_confirm }}</span></button><button type="button" class="btn btn-primary main-btn" data-dismiss="toast"><i class="os-icon os-icon-x-circle"></i><span>{{ button_cancel }}</span></button></div></div></div>');

  if (toast_modal_el.find('.toast-shadow').length == 0)
    toast_modal_el.append('<div class="toast-shadow" data-dismiss="toast"></div>');

  toast_modal_el.find('.toast').remove();
  toast_modal_el.append(toast);

  setTimeout(function(){
    toast.addClass('showing');
  },10);

  toast_modal_el.find('[data-dismiss="toast"]').click(function(){
    toast_modal_el.find('.toast, .toast-shadow').remove();
  });

  toast_modal_el.find('[data-toast_submit]').click(function(){
    action();
  });
};

$('body').on('click', '[data-confirm]', function(e){
  e.preventDefault();

  var href = this.href;

  toast_confirm(this.dataset.confirm, function(){location = href});
});

// Toast - message
var toast_message_el = $('<div class="toast-block toast-bottom-right" id="toast_message"></div>');

$('body').append(toast_message_el);

var toast_message = function(title, message, time){
  var toast = $('<div class="toast"><div class="toast-header">' + title + '<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="{{ button_continue }}"><span class="os-icon os-icon-close"></span></button></div><div class="toast-body d-flex justify-content-end">' + message + '</div></div>'),
    timeout,
    time = time || 20000;

  toast_message_el.prepend(toast);

  setTimeout(function(){
    toast.addClass('showing');
  },10);

  toast.on("transitionend webkitTransitionEnd oTransitionEnd", function(){
    if (!$(this).hasClass('showing')) {
      clearTimeout(timeout);
      $(this).remove();
    }
  });

  toast.find('[data-dismiss="toast"]').click(function(){
    $(this).parents('.toast').removeClass('showing');
  });

  if (time > 0) {
    timeout = setTimeout(function(){
      toast.removeClass('showing');
    }, time);
  }
};