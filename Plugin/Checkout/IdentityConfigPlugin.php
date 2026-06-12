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

use SystemCode\CustomerIdentityBrazil\Model\Checkout\IdentityConfig;
use SystemCode\CustomerIdentityBrazilEnhanced\Api\ConfigInterface;
use SystemCode\CustomerIdentityBrazil\ViewModel\CustomerIdentity as BaseCustomerIdentity;

class IdentityConfigPlugin
{
    private const array FIELD_CODES = ['cpf', 'cnpj', 'rg', 'ie', 'socialname', 'tradename'];

    /**
     * Initialize dependencies.
     *
     * @param ConfigInterface $config
     * @param BaseCustomerIdentity $customerIdentity
     */
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly BaseCustomerIdentity $customerIdentity
    ) {
    }

    /**
     * Execute after to array.
     *
     * @param IdentityConfig $subject
     * @param array $result
     * @return array
     */
    public function afterToArray(IdentityConfig $subject, array $result): array
    {
        if (!($result['isActive'] ?? false)) {
            return $result;
        }

        $result['isEnhanced'] = true;
        $result['taxvatIndividual'] = false;
        $result['taxvatCorporation'] = false;
        $result['taxvatIndividualRequired'] = false;
        $result['taxvatCorporationRequired'] = false;
        unset($result['fields']['taxvat']);

        foreach (self::FIELD_CODES as $code) {
            if (!$this->config->isFieldVisible($code)) {
                unset($result['fields'][$code]);
                continue;
            }

            $result['fields'][$code] = [
                'required' => $this->config->isFieldRequired($code),
            ];
        }

        if ($this->config->isCopyFirstnameEnabled()) {
            unset($result['fields']['socialname']);
        }

        if ($this->config->isCopyLastnameEnabled()) {
            unset($result['fields']['tradename']);
        }

        $result['changeFirstnameLabel'] = false;
        $result['changeLastnameLabel'] = false;
        $result['copyFirstnameEnabled'] = $this->config->isCopyFirstnameEnabled();
        $result['copyLastnameEnabled'] = $this->config->isCopyLastnameEnabled();
        $result['copyFirstnameRequired'] = $this->config->isCopyFirstnameEnabled()
            && $this->config->isFieldVisible('socialname')
            && $this->config->isFieldRequired('socialname');
        $result['copyLastnameRequired'] = $this->config->isCopyLastnameEnabled()
            && $this->config->isFieldVisible('tradename')
            && $this->config->isFieldRequired('tradename');
        $result['copyTaxvatEnabled'] = $this->config->isCopyTaxvatEnabled();
        $result['cpfVisible'] = $this->config->isFieldVisible('cpf');
        $result['cpfRequired'] = $this->config->isFieldRequired('cpf');
        $result['cnpjVisible'] = $this->config->isFieldVisible('cnpj');
        $result['cnpjRequired'] = $this->config->isFieldRequired('cnpj');
        $result['socialnameVisible'] = $this->config->isFieldVisible('socialname')
            && !$this->config->isCopyFirstnameEnabled();
        $result['socialnameRequired'] = $this->config->isFieldRequired('socialname');
        $result['tradenameVisible'] = $this->config->isFieldVisible('tradename')
            && !$this->config->isCopyLastnameEnabled();
        $result['tradenameRequired'] = $this->config->isFieldRequired('tradename');
        $result['rgVisible'] = $this->config->isFieldVisible('rg');
        $result['rgRequired'] = $this->config->isFieldRequired('rg');
        $result['ieVisible'] = $this->config->isFieldVisible('ie');
        $result['ieRequired'] = $this->config->isFieldRequired('ie');
        $result['showIndividualSection'] = $this->config->isFieldVisible('cpf')
            || $this->config->isFieldVisible('rg');
        $result['showCorporationSection'] = $this->config->isFieldVisible('cnpj')
            || $this->config->isFieldVisible('ie')
            || ($this->config->isFieldVisible('socialname') && !$this->config->isCopyFirstnameEnabled())
            || ($this->config->isFieldVisible('tradename') && !$this->config->isCopyLastnameEnabled());
        $result['identityData'] = array_merge(
            $result['identityData'] ?? [],
            $this->getEnhancedIdentityData()
        );

            return $result;
    }

    /**
     * Retrieve enhanced identity data.
     *
     * @return array
     */
    private function getEnhancedIdentityData(): array
    {
        $data = [];

        foreach (self::FIELD_CODES as $code) {
            $data[$code] = $this->customerIdentity->getCustomerValue($code);
        }

        return $data;
    }
}
