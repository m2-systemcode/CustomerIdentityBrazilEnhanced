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

namespace SystemCode\CustomerIdentityBrazilEnhanced\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class CustomerIdentity implements ArgumentInterface
{
    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * Check whether copy firstname enabled.
     *
     * @return bool
     */
    public function isCopyFirstnameEnabled(): bool
    {
        return $this->config->isCopyFirstnameEnabled();
    }

    /**
     * Check whether copy lastname enabled.
     *
     * @return bool
     */
    public function isCopyLastnameEnabled(): bool
    {
        return $this->config->isCopyLastnameEnabled();
    }

    /**
     * Handle should change firstname label.
     *
     * @return bool
     */
    public function shouldChangeFirstnameLabel(): bool
    {
        return false;
    }

    /**
     * Handle should change lastname label.
     *
     * @return bool
     */
    public function shouldChangeLastnameLabel(): bool
    {
        return false;
    }

    /**
     * Handle should require firstname for socialname.
     *
     * @param bool $isVisible
     * @param bool $isRequired
     * @return bool
     */
    public function shouldRequireFirstnameForSocialname(bool $isVisible, bool $isRequired): bool
    {
        return $this->config->isCopyFirstnameEnabled() && $isVisible && $isRequired;
    }

    /**
     * Handle should require lastname for tradename.
     *
     * @param bool $isVisible
     * @param bool $isRequired
     * @return bool
     */
    public function shouldRequireLastnameForTradename(bool $isVisible, bool $isRequired): bool
    {
        return $this->config->isCopyLastnameEnabled() && $isVisible && $isRequired;
    }

    /**
     * Check whether field required by config.
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isFieldRequiredByConfig(string $attributeCode): bool
    {
        return $this->config->isFieldVisible($attributeCode)
            && $this->config->isFieldRequired($attributeCode);
    }

    /**
     * Retrieve js field requirements.
     *
     * @return array
     */
    public function getJsFieldRequirements(): array
    {
        return [
            '#cpf' => $this->isFieldRequiredByConfig('cpf'),
            '#rg' => $this->isFieldRequiredByConfig('rg'),
            '#cnpj' => $this->isFieldRequiredByConfig('cnpj'),
            '#ie' => $this->isFieldRequiredByConfig('ie'),
            '#socialname' => $this->isFieldRequiredByConfig('socialname'),
            '#tradename' => $this->isFieldRequiredByConfig('tradename'),
        ];
    }
}
