<?php
namespace Feedaty\Badge\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Store\Model\StoreManagerInterface; 
use Feedaty\Badge\Helper\Data as DataHelp;

class StyleProduct implements ArrayInterface
{
    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var Feedaty\Badge\Helper\Data
    */
    protected $_dataHelper;

    /*
    * Constructor
    *
    */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        DataHelp $dataHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->_dataHelper = $dataHelper;
    }


    /**
    *
    * @return $return
    */
    public function toOptionArray()
    {
        $service = new WebService($this->scopeConfig, $this->storeManager,$this->_dataHelper);
		if (strlen($this->scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) == 0) return array();

        $data = $service->_get_FeedatyData();
		foreach ($data as $k=>$v) {
            if ($v['type'] == "product")
			    $return[] = array('value'=>$k,'label'=>' <img src="'.$v['thumb'].'"><br />');
		}
		
		
		return $return;
    }
}

