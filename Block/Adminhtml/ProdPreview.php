<?php
namespace Feedaty\Badge\Block\Adminhtml;

use \Magento\Config\Block\System\Config\Form\Field;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Backend\Block\Template\Context;
use \Magento\Framework\Data\Form\Element\AbstractElement;
use Feedaty\Badge\Model\Config\Source\WebService;
use \Magento\Framework\ObjectManagerInterface;

class ProdPreview extends Field
{

    /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;

    /**
    * @var \Magento\Framework\ObjectManagerInterface
    */   
    protected $objectManager;


    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        WebService $fdservice,
        ObjectManagerInterface $objectManager,
        array $data = []
    )
    {
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->_fdservice = $fdservice;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

    /**
    * @return $html
    */
    protected function _getElementHtml(AbstractElement $element)
    {

        $store_scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $merchant = $this->_scopeConfig->getValue('feedaty_global/feedaty_preferences/feedaty_code', $store_scope);

        $style = $this->_scopeConfig->getValue('feedaty_badge_options/widget_products/product_style', $store_scope);
        $variant = $this->_scopeConfig->getValue('feedaty_badge_options/widget_products/prod_variant', $store_scope);

        $data = $merchant != null ? $this->_fdservice->getFeedatyData($merchant) : null;

        if($style == null || $data == null) return $this->noPreview();

        $widget = $data[$style];
        $name = $widget["name"];

        if (! array_key_exists($variant, $widget["variants"])) return $this->noPreview();

        $variant = $widget["variants"][$variant];

        $images = "";

        if(strlen($variant) > 0 && strlen($name) > 0 ) {

            if( $name == 'dynamic' || $name == 'dynvertical' || $name == 'productdynamic' ) {

                $sizes = array_keys($widget['thumbs']);

                foreach ($sizes as $size) {

                    $images .= "<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/".$name."_".$variant."_".$size."_it-IT.png\" />";
                }

            }   

            else {

                $images .= "<img src=\"https://widget.zoorate.com/widgets_v6/thumbs/" . $name . "_" . $variant . "_it-IT.png\" />";
            }

            $html = $images;

            return $html;
        }

        else {

            $html = "<p>Anteprima non disponibile</p>";
        }

        return $html;

    }

    private function noPreview() {

        return "<p>Anteprima non disponibile</p>";
    }
}