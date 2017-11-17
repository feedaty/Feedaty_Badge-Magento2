<?php
namespace Feedaty\Badge\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;


    /*
    * Constructor
    *
    */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
    }

    /*
    * Execute
    * 
    */
    public function execute() {
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store_id = (int) $this->_request->getParam('store', 0);
        $scope_store = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        //$fromDate = date("Y-m-d H:i:s", strtotime("-3 months"));

        $orders = $objectManager->create('\Magento\Sales\Model\Order')->getCollection()
            ->addFieldToFilter('status', $this->scopeConfig->getValue('feedaty_global/feedaty_sendorder/sendorder', $scope_store))
            ->addFieldToFilter('store_id', $store_id);

        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');


        $heading = [
            __('Order ID'),
            __('UserID'),
            __('E-mail'),
            __('Date'),
            __('Product ID'),
            __('Extra'),
            __('Product Url'),
            __('Product Image'),
            __('Platform'),           
        ];

        $outputFile = "FeedatyExport". date('Ymd_His').".csv";
        $handle = fopen($outputFile, 'w');
        fputcsv($handle, $heading);

        foreach ($orders as $order) {

            $objproducts = $order->getAllItems();

            foreach ($objproducts as $itemId => $item) {
                unset($tmp);
                if (!$item->getParentItem()) {

                    $fd_oProduct = $objectManager->create('\Magento\Catalog\Model\Product')->load((int) $item->getProductId());

                    $tmp['Id'] = $item->getProductId();

                    $tmp['Url'] = $fd_oProduct->getUrlModel()->getUrl($fd_oProduct);

                    if ($fd_oProduct->getImage() != "no_selection") 
                    { 
                        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
                        $tmp['ImageUrl'] = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $fd_oProduct->getImage();
                    }
                    else 
                    {
                        $tmp['ImageUrl'] = "";
                    }

                    $tmp['Name'] = $item->getName();
                    $tmp['Brand'] = $item->getBrand();
                    if ($tmp['Brand'] === null) $tmp['Brand']  = "";

                    $row = [
                        $order->getId(),
                        $order->getBillingAddress()->getEmail(), 
                        $order->getBillingAddress()->getEmail(), 
                        $order->getCreatedAt(), 
                        $item->getProductId(), 
                        str_replace('"','""',$tmp['Name']), 
                        $tmp['Url'], 
                        $tmp['ImageUrl'], 
                        "Magento".$productMetadata->getVersion()."CSV"
                    ];
                    fputcsv($handle, $row);
                }
            }
        }
        $this->downloadCsv($outputFile);
    }

    private function downloadCsv($file) {
        if (file_exists($file)) {
            //set headers
            header('Content-Description: File Transfer');
            header('Content-Type: application/csv');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
        }
    }
}
