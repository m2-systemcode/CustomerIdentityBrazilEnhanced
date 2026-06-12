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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\Quote;

use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;

class QuoteManagementPlugin
{
    private const array ORDER_FIELDS = ['cpf', 'cnpj', 'socialname', 'tradename'];

    /**
     * Initialize dependencies.
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * Execute after submit.
     *
     * @param QuoteManagement $subject
     * @param mixed $order
     * @param mixed $quote
     * @param array $orderData
     * @return mixed
     */
    public function afterSubmit(QuoteManagement $subject, mixed $order, mixed $quote, array $orderData = []): mixed
    {
        if (!$order || !$quote) {
            return $order;
        }

        $hasData = false;

        foreach (self::ORDER_FIELDS as $field) {
            $value = $quote->getData($field);
            if ($value === null || $value === '') {
                continue;
            }

            $order->setData($field, (string) $value);
            $hasData = true;
        }

        if ($hasData) {
            $this->orderRepository->save($order);
        }

        return $order;
    }
}
