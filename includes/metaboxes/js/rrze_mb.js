window.RRZE_MB = (function(window, document, $, undefined) {
    'use strict';

    var l10n = window.rrze_mb_l10;

    var rrze_mb = {
        formfield: '',
        idNumber: false
    };

    rrze_mb.metabox = function() {
        if (rrze_mb.$metabox) {
            return rrze_mb.$metabox;
        }
        rrze_mb.$metabox = $('table.rrze_mb_metabox');
        return rrze_mb.$metabox;
    };

    rrze_mb.init = function() {

        var $metabox = rrze_mb.metabox();

        rrze_mb.initPickers($metabox.find('input:text.rrze_mb_timepicker'), $metabox.find('input:text.rrze_mb_datepicker'), $metabox.find('input:text.rrze_mb_colorpicker'));

        $("#ui-datepicker-div").wrap('<div class="rrze-mb-element" />');
    };

    rrze_mb.toggleCheckBoxes = function(event) {
        event.preventDefault();
        var $self = $(this);
        var $multicheck = $self.parents('td').find('input[type=checkbox]');

        if ($self.data('checked')) {
            $multicheck.prop('checked', false);
            $self.data('checked', false);
        }
        else {
            $multicheck.prop('checked', true);
            $self.data('checked', true);
        }
    };

    rrze_mb.emptyValue = function(event, row) {
        $('input:not([type="button"]), textarea', row).val('');
    };

    rrze_mb.initPickers = function($timePickers, $datePickers, $colorPickers) {
        rrze_mb.initTimePickers($timePickers);
        rrze_mb.initDatePickers($datePickers);
        rrze_mb.initColorPickers($colorPickers);
    };

    rrze_mb.initTimePickers = function($selector) {
        if (!$selector.length) {
            return;
        }

        $selector.timePicker({
            startTime: "00:00",
            endTime: "23:59",
            show24Hours: false,
            separator: ':',
            step: 30
        });
    };

    rrze_mb.initDatePickers = function($selector) {
        if (!$selector.length) {
            return;
        }

        $selector.datepicker("destroy");
        $selector.datepicker();
    };

    rrze_mb.initColorPickers = function($selector) {
        if (!$selector.length) {
            return;
        }
        if (typeof jQuery.wp === 'object' && typeof jQuery.wp.wpColorPicker === 'function') {

            $selector.wpColorPicker();

        } else {
            $selector.each(function(i) {
                $(this).after('<div id="picker-' + i + '" style="z-index: 1000; background: #EEE; border: 1px solid #CCC; position: absolute; display: block;"></div>');
                $('#picker-' + i).hide().farbtastic($(this));
            })
                    .focus(function() {
                        $(this).next().show();
                    })
                    .blur(function() {
                        $(this).next().hide();
                    });
        }
    };

    rrze_mb.log = function() {
        if (l10n.script_debug && console && typeof console.log === 'function') {
            console.log.apply(console, arguments);
        }
    };

    $(document).ready(rrze_mb.init);

    return rrze_mb;

})(window, document, jQuery);
