<?php
/**
 * Bizcommerce
 * @category   Bizcommerce
 * @package    Contardi_ProductList
 * @copyright  Copyright (c) 2018
 */
class Contardi_ProductList_Model_Source_Boolean
{

    public function toOptionArray()
    {
        $attributes = Mage::getModel('catalog/product')->getResource()
            ->loadAllAttributes()
            ->getAttributesByCode();

        $result = array();
        $result[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            if ($attribute->getId() && in_array($attribute->getFrontendInput(), array('boolean'))) {
                $result[] = array(
                    'value' => $attribute->getAttributeCode(),
                    'label' => $attribute->getFrontend()->getLabel(),
                );
            }
        }
        return $result;
    }
}
