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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\Customer;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use SystemCode\CustomerIdentityBrazil\Api\ConfigInterface as BaseConfigInterface;
use SystemCode\CustomerIdentityBrazil\Model\Customer\SaveValidator;
use SystemCode\CustomerIdentityBrazil\Model\Customer\UniqueValidator;
use SystemCode\CustomerIdentityBrazil\Model\Validator\Cnpj;
use SystemCode\CustomerIdentityBrazil\Model\Validator\Cpf;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class SaveValidatorPlugin
{
    private const array ATTRIBUTE_CODES = [
        'person_type',
        'rg',
        'ie',
        'cpf',
        'cnpj',
        'socialname',
        'tradename',
    ];

    /**
     * Initialize dependencies.
     *
     * @param BaseConfigInterface $baseConfig
     * @param ConfigInterface $config
     * @param RequestInterface $request
     * @param Cpf $cpfValidator
     * @param Cnpj $cnpjValidator
     * @param UniqueValidator $uniqueValidator
     */
    public function __construct(
        private readonly BaseConfigInterface $baseConfig,
        private readonly ConfigInterface $config,
        private readonly RequestInterface $request,
        private readonly Cpf $cpfValidator,
        private readonly Cnpj $cnpjValidator,
        private readonly UniqueValidator $uniqueValidator
    ) {
    }

    /**
     * Execute around execute.
     *
     * @param SaveValidator $subject
     * @param callable $proceed
     * @param CustomerInterface $customer
     * @return void
     */
    public function aroundExecute(SaveValidator $subject, callable $proceed, CustomerInterface $customer): void
    {
        if (!$this->baseConfig->isActive()) {
            $proceed($customer);
            return;
        }

        $params = $this->resolveParams($customer);
        $personType = (string) ($params['person_type'] ?? 'cpf');

        if (!in_array($personType, ['cpf', 'cnpj'], true)) {
            $personType = 'cpf';
        }

        $this->setCustomerAttribute($customer, 'person_type', $personType);
        $this->validate($customer, $params, $personType);

        $groupId = $personType === 'cpf'
            ? $this->baseConfig->getIndividualGroupId()
            : $this->baseConfig->getCorporationGroupId();

        if ($groupId !== null) {
            $customer->setGroupId($groupId);
        }
    }

    /**
     * Validate .
     *
     * @param CustomerInterface $customer
     * @param array $params
     * @param string $personType
     * @return void
     */
    private function validate(CustomerInterface $customer, array $params, string $personType): void
    {
        if ($personType === 'cpf') {
            $this->validateDocument($customer, $params, 'cpf', $this->cpfValidator->isValid(...));
            $this->validateAttribute($customer, $params, 'rg');
            $this->setCustomerAttribute($customer, 'cnpj', null);
            $this->setCustomerAttribute($customer, 'socialname', null);
            $this->setCustomerAttribute($customer, 'tradename', null);
            $this->setCustomerAttribute($customer, 'ie', null);
        } else {
            $this->validateDocument($customer, $params, 'cnpj', $this->cnpjValidator->isValid(...));
            $this->validateAttribute($customer, $params, 'ie');

            if (!$this->config->isCopyFirstnameEnabled()) {
                $this->validateAttribute($customer, $params, 'socialname');
            } else {
                $this->validateNativeNameCopy($customer, $params, 'firstname', 'socialname');
            }

            if (!$this->config->isCopyLastnameEnabled()) {
                $this->validateAttribute($customer, $params, 'tradename');
            } else {
                $this->validateNativeNameCopy($customer, $params, 'lastname', 'tradename');
            }

            $this->setCustomerAttribute($customer, 'cpf', null);
            $this->setCustomerAttribute($customer, 'rg', null);
        }

        if ($this->config->isCopyTaxvatEnabled()) {
            $document = $personType === 'cpf'
            ? $this->getValue($customer, $params, 'cpf')
            : $this->getValue($customer, $params, 'cnpj');
            $customer->setTaxvat($document);
        }
    }

    /**
     * Validate attribute.
     *
     * @param CustomerInterface $customer
     * @param array $params
     * @param string $attributeCode
     * @return void
     */
    private function validateAttribute(CustomerInterface $customer, array $params, string $attributeCode): void
    {
        if (!$this->config->isFieldVisible($attributeCode)) {
            return;
        }

        $value = $this->getValue($customer, $params, $attributeCode);

        if ($this->config->isFieldRequired($attributeCode) && $value === '') {
            throw new LocalizedException(__('%1 is a required field.', strtoupper($attributeCode)));
        }

        if ($value !== '' && $this->config->isFieldUnique($attributeCode)) {
            $this->uniqueValidator->assertUnique($customer, $attributeCode, $value, strtoupper($attributeCode));
        }

        $this->setCustomerAttribute($customer, $attributeCode, $value !== '' ? $value : null);
    }

    /**
     * Validate document.
     *
     * @param CustomerInterface $customer
     * @param array $params
     * @param string $attributeCode
     * @param callable $formatValidator
     * @return void
     */
    private function validateDocument(
        CustomerInterface $customer,
        array $params,
        string $attributeCode,
        callable $formatValidator
    ): void {
        if (!$this->config->isFieldVisible($attributeCode)) {
            return;
        }

        $value = $this->getValue($customer, $params, $attributeCode);

        if ($this->config->isFieldRequired($attributeCode) && $value === '') {
            throw new LocalizedException(__('%1 is a required field.', strtoupper($attributeCode)));
        }

        if ($value !== '' && !$formatValidator($value)) {
            throw new LocalizedException(__('%1 is invalid.', strtoupper($attributeCode)));
        }

        if ($value !== '' && $this->config->isFieldUnique($attributeCode)) {
            $this->uniqueValidator->assertUnique($customer, $attributeCode, $value, strtoupper($attributeCode));
        }

        $this->setCustomerAttribute($customer, $attributeCode, $value !== '' ? $value : null);
    }

    /**
     * Validate native name copy.
     *
     * @param CustomerInterface $customer
     * @param array $params
     * @param string $nativeField
     * @param string $attributeCode
     * @return void
     */
    private function validateNativeNameCopy(
        CustomerInterface $customer,
        array $params,
        string $nativeField,
        string $attributeCode
    ): void {
        if (!$this->config->isFieldVisible($attributeCode)) {
            return;
        }

        $value = (string) ($params[$nativeField] ?? '');

        if ($this->config->isFieldRequired($attributeCode) && $value === '') {
            throw new LocalizedException(__('%1 is a required field.', strtoupper($attributeCode)));
        }

        $this->setCustomerAttribute($customer, $attributeCode, $value !== '' ? $value : null);
    }

    /**
     * Resolve params.
     *
     * @param CustomerInterface $customer
     * @return array
     */
    private function resolveParams(CustomerInterface $customer): array
    {
        $params = [];

        foreach (self::ATTRIBUTE_CODES as $attributeCode) {
            $requestValue = $this->request->getParam($attributeCode);

            if ($requestValue !== null && $requestValue !== '') {
                $params[$attributeCode] = $requestValue;
                continue;
            }

            $attributeValue = $this->getCustomAttributeValue($customer, $attributeCode);

            if ($attributeValue !== '') {
                $params[$attributeCode] = $attributeValue;
            }
        }

        foreach (['firstname', 'lastname'] as $nativeField) {
            $requestValue = $this->request->getParam($nativeField);

            if ($requestValue !== null && $requestValue !== '') {
                $params[$nativeField] = $requestValue;
                continue;
            }

            $nativeValue = $nativeField === 'firstname'
                ? $customer->getFirstname()
                : $customer->getLastname();

            if ($nativeValue !== null && $nativeValue !== '') {
                $params[$nativeField] = $nativeValue;
            }
        }

        return $params;
    }

    /**
     * Retrieve value.
     *
     * @param CustomerInterface $customer
     * @param array $params
     * @param string $attributeCode
     * @return string
     */
    private function getValue(CustomerInterface $customer, array $params, string $attributeCode): string
    {
        if (isset($params[$attributeCode]) && $params[$attributeCode] !== '') {
            return (string) $params[$attributeCode];
        }

        return $this->getCustomAttributeValue($customer, $attributeCode);
    }

    /**
     * Retrieve custom attribute value.
     *
     * @param CustomerInterface $customer
     * @param string $attributeCode
     * @return string
     */
    private function getCustomAttributeValue(CustomerInterface $customer, string $attributeCode): string
    {
        $attribute = $customer->getCustomAttribute($attributeCode);

        if ($attribute === null || $attribute->getValue() === null) {
            return '';
        }

        return (string) $attribute->getValue();
    }

    /**
     * Set customer attribute.
     *
     * @param CustomerInterface $customer
     * @param string $attributeCode
     * @param string $value
     * @return void
     */
    private function setCustomerAttribute(CustomerInterface $customer, string $attributeCode, ?string $value): void
    {
        $customer->setCustomAttribute($attributeCode, $value !== null && $value !== '' ? $value : null);
    }
}
