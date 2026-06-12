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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class Config implements ConfigInterface
{
    /**
     * Initialize dependencies.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * Check whether copy taxvat enabled.
     *
     * @return bool
     */
    public function isCopyTaxvatEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_COPY_TAXVAT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check whether copy firstname enabled.
     *
     * @return bool
     */
    public function isCopyFirstnameEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_COPY_FIRSTNAME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Check whether copy lastname enabled.
     *
     * @return bool
     */
    public function isCopyLastnameEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_COPY_LASTNAME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Retrieve field visibility.
     *
     * @param string $attributeCode
     * @return string
     */
    public function getFieldVisibility(string $attributeCode): string
    {
        $group = self::FIELD_GROUPS[$attributeCode] ?? null;
        if ($group === null) {
            return '';
        }

        return (string) $this->scopeConfig->getValue(
            sprintf(self::XML_PATH_FIELD_VISIBILITY, $group, $attributeCode),
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Check whether field visible.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isFieldVisible(string $attributeCode): bool
    {
        return in_array($this->getFieldVisibility($attributeCode), ['opt', 'req', 'optuni', 'requni', '1'], true);
    }

    /**
     * Check whether field required.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isFieldRequired(string $attributeCode): bool
    {
        return in_array($this->getFieldVisibility($attributeCode), ['req', 'requni'], true);
    }

    /**
     * Check whether field unique.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isFieldUnique(string $attributeCode): bool
    {
        return in_array($this->getFieldVisibility($attributeCode), ['optuni', 'requni'], true);
    }
}
