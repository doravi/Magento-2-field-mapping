<?php
/**
 * update customer fields with mapped fields from Gigya.
 * See Magento prepared methods at: app/code/Magento/Customer/Model/Data/Customer.php
 * helpful magento guide for creating custom fields:
 * https://maxyek.wordpress.com/2015/10/22/building-magento-2-extension-customergrid/comment-page-1/
 *
 * For mapping existing Magento custom fields to gigya fields:
 * use: $customer->setCustomAttribute($attributeCode, $attributeValue);
 * or: $customer->setCustomAttributes(array());
 * located at: /lib/internal/Magento/Framework/Api/AbstractExtensibleObject
 */

namespace Gigya\Gigya_FieldMapping\Model;

use Gigya\CmsStarterKit\fieldMapping;

class M2FieldsUpdater extends fieldMapping\CmsUpdater
{

    public $_logger;

    public function __construct(
        \Gigya\CmsStarterKit\User\GigyaUser $gigyaAccount, $mappingFilePath)
    {
        parent::__construct($gigyaAccount, $mappingFilePath);
    }

    public function callCmsHook() {
        return true;
    }

    public function setGigyaLogger($logger) {
        $this->_logger = $logger;
    }

    /**
     * @param Magento/Customer $account
     */
    public function setAccountValues(&$account) {
        foreach ($this->getGigyaMapping() as $gigyaName => $confs) {
            /** @var Gigya\CmsStarterKit\fieldMapping\ConfItem $conf */
            $value = parent::getValueFromGigyaAccount($gigyaName); // e.g: loginProvider = facebook
            // if no value found, log and skip field
            if (is_null($value)) {
                $this->_logger->info( __FUNCTION__ . ": Value for {$gigyaName} not found in gigya user object. check your field mapping configuration");
                continue;
            }
            foreach ($confs as $conf) {
                $mageKey = $conf->getCmsName();     // e.g: mageKey = prefix
                $value   = $this->castValue($value, $conf);
                $account->setData($mageKey, $value);
            }
        }
    }

    public function saveCmsAccount(&$cmsAccount, $cmsAccountSaver) {
        if ($cmsAccountSaver) {
            $cmsAccountSaver->gigyaUpdateCustomer($cmsAccount);
        }
    }

}