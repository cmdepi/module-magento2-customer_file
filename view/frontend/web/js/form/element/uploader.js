/**
 *
 * @description Uploader JS
 *
 * @author Bina Commerce      <https://www.binacommerce.com>
 * @author C. M. de Picciotto <cmdepicciotto@binacommerce.com>
 *
 */
define([
    'Magento_Ui/js/form/element/file-uploader'
], function (Element) {
    'use strict';

    return Element.extend({
        /**
         *
         * @type {Object}
         *
         */
        defaults: {
            template      : 'Bina_CustomerFile/form/element/uploader',
            previewTmpl   : 'Bina_CustomerFile/form/element/uploader/preview',
            fieldScope    : 'customer',
            fieldAttribute: ''
        },

        /**
         *
         * Initialize
         *
         * @returns {void}
         *
         * @public
         *
         */
        initialize: function () {
            /**
             *
             * @note Initialize parent logic
             *
             */
            this._super();

            /**
             *
             * @note Init field name
             *
             */
            this.fieldName = this.fieldScope + '[' + this.fieldAttribute + ']';
        }
    });
});