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
        $this->moduleList = $moduleList;
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
        $moduleInfo = $this->moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }


}
