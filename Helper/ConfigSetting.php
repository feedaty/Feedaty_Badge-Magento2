<?php

namespace Feedaty\Badge\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class ConfigSetting extends AbstractHelper
{

	const XML_PATH_FEEDATY = 'feedaty_global/';

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

    /**
     * @param $code
     * @param null $storeId
     * @return mixed
     * Get Admin Preferences Configurations
     */
	public function getPreferencesConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_FEEDATY .'feedaty_preferences/'. $code, $storeId);
	}

    /**
     * @param $code
     * @param null $storeId
     * @return mixed
     * Get Admin Send Order Configurations
     */
    public function getSendOrderConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_FEEDATY .'feedaty_sendorder/'. $code, $storeId);
    }


    public function getSnippetConfig($code, $storeId = null)
    {
        return $this->getConfigValue('feedaty_microdata_options/snippet_products/'. $code, $storeId);
    }

    public function getExportConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_FEEDATY .'export/'. $code, $storeId);
    }

}
