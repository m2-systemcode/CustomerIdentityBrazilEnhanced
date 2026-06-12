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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\Eav\Validator;

use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Validator\Attribute\Data;
use Magento\Framework\Model\AbstractModel;
use SystemCode\CustomerIdentityBrazilEnhanced\Model\Customer\PersonTypeAttributeFilter;

class AttributeDataPlugin
{
    /**
     * Initialize dependencies.
     *
     * @param PersonTypeAttributeFilter $personTypeAttributeFilter
     */
    public function __construct(
        private readonly PersonTypeAttributeFilter $personTypeAttributeFilter
    ) {
    }

    /**
     * Execute before is valid.
     *
     * @param Data $subject
     * @param mixed $entity
     * @return array
     */
    public function beforeIsValid(Data $subject, $entity): array
    {
        if (!$entity instanceof AbstractModel || !$this->personTypeAttributeFilter->isActive()) {
            return [$entity];
        }

        $resource = $entity->getResource();

        if (!$resource instanceof AbstractEntity
            || $resource->getEntityType()->getEntityTypeCode() !== 'customer'
        ) {
            return [$entity];
        }

        $deniedAttributes = $this->personTypeAttributeFilter->getDeniedAttributeCodes($entity);

        if ($deniedAttributes !== []) {
            $subject->setDeniedAttributesList($deniedAttributes);
        }

        return [$entity];
    }
}
