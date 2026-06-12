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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Model\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use SystemCode\CustomerIdentityBrazil\Api\ConfigInterface as BaseConfigInterface;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;

class PersonTypeAttributeFilter
{
    private const array INDIVIDUAL_ATTRIBUTES = [
        'cpf',
        'rg',
    ];

    private const array CORPORATION_ATTRIBUTES = [
        'cnpj',
        'ie',
        'socialname',
        'tradename',
    ];

    /**
     * Initialize dependencies.
     *
     * @param BaseConfigInterface $baseConfig
     * @param ConfigInterface $config
     * @param RequestInterface $request
     */
    public function __construct(
        private readonly BaseConfigInterface $baseConfig,
        private readonly ConfigInterface $config,
        private readonly RequestInterface $request
    ) {
    }

    /**
     * Check whether active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->baseConfig->isActive();
    }

    /**
     * Retrieve denied attribute codes.
     *
     * @param DataObject $entity
     * @return array
     */
    public function getDeniedAttributeCodes(DataObject $entity): array
    {
        if (!$this->isActive()) {
            return [];
        }

        $personType = $this->resolvePersonType($entity);
        $denied = $personType === 'cnpj'
            ? self::INDIVIDUAL_ATTRIBUTES
            : self::CORPORATION_ATTRIBUTES;

        $denied[] = 'taxvat';

        if ($personType === 'cnpj' && $this->config->isCopyFirstnameEnabled()) {
            $denied[] = 'socialname';
        }

        if ($personType === 'cnpj' && $this->config->isCopyLastnameEnabled()) {
            $denied[] = 'tradename';
        }

        return array_values(array_unique($denied));
    }

    /**
     * Check whether denied.
     *
     * @param string $attributeCode
     * @param DataObject $entity
     * @return bool
     */
    public function isDenied(string $attributeCode, DataObject $entity): bool
    {
        return in_array($attributeCode, $this->getDeniedAttributeCodes($entity), true);
    }

    /**
     * Resolve person type.
     *
     * @param DataObject $entity
     * @return string
     */
    private function resolvePersonType(DataObject $entity): string
    {
        $requestValue = $this->request->getParam('person_type');

        if (is_string($requestValue) && $requestValue !== '') {
            return $this->normalizePersonType($requestValue);
        }

        $entityValue = $entity->getData('person_type');

        if (is_string($entityValue) && $entityValue !== '') {
            return $this->normalizePersonType($entityValue);
        }

        return 'cpf';
    }

    /**
     * Handle normalize person type.
     *
     * @param string $personType
     * @return string
     */
    private function normalizePersonType(string $personType): string
    {
        return in_array($personType, ['cpf', 'cnpj'], true) ? $personType : 'cpf';
    }
}
