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

namespace SystemCode\CustomerIdentityBrazilEnhanced\Plugin\CustomerIdentity;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SystemCode\CustomerIdentityBrazil\Model\CustomerIdentity\PersonType;

class PersonTypePlugin
{
    /**
     * Initialize dependencies.
     *
     * @param Session $customerSession
     * @param CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        private readonly Session $customerSession,
        private readonly CustomerRepositoryInterface $customerRepository
    ) {
    }

    /**
     * Execute after resolve.
     *
     * @param PersonType $subject
     * @param string $result
     * @return string
     */
    public function afterResolve(PersonType $subject, string $result): string
    {
        if ($result === 'cnpj') {
            return $result;
        }

        $customer = $this->getLoggedInCustomer();

        return $customer !== null && $this->hasCorporationAttributes($customer) ? 'cnpj' : $result;
    }

    /**
     * Check whether entity has corporation attributes.
     *
     * @param CustomerInterface $customer
     * @return bool
     */
    private function hasCorporationAttributes(CustomerInterface $customer): bool
    {
        foreach (['cnpj', 'ie', 'socialname', 'tradename'] as $attributeCode) {
            $value = $customer->getCustomAttribute($attributeCode)?->getValue();
            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve logged in customer.
     *
     * @return ? CustomerInterface
     */
    private function getLoggedInCustomer(): ?CustomerInterface
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }

        try {
            return $this->customerRepository->getById((int) $this->customerSession->getCustomerId());
        } catch (NoSuchEntityException | LocalizedException) {
            return null;
        }
    }
}
