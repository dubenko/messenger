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
    setAutocomplete('#from, #to');
    $('.filter-lnk a').click(function(e){
        e.preventDefault();
        $('.filter').remove();
        var that = this;
        $.ajax({
            url: $(that).attr('href'),
            cache: false,
            success: function(data) {
                $(that).parent().before(data);
                setAutocomplete('#filter-from, #filter-to');
            }
        });
        return false;
    });
});
