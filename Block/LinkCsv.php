<?php
namespace Feedaty\Badge\Block;

use \Magento\Framework\Data\Form\Element\AbstractElement;

class LinkCsv extends \Magento\Config\Block\System\Config\Form\Field
{

    
    /**
    * @param $element
    * @return $html
    */
    protected function _getElementHtml(AbstractElement $element)
    {
        
        $this->setElement($element);
        $url = $this->getUrl('feedatyexport'); 

        $html = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                    ->setType('button')
                    ->setClass('scalable')
                    ->setLabel('Salva ora')
                    ->setOnClick("document.location.href = '".$url."'")
                    ->toHtml();
        return $html;
    }
}