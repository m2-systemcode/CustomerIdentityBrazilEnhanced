define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('systemCode.changePersonTypeEnhanced', {
        options: {
            individualSelector: '[data-role=type-individual]',
            corporateSelector: '[data-role=type-corporation]',
            individualContainer: '[data-container=type-individual]',
            corporateContainer: '[data-container=type-corporation]',
            copyFirstnameRequired: false,
            copyLastnameRequired: false,
            individualFields: ['#cpf', '#rg'],
            corporateFields: ['#cnpj', '#ie', '#socialname', '#tradename'],
            fieldRequirements: {}
        },

        _create: function () {
            this._initialNativeNameRequired = {
                firstname: $('#firstname').hasClass('required-entry'),
                lastname: $('#lastname').hasClass('required-entry')
            };

            $(document).on(
                'change',
                this.options.individualSelector + ',' + this.options.corporateSelector,
                $.proxy(this._checkChoice, this)
            );
            this._checkChoice();
        },

        _isCorporate: function () {
            return $(this.options.corporateSelector).is(':checked');
        },

        _checkChoice: function () {
            if (this._isCorporate()) {
                this._showCorporate();
            } else {
                this._showIndividual();
            }
        },

        _showIndividual: function () {
            $(this.options.individualContainer).show();
            $(this.options.corporateContainer).hide();
            this._toggleFieldRequirements(this.options.individualFields, true);
            this._toggleFieldRequirements(this.options.corporateFields, false);
            this._updateNativeNameFields(false);
        },

        _showCorporate: function () {
            $(this.options.corporateContainer).show();
            $(this.options.individualContainer).hide();
            this._toggleFieldRequirements(this.options.corporateFields, true);
            this._toggleFieldRequirements(this.options.individualFields, false);
            this._updateNativeNameFields(true);
        },

        _isFieldRequired: function (selector) {
            return !!this.options.fieldRequirements[selector];
        },

        _getFieldWrapper: function ($input) {
            var $wrapper = $input.closest('.brazil-identity-field');

            if ($wrapper.length) {
                return $wrapper;
            }

            return $input.closest('.field');
        },

        _toggleFieldRequirements: function (selectors, enableForSection) {
            selectors.forEach(function (selector) {
                var $input = $(selector),
                    isRequired = enableForSection && this._isFieldRequired(selector),
                    validateRules;

                if (!$input.length) {
                    return;
                }

                this._getFieldWrapper($input).toggleClass('required _required', isRequired);
                $input.toggleClass('required-entry', isRequired);
                $input.attr('aria-required', isRequired ? 'true' : 'false');

                if ($input.hasClass('validate-cpf')) {
                    validateRules = isRequired
                        ? '{required:true, \'validate-cpf\':true}'
                        : '{\'validate-cpf\':true}';
                    $input.attr('data-validate', validateRules);
                } else if ($input.hasClass('validate-cnpj')) {
                    validateRules = isRequired
                        ? '{required:true, \'validate-cnpj\':true}'
                        : '{\'validate-cnpj\':true}';
                    $input.attr('data-validate', validateRules);
                } else if (isRequired) {
                    $input.attr('data-validate', '{required:true}');
                } else {
                    $input.removeAttr('data-validate');
                }
            }.bind(this));
        },

        _updateNativeNameFields: function (isCorporate) {
            var requireFirstname = isCorporate && this.options.copyFirstnameRequired
                    ? true
                    : this._initialNativeNameRequired.firstname,
                requireLastname = isCorporate && this.options.copyLastnameRequired
                    ? true
                    : this._initialNativeNameRequired.lastname;

            $('.field-name-firstname').toggleClass('required _required', requireFirstname);
            $('#firstname').toggleClass('required-entry', requireFirstname);
            $('.field-name-lastname').toggleClass('required _required', requireLastname);
            $('#lastname').toggleClass('required-entry', requireLastname);
        }
    });

    return $.systemCode.changePersonTypeEnhanced;
});
