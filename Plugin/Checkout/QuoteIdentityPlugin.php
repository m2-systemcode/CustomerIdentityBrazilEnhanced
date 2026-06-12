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

use Magento\Checkout\Api\Data\ShippingInformationExtensionInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Quote\Api\Data\CartInterface;
use SystemCode\CustomerIdentityBrazil\Api\Data\QuoteIdentityDataInterface;
use SystemCode\CustomerIdentityBrazil\Model\Checkout\QuoteIdentity;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class QuoteIdentityPlugin
{
    private const array FIELDS = ['cpf', 'cnpj', 'socialname', 'tradename'];

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
     * Execute after apply extension attributes to quote.
     *
     * @param QuoteIdentity $subject
     * @param mixed $result
     * @param CartInterface $quote
     * @param ShippingInformationExtensionInterface $extensionAttributes
     * @return mixed
     */
    public function afterApplyExtensionAttributesToQuote(
        QuoteIdentity $subject,
        mixed $result,
        CartInterface $quote,
        ShippingInformationExtensionInterface $extensionAttributes
    ): mixed {
        foreach (self::FIELDS as $field) {
            $value = $this->readExtensionAttribute($extensionAttributes, $field);
            if ($value !== null && $value !== '') {
                $quote->setData($field, $value);
            }
        }

        return $result;
    }

    /**
     * Execute after apply identity data to quote.
     *
     * @param QuoteIdentity $subject
     * @param mixed $result
     * @param CartInterface $quote
     * @param QuoteIdentityDataInterface $identity
     * @return mixed
     */
    public function afterApplyIdentityDataToQuote(
        QuoteIdentity $subject,
        mixed $result,
        CartInterface $quote,
        QuoteIdentityDataInterface $identity
    ): mixed {
        $this->setQuoteValue($quote, 'cpf', $identity->getCpf());
        $this->setQuoteValue($quote, 'cnpj', $identity->getCnpj());
        $this->setQuoteValue($quote, 'socialname', $identity->getSocialname());
        $this->setQuoteValue($quote, 'tradename', $identity->getTradename());
        $this->applyTaxvatCopy($quote, $identity);

        return $result;
    }

    /**
     * Execute after apply quote to customer.
     *
     * @param QuoteIdentity $subject
     * @param mixed $result
     * @param CartInterface $quote
     * @param CustomerInterface $customer
     * @return mixed
     */
    public function afterApplyQuoteToCustomer(
        QuoteIdentity $subject,
        mixed $result,
        CartInterface $quote,
        CustomerInterface $customer
    ): mixed {
        foreach (self::FIELDS as $field) {
            $value = $quote->getData($field);
            if ($value !== null && $value !== '') {
                $customer->setCustomAttribute($field, (string) $value);
            }
        }

        if ($this->config->isCopyTaxvatEnabled()) {
            $taxvat = $quote->getData('customer_taxvat');
            if ($taxvat !== null && $taxvat !== '') {
                $customer->setTaxvat((string) $taxvat);
            }
        }

        return $result;
    }

    /**
     * Execute after apply customer to quote.
     *
     * @param QuoteIdentity $subject
     * @param mixed $result
     * @param CartInterface $quote
     * @param CustomerInterface $customer
     * @return mixed
     */
    public function afterApplyCustomerToQuote(
        QuoteIdentity $subject,
        mixed $result,
        CartInterface $quote,
        CustomerInterface $customer
    ): mixed {
        foreach (self::FIELDS as $field) {
            $value = $customer->getCustomAttribute($field)?->getValue();
            if ($value !== null && $value !== '') {
                $quote->setData($field, (string) $value);
            }
        }

        if ($this->config->isCopyTaxvatEnabled()) {
            $personType = (string) ($customer->getCustomAttribute('person_type')?->getValue() ?? 'cpf');
            $document = $personType === 'cnpj'
                ? (string) ($customer->getCustomAttribute('cnpj')?->getValue() ?? '')
                : (string) ($customer->getCustomAttribute('cpf')?->getValue() ?? '');

            if ($document !== '') {
                $quote->setData('customer_taxvat', $document);
            }
        }

        return $result;
    }

    /**
     * Apply taxvat copy.
     *
     * @param CartInterface $quote
     * @param QuoteIdentityDataInterface $identity
     * @return void
     */
    private function applyTaxvatCopy(CartInterface $quote, QuoteIdentityDataInterface $identity): void
    {
        if (!$this->config->isCopyTaxvatEnabled()) {
            return;
        }

        $personType = $identity->getPersonType() ?: 'cpf';
        $document = $personType === 'cnpj' ? $identity->getCnpj() : $identity->getCpf();

        if ($document !== null && $document !== '') {
            $quote->setData('customer_taxvat', $document);
        }
    }

    /**
     * Set quote value.
     *
     * @param CartInterface $quote
     * @param string $field
     * @param string $value
     * @return void
     */
    private function setQuoteValue(CartInterface $quote, string $field, ?string $value): void
    {
        if ($value !== null && $value !== '') {
            $quote->setData($field, $value);
        }
    }

    /**
     * Handle read extension attribute.
     *
     * @param ShippingInformationExtensionInterface $extensionAttributes
     * @param string $field
     * @return ? string
     */
    private function readExtensionAttribute(
        ShippingInformationExtensionInterface $extensionAttributes,
        string $field
    ): ?string {
        return match ($field) {
            'cpf' => $extensionAttributes->getCpf(),
            'cnpj' => $extensionAttributes->getCnpj(),
            'socialname' => $extensionAttributes->getSocialname(),
            'tradename' => $extensionAttributes->getTradename(),
            default => null,
        };
    }
}
