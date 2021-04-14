<?php
namespace Feedaty\Badge\Block;


use \Magento\Framework\View\Element\Template;
use \Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\View\Element\Template\Context;
use Feedaty\Badge\Helper\ConfigRules;
use Magento\Catalog\Helper\Data;


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
        Data $catalogData
    )
    {
        $this->_configRules = $configRules;
        $this->_webservice = $webservice;
        $this->_catalogData = $catalogData;
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
     * @return mixed
     */
    public function getFeedatyCode(){
       return  $this->_configRules->getFeedatyCode();
    }

    /**
     * @return \Magento\Catalog\Model\Product|null
     */
    public function getProduct(){
       return $this->_catalogData->getProduct();
    }

    /**
     * @return string
     */
    public function getProductImage(){
        return $this->getProduct()->getImage();
    }

    /**
     * @return string
     */
    public function getProductName(){
        return $this->getProduct()->getName();
    }

    /**
     * @return mixed
     */
    public function getProductDescription(){
        return $this->getProduct()->getDescription();
    }

    /**
     * @return string
     */
    public function getProductSku(){
        return $this->getProduct()->getSku();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMediaUrl(){
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
}
