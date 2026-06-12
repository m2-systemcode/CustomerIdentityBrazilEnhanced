define([
    'mage/utils/wrapper',
    'mage/translate'
], function (wrapper, $t) {
    'use strict';

    function validateEnhanced(checkoutIdentity, config) {
        var data = checkoutIdentity.getData(),
            type = data.person_type || config.personType || 'cpf',
            fields = config.fields || {},
            isRequired = function (code) {
                if (config[code + 'Required'] !== undefined) {
                    return !!config[code + 'Required'];
                }

                return !!(fields[code] && fields[code].required);
            };

        if (type === 'cpf') {
            if (isRequired('cpf') && !data.cpf) {
                throw new Error($t('%1 is a required field.').replace('%1', 'CPF'));
            }

            if (isRequired('rg') && !data.rg) {
                throw new Error($t('%1 is a required field.').replace('%1', 'RG'));
            }

            return true;
        }

        if (isRequired('cnpj') && !data.cnpj) {
            throw new Error($t('%1 is a required field.').replace('%1', 'CNPJ'));
        }

        if (isRequired('ie') && !data.ie) {
            throw new Error($t('%1 is a required field.').replace('%1', 'IE'));
        }

        if (fields.socialname !== undefined && isRequired('socialname') && !data.socialname) {
            throw new Error($t('%1 is a required field.').replace('%1', 'Social Name'));
        }

        if (config.copyFirstnameRequired && !data.socialname) {
            throw new Error($t('%1 is a required field.').replace('%1', 'Social Name'));
        }

        if (fields.tradename !== undefined && isRequired('tradename') && !data.tradename) {
            throw new Error($t('%1 is a required field.').replace('%1', 'Trade Name'));
        }

        if (config.copyLastnameRequired && !data.tradename) {
            throw new Error($t('%1 is a required field.').replace('%1', 'Trade Name'));
        }

        return true;
    }

    return function (checkoutIdentity) {
        checkoutIdentity.validate = wrapper.wrap(
            checkoutIdentity.validate,
            function (originalValidate, config) {
                if (!config || !config.isActive || config.showIdentityFields === false || config.useAccountIdentity) {
                    return true;
                }

                if (config.isEnhanced) {
                    return validateEnhanced(checkoutIdentity, config);
                }

                return originalValidate(config);
            }
        );

        return checkoutIdentity;
    };
});
