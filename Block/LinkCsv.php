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
        $store_id = (int) $this->_request->getParam('store', 0);

        $this->setElement($element);
        $url = $this->getUrl('feedatyexport') . "store/" . $store_id;

        $html = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Esporta Ordini')
            ->setOnClick("document.location.href = '".$url."'")
            ->toHtml();

        return $html;
    }
}
