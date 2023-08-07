/*
 * Field ReportResult plugin
 *
 * Data attributes:
 * - data-control="field-reportresult" - enables the plugin on an element
 * - data-option="value" - an option with a value
 *
 * JavaScript API:
 * $('a#someElement').fieldReportResult({...})
 */

+ function ($) {
    "use strict";
    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    // FIELD ACTIVITYLOG CLASS DEFINITION
    // ============================

    var ReportResult = function (element, options) {
        this.options = options

        this.$el = $(element)

        $.oc.foundation.controlUtils.markDisposable(element)
        Base.call(this)
        this.init()
    }

    ReportResult.prototype = Object.create(BaseProto)
    ReportResult.prototype.constructor = ReportResult

    ReportResult.DEFAULTS = {
        // @note: Nothing here atm...
    }

    ReportResult.prototype.init = function () {
        this.$el.on('dispose-control', this.proxy(this.dispose))
    }

    ReportResult.prototype.dispose = function () {
        this.$el.off('dispose-control', this.proxy(this.dispose));
        this.$el.removeData('oc.reportresult');
        this.$el = null
        this.options = null
        BaseProto.dispose.call(this)
    }

    // Deprecated
    ReportResult.prototype.unbind = function () {
        this.dispose()
    }

    // FIELD ACTIVITYLOG PLUGIN DEFINITION
    // ============================

    var old = $.fn.fieldReportResult

    $.fn.fieldReportResult = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result
        this.each(function () {
            var $this = $(this)
            var data = $this.data('oc.reportresult')
            var options = $.extend({}, ReportResult.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.reportresult', (data = new ReportResult(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.fieldReportResult.Constructor = ReportResult

    // FIELD ACTIVITYLOG NO CONFLICT
    // =================

    $.fn.fieldReportResult.noConflict = function () {
        $.fn.fieldReportResult = old
        return this
    }

    // FIELD ACTIVITYLOG DATA-API
    // ===============

    $(document).render(function () {
        $('[data-control="field-reportresult"]').fieldReportResult()
    });

}(window.jQuery);


+ function ($) {
    "use strict";

    var ReportResultWidget = function () {
        this.clickReportResultRecord = function (recordId, triggerEl) {
            $(triggerEl).popup({
                handler: 'onViewReportReportDetails',
                size: 'huge',
                extraData: {
                    'jaxwilkoHugoReportId': recordId,
                }
            });
        }
    }

    $.oc.reportResultWidget = new ReportResultWidget;
}(window.jQuery);
