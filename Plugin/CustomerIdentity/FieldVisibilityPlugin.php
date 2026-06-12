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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\CustomerIdentity;

use SystemCode\CustomerIdentityBrazil\Model\CustomerIdentity\FieldVisibility;
use SystemCode\CustomerIdentityBrazil\Model\CustomerIdentity\PersonType;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class FieldVisibilityPlugin
{
    private const array INDIVIDUAL_FIELDS = ['cpf', 'rg'];
    private const array CORPORATION_FIELDS = ['cnpj', 'ie', 'socialname', 'tradename'];

    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $config
     * @param PersonType $personType
     */
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly PersonType $personType
    ) {
    }

    /**
     * Execute after show individual section.
     *
     * @param FieldVisibility $subject
     * @param bool $result
     * @return bool
     */
    public function afterShowIndividualSection(FieldVisibility $subject, bool $result): bool
    {
        return $this->config->isFieldVisible('cpf') || $this->config->isFieldVisible('rg');
    }

    /**
     * Execute after show corporation section.
     *
     * @param FieldVisibility $subject
     * @param bool $result
     * @return bool
     */
    public function afterShowCorporationSection(FieldVisibility $subject, bool $result): bool
    {
        return $this->config->isFieldVisible('cnpj')
            || $this->config->isFieldVisible('ie')
            || ($this->config->isFieldVisible('socialname') && !$this->config->isCopyFirstnameEnabled())
            || ($this->config->isFieldVisible('tradename') && !$this->config->isCopyLastnameEnabled());
    }

    /**
     * Execute after is field visible.
     *
     * @param FieldVisibility $subject
     * @param bool $result
     * @param string $attributeCode
     * @return bool
     */
    public function afterIsFieldVisible(FieldVisibility $subject, bool $result, string $attributeCode): bool
    {
        if ($attributeCode === 'taxvat') {
            return false;
        }

        return $this->config->isFieldVisible($attributeCode);
    }

    /**
     * Execute after is field required.
     *
     * @param FieldVisibility $subject
     * @param bool $result
     * @param string $attributeCode
     * @return bool
     */
    public function afterIsFieldRequired(FieldVisibility $subject, bool $result, string $attributeCode): bool
    {
        if ($attributeCode === 'taxvat') {
            return false;
        }

        if (!$this->config->isFieldVisible($attributeCode) || !$this->config->isFieldRequired($attributeCode)) {
            return false;
        }

        $personType = $this->personType->resolve();

        if (in_array($attributeCode, self::INDIVIDUAL_FIELDS, true)) {
            return $personType === 'cpf';
        }

        if (in_array($attributeCode, self::CORPORATION_FIELDS, true)) {
            return $personType === 'cnpj';
        }

        return false;
    }

    /**
     * Execute after is field unique.
     *
     * @param FieldVisibility $subject
     * @param bool $result
     * @param string $attributeCode
     * @return bool
     */
    public function afterIsFieldUnique(FieldVisibility $subject, bool $result, string $attributeCode): bool
    {
        if ($attributeCode === 'taxvat') {
            return false;
        }

        return $this->config->isFieldUnique($attributeCode);
    }
}
