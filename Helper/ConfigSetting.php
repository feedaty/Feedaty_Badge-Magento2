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
     */
	public function getPreferencesConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_FEEDATY .'feedaty_preferences/'. $code, $storeId);
	}

}
