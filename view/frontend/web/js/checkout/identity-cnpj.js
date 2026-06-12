define([
    'SystemCode_CustomerIdentityBrazil/js/checkout/identity-field',
    'SystemCode_CustomerIdentityBrazil/js/mask-helper'
], function (IdentityField, maskHelper) {
    'use strict';

    return IdentityField.extend({
        defaults: {
            fieldCode: 'cnpj',
            personTypes: ['cnpj']
        },

        initialize: function () {
            this._super();
            this.validation = this.validation || {};
            this.validation['validate-cnpj'] = true;

            return this;
        },

        applyMask: function (element) {
            if (this.maskApplied) {
                return;
            }

            maskHelper.apply(element, 'cnpj');
            this.maskApplied = true;
        }
    });
});
