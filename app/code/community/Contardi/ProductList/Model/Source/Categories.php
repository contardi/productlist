<?php

/**
 * Bizcommerce
 * @category   Bizcommerce
 * @package    Contardi_ProductList
 * @copyright  Copyright (c) 2018.
 */
class Contardi_ProductList_Model_Source_Categories
{
    public function toOptionArray()
    {
        $rootCategoryIds = array();
        $categories = array();

        $parentCategories = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('level', 1)
            ->addAttributeToFilter('is_active', 1);

        foreach ($parentCategories as $rootCategory) {
            if ($rootCategory->getId() && !in_array($rootCategory->getId(), $rootCategoryIds)) {
                array_push($rootCategoryIds, $rootCategory);
            }
        }

        $categories[] = array(
            'value' => '',
            'label' => Mage::helper('adminhtml')->__('-- Please Select --')
        );

        foreach ($rootCategoryIds as $rootCategory) {

            $categories[] = array(
                'value' => $rootCategory->getId(),
                'label' => $rootCategory->getName()
            );

            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('url_key')
                ->addAttributeToFilter('is_active', 1)
                ->addAttributeToFilter('parent_id', $rootCategory->getId())
                ->load();


            foreach ($collection as $category) {
                $categories[] = array(
                    'value' => $category->getId(),
                    'label' => '-- ' . $category->getName()
                );

                $childCollection = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('url_key')
                    ->addAttributeToFilter('is_active', 1)
                    ->addAttributeToFilter('parent_id', $category->getId())
                    ->load();

                foreach ($childCollection as $childCategory) {
                    $categories[] = array(
                        'value' => $childCategory->getId(),
                        'label' => '---- ' . $childCategory->getName()
                    );
                }
            }

        }
        return $categories;
    }
}
