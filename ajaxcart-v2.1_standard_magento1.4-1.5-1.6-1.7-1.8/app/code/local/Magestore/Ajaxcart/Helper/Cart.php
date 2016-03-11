<?php

class Magestore_Ajaxcart_Helper_Cart extends Mage_Checkout_Helper_Cart
{
	/**
     * Retrieve url for add product to cart
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  string
     */
    public function getAddUrl($product, $additional = array())
    {
        $continueUrl    = Mage::helper('core')->urlEncode($this->getCurrentUrl());
        $urlParamName   = Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED;

        $routeParams = array(
            $urlParamName   => $continueUrl,
            'product'       => $product->getEntityId()
        );

        if (!empty($additional)) {
            $routeParams = array_merge($routeParams, $additional);
        }

        if ($product->hasUrlDataObject()) {
            $routeParams['_store'] = $product->getUrlDataObject()->getStoreId();
            $routeParams['_store_to_url'] = true;
        }

        if ($this->_getRequest()->getRouteName() == 'checkout'
            && $this->_getRequest()->getControllerName() == 'cart') {
            $routeParams['in_cart'] = 1;
        }
        
        if (in_array($product->getTypeId(), array('grouped', 'configurable'))
            && $this->getFullActionName() != 'catalog_product_view'
        ) {
            if (!isset($routeParams['_query'])) {
                $routeParams['_query'] = array();
            }
            $routeParams['_query']['options'] = 'cart';
        }
        
        return $this->_getUrl('checkout/cart/add', $routeParams);
    }
    
    public function getFullActionName($delimiter='_') {
        $request = Mage::app()->getRequest();
        return $request->getRequestedRouteName().$delimiter.
            $request->getRequestedControllerName().$delimiter.
            $request->getRequestedActionName();
    }
}
