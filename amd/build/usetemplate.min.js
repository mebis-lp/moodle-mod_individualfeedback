define(['jquery'], function($) {
    return {
        init: function() {
            $('input.questionselect').on('change', function() {
                var id = $(this).data('id');
                var typ = $(this).data('typ');

                // Set the form value based on the checkbox.
                if ($(this).is(':checked')) {
                    var selected = true;
                    $('input[name="import_' + id + '"]').val(1);
                } else {
                    var selected = false;
                    $('input[name="import_' + id + '"]').val(0);
                }

                // Check if it's a question group that is selected.
                if (typ == 'questiongroup') {
                    $('#questiongroup_' + id + ' input.questionselect').each(function() {
                        var canchange = false;
                        if (selected) {
                            if (!$(this).is(':checked')) {
                                canchange = true;
                            }
                        } else {
                            if ($(this).is(':checked')) {
                                canchange = true;
                            }
                        }

                        if (canchange) {
                            $(this).prop('checked', selected);
                            $(this).trigger('change');
                        }
                    });
                }

                // Check if it has depended items.
                $('input.questionselect').each(function() {
                    if ($(this).data('dependitem') == id) {
                        var dependitemchange = false;
                        if (selected) {
                            if (!$(this).is(':checked')) {
                                $(this).prop('disabled', false);
                                dependitemchange = true;
                            }
                        } else {
                            if ($(this).is(':checked')) {
                                $(this).prop('disabled', true);
                                dependitemchange = true;
                            }
                        }

                        if (dependitemchange) {
                            $(this).prop('checked', selected);
                            $(this).trigger('change');
                        }
                    }
                });
            });
        }
    };
});
