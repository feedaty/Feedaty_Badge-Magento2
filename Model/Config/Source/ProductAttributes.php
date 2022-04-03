<?php

namespace Feedaty\Badge\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class ProductAttributes implements OptionSourceInterface
{

    /**
     * @var CollectionFactory
     */
    private $attributes;


    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    )
    {
         $this->attributes = $collectionFactory;
    }

    /**
     * Exclude incompatible product attributes from the mapping.
     * @var array
     */
    private $excluded = [
        'name',
        'sku',
        'price'
    ];
    /**
    *
    * @return $order_array
    */
    public function toOptionArray()
    {
        $attributes = $this->attributes
            ->create()
            ->addVisibleFilter();

        $attributeArray = [];

        foreach ($attributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (!in_array($attributeCode, $this->excluded)) {
                $attributeArray[] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $attributeCode,
                ];
            }
        }
        return $attributeArray;
    }
}
