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

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use SystemCode\CustomerIdentityBrazil\Api\ConfigInterface;

class ShippingInformationManagementPlugin
{
    private const array EXTENSION_FIELDS = ['cpf', 'cnpj', 'socialname', 'tradename'];

    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $config
     * @param CartRepositoryInterface $quoteRepository
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * Execute after save address information.
     *
     * @param ShippingInformationManagement $subject
     * @param mixed $result
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return mixed
     */
    public function afterSaveAddressInformation(
        ShippingInformationManagement $subject,
        mixed $result,
        int $cartId,
        ShippingInformationInterface $addressInformation
    ): mixed {
        if (!$this->config->isActive()) {
            return $result;
        }

        $values = $this->resolveValues($addressInformation);
        if ($values === []) {
            return $result;
        }

        try {
            $quote = $this->quoteRepository->getActive($cartId);
            foreach ($values as $field => $value) {
                $quote->setData($field, $value);
            }
            $this->quoteRepository->save($quote);
        } catch (LocalizedException) {
            return $result;
        }

        return $result;
    }

    /**
     * Resolve values.
     *
     * @param ShippingInformationInterface $addressInformation
     * @return array
     */
    private function resolveValues(ShippingInformationInterface $addressInformation): array
    {
        $values = [];
        $extensionAttributes = $addressInformation->getExtensionAttributes();

        if ($extensionAttributes !== null) {
            foreach (self::EXTENSION_FIELDS as $code) {
                $getter = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $code)));
                if (method_exists($extensionAttributes, $getter) && $extensionAttributes->$getter()) {
                    $values[$code] = (string) $extensionAttributes->$getter();
                }
            }
        }

        $content = $this->request->getContent();
        if ($content === '') {
            return $values;
        }

        $data = json_decode($content, true);
        if (!is_array($data)) {
            return $values;
        }

        $raw = $data['addressInformation']['extension_attributes'] ?? null;
        if (!is_array($raw)) {
            return $values;
        }

        foreach (self::EXTENSION_FIELDS as $code) {
            if (!empty($raw[$code])) {
                $values[$code] = (string) $raw[$code];
            }
        }

        return $values;
    }
}
