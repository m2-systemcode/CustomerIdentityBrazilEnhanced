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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\Block\Widget;

use Magento\Customer\Block\Widget\Taxvat;
use SystemCode\CustomerIdentityBrazil\Api\ConfigInterface;

class TaxvatPlugin
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
     * Execute after is enabled.
     *
     * @param Taxvat $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsEnabled(Taxvat $subject, bool $result): bool
    {
        return $this->config->isActive() ? false : $result;
    }

    /**
     * Execute after is required.
     *
     * @param Taxvat $subject
     * @param bool $result
     * @return bool
     */
    public function afterIsRequired(Taxvat $subject, bool $result): bool
    {
        return $this->config->isActive() ? false : $result;
    }

    /**
     * Execute around to html.
     *
     * @param Taxvat $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundToHtml(Taxvat $subject, callable $proceed): string
    {
        return $this->config->isActive() ? '' : $proceed();
    }
}
