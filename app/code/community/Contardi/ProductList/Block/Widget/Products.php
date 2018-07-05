<?php

/**
 * Interface
 *
 * @category   Contardi
 * @package    Contardi_ProductList
 * @author     Thiago Contardi (thiago@contardi.com.br)
 * @copyright  Copyright (c) 2018
 */
class Contardi_ProductList_Block_Widget_Products
    extends Mage_Catalog_Block_Product_Abstract
    implements Mage_Widget_Block_Interface
{

    /**
     * Product Collection
     *
     * @var Mage_Catalog_Model_Resource_Product_Collection
     */
    protected $_productCollection = null;
    protected $_helperCatalog;

    public function __construct()
    {
        parent::__construct();
        $template = ($this->getData('template')) ? $this->getData('template') : 'contardi/productlist/widget/products.phtml';
        $this->setTemplate($template);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $nameInLayout = __CLASS__;
        $parentBlock = $this->getParentBlock();
        if ($parentBlock) {
            $nameInLayout = $parentBlock->getNameInLayout();
        }
        
        $tagCache = Mage::getSingleton('core/session')->getProductsTagCache();
        $tagCache = ($tagCache) ? $tagCache : 'none';

        $utmSource = Mage::getSingleton('core/session')->getUtmSource();
        $utmSource = ($utmSource) ? $utmSource : 'direct';

        return array(
            __CLASS__,
            'WIDGET_' . $this->getData('identificator') . '_' . Mage::app()->getStore()->isCurrentlySecure(),
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'template' => $this->getTemplate(),
            $tagCache,
            $utmSource,
            $nameInLayout
        );

    }

    /**
     * Set cache data
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(array('cache_lifetime' => 86400));
        $this->addCacheTag(array(
            Mage_Catalog_Model_Product::CACHE_TAG,
            Mage_Core_Model_Store_Group::CACHE_TAG
        ));
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    protected function loadProducts()
    {
        $_collection = $this->_getProductCollection();

        Mage::dispatchEvent('catalog_block_product_list_collection', array(
            'collection' => $_collection
        ));

        return $_collection;
    }

    protected function getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            try {
                Mage::getModel('catalog/product')->getCollection()->clear();
                /** @var Mage_Catalog_Model_Resource_Product_Collection _productCollection */
                $this->_productCollection = Mage::getModel('catalog/product')->getCollection()
                    ->addStoreFilter()
                    ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
                    ->addMinimalPrice()
                    ->addFinalPrice()
                    ->addTaxPercents();
                Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_productCollection);

                if ($this->getData('categoria')) {
                    $categoryIds = array_filter(explode(',', $this->getData('category')));
                    if (!empty($categoryIds)) {
                        $this->_productCollection->joinField('category_id',
                            'catalog/category_product',
                            'category_id',
                            'product_id=entity_id',
                            'at_category_id.category_id IN ' . '(' . implode (',', $categoryIds) . ')',
                            'inner'
                        );
                    }
                }

                $attribute = $this->getData('attribute');
                if ($attribute) {
                    $this->_productCollection->addAttributeToFilter($attribute, 1);
                }

                if (!$this->getData('show_out_of_stock')) {
                    $this->_productCollection->joinField(
                        'stock_status',
                        'cataloginventory/stock_status',
                        'stock_status',
                        'product_id=entity_id',
                        array(
                            'stock_status' => Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK,
                            'website_id' => Mage::app()->getWebsite()->getWebsiteId()
                        )
                    );
                }
                $this->_productCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                $this->_productCollection->getSelect()->group('entity_id');
                $this->_productCollection->getSelect()->limit($this->getData('limit'));


            } catch (Exception $e) {
                Mage::logException($e);
            }
        }

        return $this->_productCollection;
    }

    /**
     *
     * @return Contardi_ProductList_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('contardi_productlist');
    }

}
