define([
    'uiRegistry',
    'SystemCode_CustomerIdentityBrazil/js/model/checkout-identity'
], function (registry, checkoutIdentity) {
    'use strict';

    return function (sync) {
        var originalSyncFromProviderData = sync.syncFromProviderData;

        sync.syncFromProviderData = function (identity) {
            originalSyncFromProviderData(identity);

            var config = window.checkoutConfig.customerIdentityBrazil || {},
                provider = registry.get('checkoutProvider'),
                shippingAddress;

            if (!config.isEnhanced || !provider) {
                return;
            }

            shippingAddress = provider.get('shippingAddress') || {};

            if (config.copyFirstnameEnabled && config.fields && config.fields.socialname === undefined) {
                checkoutIdentity.set('socialname', shippingAddress.firstname || '');
            }

            if (config.copyLastnameEnabled && config.fields && config.fields.tradename === undefined) {
                checkoutIdentity.set('tradename', shippingAddress.lastname || '');
            }

            if (config.copyTaxvatEnabled) {
                var personType = checkoutIdentity.get('person_type') || 'cpf',
                    document = personType === 'cnpj'
                        ? checkoutIdentity.get('cnpj')
                        : checkoutIdentity.get('cpf');

                if (document) {
                    checkoutIdentity.set('taxvat', document);
                }
            }
        };

        return sync;
    };
});
