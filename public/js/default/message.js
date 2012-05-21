$(document).ready(function() {
    $('.add, .update').validate();
    var setAutocomplete = function(selector) {
        $(selector).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/autocomplete',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        format: 'json'
                    },
                    cache: false,
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                label: item.login,
                                value: item.login
                            }
                        }));
                    }
                });
            },
            minLength: 2
        });
    }
    setAutocomplete('#to');
});
