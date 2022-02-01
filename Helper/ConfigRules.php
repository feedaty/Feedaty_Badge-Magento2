<?php

namespace Feedaty\Badge\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Feedaty\Badge\Helper\ConfigSetting;

class ConfigRules extends AbstractHelper
{
    /**
     * @var \Feedaty\Badge\Helper\ConfigSetting
     */
    protected $helperConfigSetting;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_dir;

    /**
     * ConfigRules constructor.
     * @param \Feedaty\Badge\Helper\ConfigSetting $helperConfigSetting
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     */
    public function __construct(
        ConfigSetting $helperConfigSetting,
        \Magento\Framework\Filesystem\DirectoryList $dir
    ) {
        $this->helperConfigSetting = $helperConfigSetting;
        $this->_dir = $dir;
    }
	 /**
     * Get data from preferences configuration tab
     *
     * @param array $data
     * @return array
     */
    public function getPreferencesConfig($data){
        return $this->helperConfigSetting->getPreferencesConfig($data);
    }

    public function getSendOrderConfig($data){
        return $this->helperConfigSetting->getSendOrderConfig($data);
    }

    public function getSnippetConfig($data){
        return $this->helperConfigSetting->getSnippetConfig($data);
    }

    /**
     * Get Feedaty Merch Code
     *
     * @return array
     */
    public function getFeedatyCode() {
        $feedaty_code = $this->getPreferencesConfig('feedaty_code');

        return $feedaty_code;
     }

    /**
     * Get Feedaty Secret Code
     *
     * @return array
     */
    public function getFeedatySecret() {
        $feedaty_code = $this->getPreferencesConfig('feedaty_secret');

        return $feedaty_code;
    }

    /**
     * @return array
     */
    public function getCreateReviewEnabled() {
        $feedaty_code = $this->getPreferencesConfig('create_reviews_enabled');

        return $feedaty_code;
    }

    public function getReviewForceDefaultStore()
    {
        $force_store = $this->getPreferencesConfig('create_reviews_force_default_store');
        return $force_store;
    }

    public function getReviewDefaultStore()
    {
        $default_store = $this->getPreferencesConfig('create_reviews_default_store');
        return $default_store;
    }


    public function getSendOrderStatus()
    {
        $orderStatus = $this->getSendOrderConfig('sendorder');
        return $orderStatus;
    }

    public function getSnippetEnabled()
    {
        $snippetEnabled = $this->getSnippetConfig('snippet_prod_enabled');
        return $snippetEnabled;
    }

}
