<?php

class Magestore_Ajaxcart_Helper_Product_Compare extends Mage_Catalog_Helper_Product_Compare
{
    /**
     * Retrive add to cart url
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getAddToCartUrl($product)
    {
        if ($product->getTypeInstance(true)->hasRequiredOptions($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = array();
            }
            $additional['_query']['options'] = 'cart';
            if ($this->hasProductUrl($product)) {
                $product->getUrlModel()->getUrl($product, array());
            }
        }
        
        $beforeCompareUrl = Mage::getSingleton('catalog/session')->getBeforeCompareUrl();
        $params = array(
            'product'=>$product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->getEncodedUrl($beforeCompareUrl)
        );
        
        if (in_array($product->getTypeId(), array('grouped', 'configurable'))) {
            if (!isset($params['_query'])) {
                $params['_query'] = array();
            }
            $params['_query']['options'] = 'cart';
        }
        
        return $this->_getUrl('checkout/cart/add', $params);
    }
    
    /**
     * Check Product has URL
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function hasProductUrl($product) {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }
        return false;
    }
}
