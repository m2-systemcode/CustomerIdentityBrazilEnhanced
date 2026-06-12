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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\Eav\Entity\Attribute;

use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Framework\DataObject;
use SystemCode\CustomerIdentityBrazilEnhanced\Model\Customer\PersonTypeAttributeFilter;

class BackendPlugin
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
     * Execute around validate.
     *
     * @param AbstractBackend $subject
     * @param callable $proceed
     * @param DataObject $object
     */
    public function aroundValidate(AbstractBackend $subject, callable $proceed, DataObject $object)
    {
        $attribute = $subject->getAttribute();

        if ($attribute === null
            || $attribute->getEntityType()->getEntityTypeCode() !== 'customer'
            || !$this->personTypeAttributeFilter->isActive()
        ) {
            return $proceed($object);
        }

        if ($this->personTypeAttributeFilter->isDenied((string) $attribute->getAttributeCode(), $object)) {
            return true;
        }

        return $proceed($object);
    }
}
