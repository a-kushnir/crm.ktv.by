// jquery.tools validation with twitter bootstrap layout

// Required inputs
$.tools.validator.fn("input.required, select.required", "can't be blank", function(input, value) {
    return !input.is(':visible') || value != '';
});

// Agreement checkboxes
$.tools.validator.fn('input[type="checkbox"].required', "must be checked", function(input, value) {
    return !input.is(':visible') || input.is(':checked');
});

// Date pickers
$.tools.validator.fn("input.date-picker.today-or-in-the-future", "can't be in the past", function(input, value) {
    return !input.is(':visible') || value == '' || (Date.parse(value) > Date.now() - 24*60*60*1000);
});

// Riders (input type = 'radio')
$.tools.validator.fn('input[type="radio"].required', "must be selected", function(input, value) {
    var value = $('input:radio[name='+ input.attr('name') +']:checked').val();
    return value != null;
});

// User password
$.tools.validator.fn('#new_user_password_confirmation', "doesn't match password", function(input, value) {
    var element = $('#' + input.attr('data-match-field'));
    return element.size == 0 || element.val() == value;
});

// Month validator
$.tools.validator.fn("select.month", "can't be in the past", function(input, value) {
    var month = null;
    var year = null;

    if (value <= 12) { // Month selected
        month = parseInt(value);
        year = parseInt(input.next("select").val());
    } else { // Year selected
        month = parseInt(input.prev("select").val());
        year = parseInt(value);
    }

    var today = new Date();
    return year > today.getFullYear() || (year == today.getFullYear() && month >= today.getMonth()+1);
});

function add_validation(selector)
{
    $(selector).each(function () {
        var form = $(this);

        form.find('input[type="text"], input[type="password"], input[type="email"], input[type="tel"], input[type="checkbox"], input[type="radio"], select')
            .focus(function() {
                // Andrew K. I haven't found how to reset field error yet.
            })
            .blur(function() {
                var validator = $(this).parents('form').data('validator');
                if (validator != null) validator.checkValidity($(this))
            })

        form
            .validator({
        })
            .bind('reset.validator', function () {
                remove_all_validation_markup(form);
            })
            .bind('onSuccess', function (e, ok) {
                $.each(ok, function() {
                    var input = $(this);
                    remove_validation_markup(input);
                    // uncomment next line to highlight successfully
                    // validated fields in green
                    //add_validation_markup(input, 'success');
                });
            })
            .bind('onFail', function (e, errors) {
                $.each(errors, function() {
                    var err = this;
                    var input = $(err.input);
                    remove_validation_markup(input);
                    add_validation_markup(input, 'error',
                        err.messages.join(' '));
                });
                return false;
            });
    });
}

function find_container(input) {
    return input.parents('div.control-group');
}
function remove_validation_markup(input) {
    var cont = find_container(input);
    cont.removeClass('error success warning');
    $('.help-inline.error, .help-inline.success, .help-inline.warning',
        cont).remove();
}
function add_validation_markup(input, cls, caption) {
    var cont = find_container(input);
    cont.addClass(cls);
    input.addClass(cls);

    if (caption) {
        var msg = $('<span class="help-inline"/>');
        msg.addClass(cls);
        msg.text(caption);

        var asterisk = input.next("abbr");
        var second_select = input.next("select");

        if (asterisk.size() > 0) // Checkboxes
            asterisk.after(msg);

        else if (second_select.size() > 0) // Month control
            second_select.after(msg);

        else
            input.after(msg);
    }
}
function remove_all_validation_markup(form) {
    $('.help-inline.error, .help-inline.success, .help-inline.warning',
        form).remove();
    $('.error, .success, .warning', form)
        .removeClass('error success warning');
}