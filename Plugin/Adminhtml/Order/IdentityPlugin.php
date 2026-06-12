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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\Adminhtml\Order;

use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use SystemCode\CustomerIdentityBrazil\Api\ConfigInterface as BaseConfigInterface;
use SystemCode\CustomerIdentityBrazil\ViewModel\Adminhtml\Order\Identity;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class IdentityPlugin
{
    /**
     * Initialize dependencies.
     *
     * @param Registry $registry
     * @param BaseConfigInterface $baseConfig
     * @param ConfigInterface $config
     */
    public function __construct(
        private readonly Registry $registry,
        private readonly BaseConfigInterface $baseConfig,
        private readonly ConfigInterface $config
    ) {
    }

    /**
     * Execute around get identity rows.
     *
     * @param Identity $subject
     * @param callable $proceed
     * @return array
     */
    public function aroundGetIdentityRows(Identity $subject, callable $proceed): array
    {
        if (!$this->baseConfig->isActive()) {
            return $proceed();
        }

        $order = $this->getOrder();
        if (!$order instanceof Order) {
            return $proceed();
        }

        return $this->buildRows($order);
    }

    /**
     * Handle build rows.
     *
     * @param Order $order
     * @return array
     */
    private function buildRows(Order $order): array
    {
        $personType = (string) $order->getData('person_type');
        $taxvat = (string) $order->getCustomerTaxvat();
        $rows = [];

        if ($personType !== '') {
            $rows[] = [
                'label' => __('Person Type'),
                'value' => $personType === 'cnpj' ? __('Corporation') : __('Individual Person'),
            ];
        }

        if ($personType === 'cnpj') {
            $this->appendRow($rows, __('Social Name'), $order->getData('socialname'));
            $this->appendRow($rows, __('Trade Name'), $order->getData('tradename'));
            $this->appendRow($rows, __('CNPJ'), $this->resolveDocumentValue($order, 'cnpj', $taxvat));
            $this->appendRow($rows, __('IE'), $order->getData('ie'));
        } else {
            $this->appendRow($rows, __('CPF'), $this->resolveDocumentValue($order, 'cpf', $taxvat));
            $this->appendRow($rows, __('RG'), $order->getData('rg'));
        }

        if ($this->shouldShowTaxvat($order, $taxvat)) {
            $rows[] = [
            'label' => __('Tax/VAT Number'),
            'value' => $taxvat,
            ];
        }

        return $rows;
    }

    /**
     * Handle should show taxvat.
     *
     * @param Order $order
     * @param string $taxvat
     * @return bool
     */
    private function shouldShowTaxvat(Order $order, string $taxvat): bool
    {
        if ($taxvat === '' || !$this->config->isCopyTaxvatEnabled()) {
            return false;
        }

        $document = (string) $order->getData(
            ((string) $order->getData('person_type')) === 'cnpj' ? 'cnpj' : 'cpf'
        );

            return $document === '' || $document !== $taxvat;
    }

    /**
     * Resolve document value.
     *
     * @param Order $order
     * @param string $fieldCode
     * @param string $taxvat
     * @return string
     */
    private function resolveDocumentValue(Order $order, string $fieldCode, string $taxvat): string
    {
        $value = (string) $order->getData($fieldCode);

        if ($value !== '') {
            return $value;
        }

        return $taxvat;
    }

    /**
     * Handle append row.
     *
     * @param array $rows
     * @param mixed $label
     * @param mixed $value
     * @return void
     */
    private function appendRow(array &$rows, $label, mixed $value): void
    {
        $value = (string) $value;
        if ($value === '') {
            return;
        }

        $rows[] = [
            'label' => $label,
            'value' => $value,
        ];
    }

    /**
     * Retrieve order.
     *
     * @return ? Order
     */
    private function getOrder(): ?Order
    {
        $order = $this->registry->registry('current_order')
            ?? $this->registry->registry('order');

        return $order instanceof Order ? $order : null;
    }
}
