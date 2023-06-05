/*
 * Field LighthouseResult plugin
 *
 * Data attributes:
 * - data-control="field-lighthouseresult" - enables the plugin on an element
 * - data-option="value" - an option with a value
 *
 * JavaScript API:
 * $('a#someElement').fieldLighthouseResult({...})
 */

+ function ($) {
    "use strict";
    var Base = $.oc.foundation.base,
        BaseProto = Base.prototype

    // FIELD ACTIVITYLOG CLASS DEFINITION
    // ============================

    var LighthouseResult = function (element, options) {
        this.options = options

        this.$el = $(element)

        $.oc.foundation.controlUtils.markDisposable(element)
        Base.call(this)
        this.init()
    }

    LighthouseResult.prototype = Object.create(BaseProto)
    LighthouseResult.prototype.constructor = LighthouseResult

    LighthouseResult.DEFAULTS = {
        // @note: Nothing here atm...
    }

    LighthouseResult.prototype.init = function () {
        this.$el.on('dispose-control', this.proxy(this.dispose))
    }

    LighthouseResult.prototype.dispose = function () {
        this.$el.off('dispose-control', this.proxy(this.dispose));
        this.$el.removeData('oc.lighthouseresult');
        this.$el = null
        this.options = null
        BaseProto.dispose.call(this)
    }

    // Deprecated
    LighthouseResult.prototype.unbind = function () {
        this.dispose()
    }

    // FIELD ACTIVITYLOG PLUGIN DEFINITION
    // ============================

    var old = $.fn.fieldLighthouseResult

    $.fn.fieldLighthouseResult = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result
        this.each(function () {
            var $this = $(this)
            var data = $this.data('oc.lighthouseresult')
            var options = $.extend({}, LighthouseResult.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('oc.lighthouseresult', (data = new LighthouseResult(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.fieldLighthouseResult.Constructor = LighthouseResult

    // FIELD ACTIVITYLOG NO CONFLICT
    // =================

    $.fn.fieldLighthouseResult.noConflict = function () {
        $.fn.fieldLighthouseResult = old
        return this
    }

    // FIELD ACTIVITYLOG DATA-API
    // ===============

    $(document).render(function () {
        $('[data-control="field-lighthouseresult"]').fieldLighthouseResult()
    });

}(window.jQuery);


+ function ($) {
    "use strict";

    var LighthouseResultWidget = function () {

        this.clickLighthouseResultRecord = function (recordId, triggerEl) {
            $(triggerEl).popup({
                handler: 'onViewLighthouseReportDetails',
                size: 'huge',
                extraData: {
                    'jaxwilkoHugoReportId': recordId,
                }
            });
        }
    }

    $.oc.lighthouseResultWidget = new LighthouseResultWidget;
}(window.jQuery);
