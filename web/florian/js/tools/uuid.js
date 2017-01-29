$(document).ready(function () {

    $('#uuid-form').validators({
        fields: {
            'input': {
                validators: {
                    regexp: {
                        regexp: /^[a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12}$/,
                        message: 'You can introduce just alphabetical characters, underscore, number but no spaces'
                    }
                }
            }
        },
        highlight: function (element) {
            $(element).closest('.control-group').removeClass('success').addClass('error');
        },
        success: function (element) {
            element.text('OK!').addClass('valid')
                .closest('.control-group').removeClass('error').addClass('success');
        }
    });
});