<?php
/**
 * NOTICE OF LICENSE
 *
 * @category  SystemCode
 * @package   Systemcode_CustomerIdentityBrazilEnhanced
 * @author    Eduardo Diogo Dias <contato@systemcode.com.br>
 * @copyright System Code LTDA - ME
 * @license   http://opensource.org/licenses/osl-3.0.php
 */
declare(strict_types=1);

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor;
use SystemCode\CustomerIdentityBrazil\Api\ConfigInterface as BaseConfigInterface;
use SystemCode\CustomerIdentityBrazil\Model\Checkout\IdentityFieldLayoutBuilder;
use SystemCode\CustomerIdentityBrazil\ViewModel\CustomerIdentity;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class LayoutProcessorPlugin
{
    /**
     * Initialize dependencies.
     *
     * @param BaseConfigInterface $baseConfig
     * @param ConfigInterface $config
     * @param CustomerIdentity $customerIdentity
     * @param IdentityFieldLayoutBuilder $fieldLayoutBuilder
     */
    public function __construct(
        private readonly BaseConfigInterface $baseConfig,
        private readonly ConfigInterface $config,
        private readonly CustomerIdentity $customerIdentity,
        private readonly IdentityFieldLayoutBuilder $fieldLayoutBuilder
    ) {
    }

    /**
     * Execute after process.
     *
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(LayoutProcessor $subject, array $jsLayout): array
    {
        if (!$this->baseConfig->isActive() || !$this->customerIdentity->shouldShowCheckoutIdentityFields()) {
            return $jsLayout;
        }

        $fieldset = &$jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];

        if (!is_array($fieldset)) {
            return $jsLayout;
        }

        unset(
            $fieldset['customer-identity-brazil-taxvat'],
            $fieldset['customer-identity-brazil-cpf'],
            $fieldset['customer-identity-brazil-cnpj'],
            $fieldset['customer-identity-brazil-socialname'],
            $fieldset['customer-identity-brazil-tradename'],
            $fieldset['customer-identity-brazil-fields']
        );

            $lastnameSortOrder = (int) ($fieldset['lastname']['sortOrder'] ?? 40);

        if ($this->config->isFieldVisible('cpf')) {
            $fieldset['customer-identity-brazil-cpf'] = $this->fieldLayoutBuilder->buildTextField(
                'cpf',
                'CPF',
                $lastnameSortOrder + 2,
                ConfigInterface::IDENTITY_CPF_COMPONENT,
                ['cpf'],
                true,
                $this->config->isFieldRequired('cpf')
            );
        }

        if ($this->config->isFieldVisible('socialname') && !$this->config->isCopyFirstnameEnabled()) {
            $fieldset['customer-identity-brazil-socialname'] = $this->fieldLayoutBuilder->buildTextField(
                'socialname',
                'Social Name',
                $lastnameSortOrder + 3,
                BaseConfigInterface::IDENTITY_FIELD_COMPONENT,
                ['cnpj'],
                true,
                $this->config->isFieldRequired('socialname')
            );
        }

        if ($this->config->isFieldVisible('tradename') && !$this->config->isCopyLastnameEnabled()) {
            $fieldset['customer-identity-brazil-tradename'] = $this->fieldLayoutBuilder->buildTextField(
                'tradename',
                'Trade Name',
                $lastnameSortOrder + 4,
                BaseConfigInterface::IDENTITY_FIELD_COMPONENT,
                ['cnpj'],
                true,
                $this->config->isFieldRequired('tradename')
            );
        }

        if ($this->config->isFieldVisible('cnpj')) {
            $fieldset['customer-identity-brazil-cnpj'] = $this->fieldLayoutBuilder->buildTextField(
                'cnpj',
                'CNPJ',
                $lastnameSortOrder + 5,
                ConfigInterface::IDENTITY_CNPJ_COMPONENT,
                ['cnpj'],
                true,
                $this->config->isFieldRequired('cnpj')
            );
        }

        if (isset($fieldset['customer-identity-brazil-rg'])) {
            $fieldset['customer-identity-brazil-rg']['sortOrder'] = $lastnameSortOrder + 6;
        }

        if (isset($fieldset['customer-identity-brazil-ie'])) {
            $fieldset['customer-identity-brazil-ie']['sortOrder'] = $lastnameSortOrder + 7;
        }

            return $jsLayout;
    }
}
