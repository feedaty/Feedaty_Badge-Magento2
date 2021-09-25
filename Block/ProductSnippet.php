<?php
namespace Feedaty\Badge\Block;

use Feedaty\Badge\Helper\ConfigRules;
use Feedaty\Badge\Model\Config\Source\WebService;
use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;

class ProductSnippet extends Template
{
    /**
     * @var WebService
     */
    protected $_webservice;

    /**
     * @var Data
     */
    protected $_catalogData;

    /**
     * @var ConfigRules
     */
    protected $_configRules;

    /**
     * @var StoreManagerInterface
     */
    private $storeConfig;

    /**
     * @var CurrencyFactory
     */
    private $currencyCode;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonResultFactory;

    /**
     * ProductSnippet constructor.
     * @param Context $context
     * @param WebService $webservice
     * @param ConfigRules $configRules
     * @param Data $catalogData
     */
    public function __construct(
        Context $context,
        WebService $webservice,
        ConfigRules $configRules,
        Data $catalogData,
        StoreManagerInterface $storeConfig,
        CurrencyFactory $currencyFactory
    ) {
        $this->_configRules = $configRules;
        $this->_webservice = $webservice;
        $this->_catalogData = $catalogData;
        $this->storeConfig = $storeConfig;
        $this->currencyCode = $currencyFactory->create();
        parent::__construct($context);
    }

    /**
     * @param $feedaty_code
     * @param $id
     * @return mixed
     */
    public function retriveInformationsProduct($feedaty_code, $id)
    {
        return $this->_webservice->retriveInformationsProduct($feedaty_code, $id);
    }

    /**
     * @return array
     */
    public function getFeedatyCode()
    {
        return  $this->_configRules->getFeedatyCode();
    }

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct()
    {
        return $this->_catalogData->getProduct();
    }

    /**
     * @return string
     */
    public function getProductImage()
    {
        return $this->getProduct()->getImage();
    }

    /**
     * @return string
     */
    public function getProductName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        return $this->getProduct()->getProductUrl();
    }


    /**
     * @return string
     */
    public function getProductIsSalable()
    {
        return $this->getProduct()->getIsSalable();
    }

    /**
     * @return mixed
     */
    public function getProductDescription()
    {
        return $this->getProduct()->getDescription();
    }

    /**
     * @return string
     */
    public function getProductSku()
    {
        return $this->getProduct()->getSku();
    }

    /**
     * @return string
     */
    public function getProductFinalPrice()
    {
        return $this->getProduct()->getFinalPrice();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        $currencyCode = $this->storeConfig->getStore()->getCurrentCurrencyCode();
       // $currency = $this->currencyCode->load($currencyCode);
        //return $currency->getCurrencySymbol();
        return $currencyCode;
    }
}
