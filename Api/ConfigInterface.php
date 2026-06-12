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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Api;

interface ConfigInterface
{
    public const string XML_PATH_COPY_TAXVAT = 'customeridentitybrazil/enhanced_sync/copy_taxvat';
    public const string XML_PATH_COPY_FIRSTNAME = 'customeridentitybrazil/enhanced_sync/copy_firstname';
    public const string XML_PATH_COPY_LASTNAME = 'customeridentitybrazil/enhanced_sync/copy_lastname';
    public const string XML_PATH_FIELD_VISIBILITY = 'customeridentitybrazil/%s/%s_show';
    public const string IDENTITY_CPF_COMPONENT = 'SystemCode_CustomerIdentityBrazilEnhanced/js/checkout/identity-cpf';
    public const string IDENTITY_CNPJ_COMPONENT = 'SystemCode_CustomerIdentityBrazilEnhanced/js/checkout/identity-cnpj';
    public const array FIELD_GROUPS = [
        'cpf' => 'individual',
        'rg' => 'individual',
        'cnpj' => 'corporation',
        'ie' => 'corporation',
        'socialname' => 'corporation',
        'tradename' => 'corporation',
    ];

    /**
     * Check whether copy taxvat enabled.
     *
     * @return bool
     */
    public function isCopyTaxvatEnabled(): bool;

    /**
     * Check whether copy firstname enabled.
     *
     * @return bool
     */
    public function isCopyFirstnameEnabled(): bool;

    /**
     * Check whether copy lastname enabled.
     *
     * @return bool
     */
    public function isCopyLastnameEnabled(): bool;

    /**
     * Retrieve field visibility.
     *
     * @param string $attributeCode
     * @return string
     */
    public function getFieldVisibility(string $attributeCode): string;

    /**
     * Check whether field visible.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isFieldVisible(string $attributeCode): bool;

    /**
     * Check whether field required.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isFieldRequired(string $attributeCode): bool;

    /**
     * Check whether field unique.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isFieldUnique(string $attributeCode): bool;
}
