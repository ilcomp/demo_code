(function(){
    var inputs = $('#form-general input, #form-general textarea').filter('[id^=input-meta]');

    inputs.each(function(){
        $(this).after('<small class="char-count" style="padding: 2px 4px;"></small');
    });

    inputs.on('input change', function(){
        var len = this.value.length,
            color = 'alert-dark';

        if (this.id.indexOf('title') > 0) {
            if (len > 75)
                color = 'alert-danger';
            else if (len > 60)
                color = 'alert-warning';
        } else {
            if (len > 190)
                color = 'alert-danger';
            else if (len > 160)
                color = 'alert-warning';
        }

        $(this).next('.char-count').removeClass('alert-dark alert-warning alert-danger').addClass(color).text(this.value.length);
    }).change();
})()