<?php
namespace Feedaty\Badge\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
    * @var \Magento\Framework\Module\ModuleListInterface
    */
    protected $moduleList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_moduleList = $moduleList;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }


    /**
     * @return array
     */
    public function getAllStoresIds()
    {
        $ids = [];
        $stores = $this->storeManager->getStores(false);

        foreach($stores as $store) {
            $ids[] = $store->getId();
        }

        return $ids;
    }

    public function getExtensionVersion() {
        $moduleCode = 'Feedaty_Badge';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    /**
    * Function Feedaty Debug - Save debug infoes in MageBasePath/var/log/feedaty.log
    *   @param $message - string - the debug message
    *   @param $severity - string - the message severity
    */
    public function feedatyDebug($message, $severity) {
        $message = json_encode($message);
        $fdwriter = new \Zend\Log\Writer\Stream(BP . '/var/log/feedaty.log');
        $fdlogger = new \Zend\Log\Logger();
        $fdlogger->addWriter($fdwriter);
        $fdlogger->info("\n".$severity."\n".$message."\n");

    }

        /**
     * Returns system configuration value
     *
     * @param $key
     * @param null $store
     * @return mixed
     */
    public function getConfigurationValue($key, $store = null)
    {
        return $this->scopeConfig->getValue(
            'example_section/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
