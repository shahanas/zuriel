<?php

class Magestore_Ajaxcart_Model_Observer {

    protected function _getCart() {
        return Mage::getSingleton('checkout/cart');
    }

    protected function _getSession() {
        return Mage::getSingleton('checkout/session');
    }

    protected function _getQuote() {
        return $this->_getCart()->getQuote();
    }

    protected function _initProduct($params) {
        $productId = (int) $params['product'];
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId())
                return $product;
        }
        return false;
    }

    public function catalogProductView($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $categoryId = (int) $action->getRequest()->getParam('category', false);
            $productId = (int) $action->getRequest()->getParam('id');
            $specifyOptions = $action->getRequest()->getParam('options');

            $viewHelper = Mage::helper('ajaxcart/product_view');

            $params = new Varien_Object();
            $params->setCategoryId($categoryId);
            $params->setSpecifyOptions($specifyOptions);

            $productHelper = Mage::helper('ajaxcart/product');

            $result = array();
            try {
                $product = $productHelper->initProduct($productId, $action, $params);
                if (!$product) {
                    $this->_getSession->addError($viewHelper->__('Product is not loaded'));
                }
                Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
                $viewHelper->initProductLayout($product, $action);
            } catch (Exception $e) {
                return $this;
            }
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $params = $action->getRequest()->getParams();
            try {
                $result['hasOptions'] = true;
                if (isset($params['groupmessage']) && $action->getLayout()->getMessagesBlock()) {
                    $action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
                    $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
                    $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                } else {
                    $this->_getSession()->getMessages(true);
                    $this->_getSession()->getEscapeMessages(true);
                }
                if ($typeBlock = Mage::getStoreConfig("ajaxcart/product/{$product->getTypeId()}"))
                    $productBlock = $action->getLayout()->createBlock($typeBlock, 'ajaxcart_product_view');
                else
                    $productBlock = $action->getLayout()->createBlock('ajaxcart/product_view', 'ajaxcart_product_view');
                if (!Mage::helper('ajaxcart')->getConfig('pre_load_ajax')) {
                    $result['optionjs'] = $productBlock->getJsItems();
                }
                $result['optionhtml'] = $productBlock->toHtml();
            } catch (Exception $e) {
                
            }
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartAdd($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $cart = $this->_getCart();
            $params = $action->getRequest()->getParams();
            $result = array();
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
                    $params['qty'] = $filter->filter($params['qty']);
                }
                $product = $this->_initProduct($params);
                if(isset($params['related_product']) && $params['related_product'])
                    $related = $params['related_product'];

                if ($product) {
                    $cart->addProduct($product, $params);
                    if (!empty($related))
                        $cart->addProductsByIds(explode(',', $related));
                    $cart->save();

                    $this->_getSession()->setCartWasUpdated(true);
                    Mage::dispatchEvent('checkout_cart_add_product_complete', array('product' => $product, 'request' => $action->getRequest(), 'response' => $action->getResponse()));

                    if (!$cart->getQuote()->getHasError()) {
                        $this->_getSession()->addSuccess(Mage::helper('checkout')->__('%s has been added to cart.', Mage::helper('core')->htmlEscape($product->getName())));
                    }
                } else {
                    $this->_getSession()->addError(Mage::helper('checkout')->__('Product not found!'));
                }
            } catch (Mage_Core_Exception $e) {
                $result['hasOptions'] = true;
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('checkout')->__('Cannot add item to shopping cart!'));
            }
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            if (isset($result['hasOptions'])) {
                $redirectUrl = Mage::getUrl('catalog/product/view', array(
                        'id' => $product->getId(),
                        'related_product' => isset($params['related_product']) ? $params['related_product'] : ''
                    ));
                $result['redirectUrl'] = $redirectUrl;
            } elseif (isset($params['groupmessage']) || isset($params['minicart']) || isset($params['ajaxlinks']) || isset($params['isajaxcartpage'])) {
                $action->loadLayout();
                try {
                    $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($product);           
                    $result['relatedhtml'] = $relatedBlock->toHtml();
                    if (isset($params['minicart']) && $params['minicart'] && $action->getLayout()->getBlock('cart_sidebar')) {
                        $result['miniCart'] = $action->getLayout()->getBlock('cart_sidebar')->toHtml();
                    }
                    if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                        $action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
                        $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
                        $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                    } else {
                        $this->_getSession()->getMessages(true);
                        $this->_getSession()->getEscapeMessages(true);
                    }
                    if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                        $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                    }
                    if (isset($params['isajaxcartpage']) && $params['isajaxcartpage']) {
                        $result['hasOptions'] = true;
                        $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
                    }
                } catch (Exception $e) {

                }
            }
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function compareProductAdd($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
       
        $action = $observer->getEvent()->getControllerAction();

        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $params = $action->getRequest()->getParams();
            $result = array();
            $productId = Mage::app()->getRequest()->getParam('product');

            if ($productId) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);

                if ($product->getId()/* && !$product->isSuper() */) {
                    try {
                        Mage::getSingleton('catalog/product_compare_list')->addProduct($product);
                        Mage::getSingleton('catalog/session')->addSuccess(
                            Mage::helper('catalog')->__('%s has been added to comparison list.', $product->getName())
                        );
                        Mage::dispatchEvent('catalog_product_compare_add_product', array('product' => $product));
                    } catch (Exception $e) {
                        Mage::getSingleton('catalog/session')->addError('error');
                    }
                }
                Mage::helper('catalog/product_compare')->calculate();

                try {
                    $action->loadLayout();
                    $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($product);
                    $result['relatedhtml'] = $relatedBlock->toHtml();
                    if (isset($params['minicompare']) && $params['minicompare'] && $action->getLayout()->getBlock('catalog.compare.sidebar')) {
                        $result['miniCompare'] = $action->getLayout()->getBlock('catalog.compare.sidebar')->toHtml();
                    }
                    if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                        $action->getLayout()->getMessagesBlock()->addMessages(Mage::getSingleton('catalog/session')->getMessages(true));
                        $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag(Mage::getSingleton('catalog/session')->getEscapeMessages(true));
                        $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                    } else {
                        Mage::getSingleton('catalog/session')->getMessages(true);
                        Mage::getSingleton('catalog/session')->getEscapeMessages(true);
                    }
                    if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                        $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                    }
                } catch (Exception $e) {
                    
                }
                $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));            
            }
        }
    }

    public function compareProductRemove($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $params = $action->getRequest()->getParams();
            $result = array();
            if ($productId = Mage::app()->getRequest()->getParam('product')) {
                $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load($productId);

                if ($product->getId()) {
                    /** @var $item Mage_Catalog_Model_Product_Compare_Item */
                    $item = Mage::getModel('catalog/product_compare_item');

                    $item->loadByProduct($product);

                    if ($item->getId()) {
                        $item->delete();
                        Mage::getSingleton('catalog/session')->addSuccess(
                            Mage::helper('catalog')->__('%s has been removed from comparison list.', $product->getName())
                        );
                        Mage::dispatchEvent('catalog_product_compare_remove_product', array('product' => $item));
                        Mage::helper('catalog/product_compare')->calculate();
                    }
                    try {
                        $action->loadLayout();
                        $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($product);
                        $result['relatedhtml'] = $relatedBlock->toHtml();
                        
                        if (isset($params['minicompare']) && $params['minicompare'] && $action->getLayout()->getBlock('catalog.compare.sidebar')) {
                            $result['miniCompare'] = $action->getLayout()->getBlock('catalog.compare.sidebar')->toHtml();
                        }
                        if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                            $action->getLayout()->getMessagesBlock()->addMessages(Mage::getSingleton('catalog/session')->getMessages(true));
                            $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag(Mage::getSingleton('catalog/session')->getEscapeMessages(true));
                            $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                        } else {
                            Mage::getSingleton('catalog/session')->getMessages(true);
                            Mage::getSingleton('catalog/session')->getEscapeMessages(true);
                        }
                        if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                            $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                        }
                    } catch (Exception $e) {
                        
                    }
                }
            }
            
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    public function compareProductClear($observer){
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $params = $action->getRequest()->getParams();
            $result = array();
            $items = Mage::getResourceModel('catalog/product_compare_item_collection');
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $items->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());
            } else {
                $items->setVisitorId(Mage::getSingleton('log/visitor')->getId());
            }
            /** @var $session Mage_Catalog_Model_Session */
            $session = Mage::getSingleton('catalog/session');
            try {
                $items->clear();
                $session->addSuccess(Mage::helper('catalog')->__('The comparison list was cleared.'));
                Mage::helper('catalog/product_compare')->calculate();
            } catch (Mage_Core_Exception $e) {
                $session->addError($e->getMessage());
            } catch (Exception $e) {
                $session->addException($e, $this->__('An error occurred while clearing comparison list.'));
            }
            try {
                $action->loadLayout();
                if (isset($params['minicompare']) && $params['minicompare'] && $action->getLayout()->getBlock('catalog.compare.sidebar')) {
                    $result['miniCompare'] = $action->getLayout()->getBlock('catalog.compare.sidebar')->toHtml();
                }
                if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                    $action->getLayout()->getMessagesBlock()->addMessages(Mage::getSingleton('catalog/session')->getMessages(true));
                    $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag(Mage::getSingleton('catalog/session')->getEscapeMessages(true));
                    $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                } else {
                    Mage::getSingleton('catalog/session')->getMessages(true);
                    Mage::getSingleton('catalog/session')->getEscapeMessages(true);
                }
                if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                    $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                }
            } catch (Exception $e) {
                
            }
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

        }
    }

    protected function _getWishlist()
    {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }

        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('wishlist/session')->addError($e->getMessage());
        } catch (Exception $e) {
            return false;
        }

        return $wishlist;
    }
    
    public function wishlistProductAdd($observer){
         if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $params = $action->getRequest()->getParams();
            $resultJson = array();
            $session = Mage::getSingleton('customer/session');
            $wishlist = $this->_getWishlist();
            if (!$wishlist) {
                $action->_redirect('*/');
                return;
            }
           
            $productId = (int) Mage::app()->getRequest()->getParam('product');
            if (!$productId) {
                $action->_redirect('*/');
                return;
            }

            $product = Mage::getModel('catalog/product')->load($productId);
            if (!$product->getId() || !$product->isVisibleInCatalog()) {
                $session->addError(Mage::helper('wishlist')->__('Cannot specify product.'));
                $action->_redirect('*/');
                return;
            }
            try {
                $requestParams = array('product'=>$productId);
                $buyRequest = new Varien_Object($requestParams); 
                if (version_compare(Mage::getVersion(), '1.5.0.0', '>=')){
                    $result = $wishlist->addNewItem($product, $buyRequest);
                }else{
                    $result = $wishlist->addNewItem($product->getId());
                }
                if (is_string($result)) {
                    Mage::throwException($result);
                }
                $wishlist->save();            
                Mage::dispatchEvent(
                    'wishlist_add_product',
                    array(
                        'wishlist'  => $wishlist,
                        'product'   => $product,
                        'item'      => $result
                    )
                );
                Mage::helper('wishlist')->calculate();
              
                $message = Mage::helper('wishlist')->__('%1$s has been added to wishlist.', $product->getName());
                $session->addSuccess($message);
            }
            catch (Mage_Core_Exception $e) {
                $session->addError(Mage::helper('wishlist')->__('An error occurred while adding item to wishlist: %s', $e->getMessage()));
            }
            catch (Exception $e) {
                $session->addError(Mage::helper('wishlist')->__('An error occurred while adding item to wishlist.'));
            }
             try {
                $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                $action->loadLayout();
                $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($product);
                $resultJson['relatedhtml'] = $relatedBlock->toHtml();
				if(!isset($params['wishlist']) || !$params['wishlist']){
					$redirectUrl = Mage::getUrl('wishlist/index/add', array(
						'product' => $product->getId(),
					));
					$resultJson['redirectUrl'] = $redirectUrl;
                }
                if (isset($params['minicompare']) && $params['minicompare'] && $action->getLayout()->getBlock('wishlist_sidebar')) {
                    $resultJson['miniCompare'] = $action->getLayout()->getBlock('wishlist_sidebar')->toHtml();
                }
                if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                    $action->getLayout()->getMessagesBlock()->addMessages($session->getMessages(true));
                    $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($session->getEscapeMessages(true));
                    $resultJson['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                } else {
                    $session->getMessages(true);
                    $session->getEscapeMessages(true);
                }
                if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                    $resultJson['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                }
            } catch (Exception $e) {
                
            }
          
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($resultJson));
        }
    }
    
    public function wishlistProductRemove($observer){
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $result = array();
            $params = $action->getRequest()->getParams();
            $session = Mage::getSingleton('customer/session');
            $wishlist = $this->_getWishlist();
            $id = (int) $action->getRequest()->getParam('item');
            $item = Mage::getModel('wishlist/item')->load($id);
            $productId = $item -> getProductId();
            $product = Mage::getModel('catalog/product')->load($productId);
            if($item->getWishlistId() == $wishlist->getId()) {
                try {
                    $item->delete();
                    $wishlist->save();
                    $message = Mage::helper('wishlist')->__('%1$s has been removed from wishlist.', $product->getName());
                    $session->addSuccess($message);
                }
                catch (Mage_Core_Exception $e) {
                    Mage::getSingleton('customer/session')->addError(
                        Mage::helper('wishlist')->__('An error occurred while deleting the item from wishlist: %s', $e->getMessage())
                    );
                }
                catch(Exception $e) {
   
                    Mage::getSingleton('customer/session')->addError(
                        Mage::helper('wishlist')->__('An error occurred while deleting the item from wishlist.')
                    );
                }
            }
            Mage::helper('wishlist')->calculate();
             try {
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        $action->loadLayout();
                        $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($product);
                        $result['relatedhtml'] = $relatedBlock->toHtml();
                        if (isset($params['minicompare']) && $params['minicompare'] && $action->getLayout()->getBlock('wishlist_sidebar')) {
                            $result['miniCompare'] = $action->getLayout()->getBlock('wishlist_sidebar')->toHtml();
                        }
                        if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                            $action->getLayout()->getMessagesBlock()->addMessages($session->getMessages(true));
                            $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($session->getEscapeMessages(true));
                            $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                        } else {
                            $session->getMessages(true);
                            $session->getEscapeMessages(true);
                        }
                        if (isset($params['ajaxlinks'] ) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                            $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                        }
                    } catch (Exception $e) {
                        
                    }
                $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
    
    public function checkoutCartDelete($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $params = $action->getRequest()->getParams();
            $result = array();
            if ($id = (int) $action->getRequest()->getParam('id')) {
                try {
                    $item = $this->_getQuote()->getItemById($id);
                    $this->_getCart()->removeItem($id)->save();
                    $this->_getSession()->addSuccess(Mage::helper('checkout')->__('Item was removed successfully.'));
                } catch (Exception $e) {
                    $this->_getSession()->addError(Mage::helper('checkout')->__('Cannot remove the item.'));
                }
            }
            if (isset($params['groupmessage']) || isset($params['minicart']) || isset($params['ajaxlinks']) || isset($params['isajaxcartpage'])) {
                $action->loadLayout();
                try {
                    if (isset($item) && $item) {
                        $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($item->getProduct());
                        $result['relatedhtml'] = $relatedBlock->toHtml();
                    }
                    if (isset($params['minicart']) && $params['minicart'] && $action->getLayout()->getBlock('cart_sidebar')) {
                        $result['miniCart'] = $action->getLayout()->getBlock('cart_sidebar')->toHtml();
                    }
                    if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                        $action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
                        $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
                        $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                    } else {
                        $this->_getSession()->getMessages(true);
                        $this->_getSession()->getEscapeMessages(true);
                    }
                    if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                        $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                    }
                    if (isset($params['isajaxcartpage']) && $params['isajaxcartpage']) {
                        $result['hasOptions'] = true;
                        $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
                    }
                } catch (Exception $e) {
                    
                }
            }
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartConfigure($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $id = (int) $action->getRequest()->getParam('id');
            $quoteItem = null;
            $cart = $this->_getCart();
            if ($id)
                $quoteItem = $cart->getQuote()->getItemById($id);

            $result = array();
            if (!$quoteItem) {
                $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                return $action->getResponse()->setBody('');
            }
            $viewHelper = Mage::helper('ajaxcart/product_view');

            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            $productHelper = Mage::helper('ajaxcart/product');
            $productId = $quoteItem->getProduct()->getId();
            try {
                $product = $productHelper->initProduct($productId, $action, $params);
                if (!$product) {
                    $this->_getSession()->addError($viewHelper->__('Product is not loaded'));
                } else {
                    if ($buyRequest = $params->getBuyRequest())
                        $productHelper->prepareProductOptions($product, $buyRequest);
                    $product->setConfigureMode(true);
                    Mage::dispatchEvent('catalog_controller_product_view', array('product' => $product));
                    $viewHelper->initProductLayout($product, $action);
                    $result['hasOptions'] = true;
                }
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('checkout')->__('Cannot configure product.'));
            }
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $params = $action->getRequest()->getParams();
            try {
                if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                    $action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
                    $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
                    $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                } else {
                    $this->_getSession()->getMessages(true);
                    $this->_getSession()->getEscapeMessages(true);
                }
                if (isset($result['hasOptions'])) {
                    if ($typeBlock = Mage::getStoreConfig("ajaxcart/product/{$product->getTypeId()}"))
                        $productBlock = $action->getLayout()->createBlock($typeBlock, 'ajaxcart_product_view');
                    else
                        $productBlock = $action->getLayout()->createBlock('ajaxcart/product_view', 'ajaxcart_product_view');
                    $productBlock->setData('submit_route_data', array(
                        'route' => 'checkout/cart/updateItemOptions',
                        'params' => array('id' => $id),
                    ));
                    if (!Mage::helper('ajaxcart')->getConfig('pre_load_ajax')) {
                        $result['optionjs'] = $productBlock->getJsItems();
                    }
                    $result['optionhtml'] = $productBlock->toHtml();
                }
            } catch (Exception $e) {
                
            }
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartUpdateItemOptions($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $cart = $this->_getCart();
            $id = (int) $action->getRequest()->getParam('id');
            $params = $action->getRequest()->getParams();
            $result = array();
            if (!isset($params['options']))
                $params['options'] = array();
            try {
                if (isset($params['qty'])) {
                    $filter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
                    $params['qty'] = $filter->filter($params['qty']);
                }
                $quoteItem = $cart->getQuote()->getItemById($id);
                if ($quoteItem) {
                    $item = $cart->updateItem($id, new Varien_Object($params));
                    if (is_string($item)) {
                        $this->_getSession()->addError($item);
                    } elseif ($item->getHasError()) {
                        $this->_getSession()->addError($item->getMessage());
                    } else {
                        $related = $action->getRequest()->getParam('related_product');
                        if (!empty($related))
                            $cart->addProductsByIds(explode(',', $related));
                        $cart->save();
                        $this->_getSession()->setCartWasUpdated(true);
                        Mage::dispatchEvent('checkout_cart_update_item_complete', array('item' => $item, 'request' => $action->getRequest(), 'response' => $action->getResponse()));
                        if (!$cart->getQuote()->getHasError()) {
                            $message = Mage::helper('checkout')->__('%s was updated in your shopping cart.', Mage::helper('core')->htmlEscape($item->getProduct()->getName()));
                            $this->_getSession()->addSuccess($message);
                        }
                    }
                } else {
                    $this->_getSession()->addError(Mage::helper('checkout')->__('Quote item is not found.'));
                }
            } catch (Mage_Core_Exception $e) {
                if ($this->_getSession()->getUseNotice(true)) {
                    $this->_getSession()->addNotice($e->getMessage());
                } else {
                    $messages = array_unique(explode("\n", $e->getMessage()));
                    foreach ($messages as $message)
                        $this->_getSession()->addError($message);
                }
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('checkout')->__('Cannot update the item.'));
            }
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            if (isset($params['groupmessage']) || isset($params['minicart']) || isset($params['ajaxlinks']) || isset($params['isajaxcartpage'])) {
                $action->loadLayout();
                try {
                    if (isset($item) && $item) {
                        $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($item->getProduct());
                        $result['relatedhtml'] = $relatedBlock->toHtml();
                    }
                    if (isset($params['minicart']) && $params['minicart'] && $action->getLayout()->getBlock('cart_sidebar')) {
                        $result['miniCart'] = $action->getLayout()->getBlock('cart_sidebar')->toHtml();
                    }
                    if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                        $action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
                        $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
                        $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                    } else {
                        // $this->_getSession()->getMessages(true);
                        // $this->_getSession()->getEscapeMessages(true);
                    }
                    if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                        $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                    }
                    if (isset($params['isajaxcartpage']) && $params['isajaxcartpage']) {
                        $result['hasOptions'] = true;
                        $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
                    }
                } catch (Exception $e) {
                    
                }
            }
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartIndex($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true'
            && $action->getRequest()->getParam('isajaxcartpage') == 1) {
            $cart = $this->_getCart();
            if ($cart->getQuote()->getItemsCount()) {
                $cart->init();
                $cart->save();
                if (!$this->_getQuote()->validateMinimumAmount()) {
                    $warning = Mage::getStoreConfig('sales/minimum_order/description');
                    $this->_getSession()->addNotice($warning);
                }
            }
            if (version_compare(Mage::getVersion(), '1.6.0.0', '>=')) {
                $messages = array();
                foreach ($cart->getQuote()->getMessages() as $message)
                    if ($message)
                        $messages[] = $message;
                $this->_getSession()->addUniqueMessages($messages);
            } else {
                foreach ($cart->getQuote()->getMessages() as $message)
				if ($message instanceof Mage_Core_Model_Message_Abstract)
					$this->_getSession()->addMessage($message);
            }
            $this->_getSession()->setCartWasUpdated(true);

            $action->loadLayout();
            if ($action->getLayout()->getMessagesBlock()) {
                $action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
                $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
            }
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);

            $result = array();
            if ($action->getRequest()->getParam('ajaxlinks') && $action->getLayout()->getBlock('top.links'))
                $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
            if ($cartBlock = $action->getLayout()->getBlock('checkout.cart')) {
                $cartHtml = $cartBlock->toHtml();
                if (/* Mage::helper('ajaxcart')->getConfig('minicart')
                    && */$action->getLayout()->getBlock('cart_sidebar'))
                    $cartHtml .= $action->getLayout()->getBlock('cart_sidebar')->toHtml();
                $result['cartPage'] = $cartHtml;
            }
            if (!$cart->getQuote()->getItemsCount())
                $result['emptyCart'] = true;
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartUpdatePost($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true'
            && $action->getRequest()->getParam('isajaxcartpage') == 1) {
            try {
                $cartData = $action->getRequest()->getParam('cart');
                if (is_array($cartData)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
                    foreach ($cartData as $index => $data)
                        if (isset($data['qty']))
                            $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    $cart = $this->_getCart();
                    if (!$cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId())
                        $cart->getQuote()->setCustomerId(null);
                    if (method_exists($cart,'suggestItemsQty'))
                        $cartData = $cart->suggestItemsQty($cartData);
                    $cart->updateItems($cartData)->save();
                }
                $this->_getSession()->setCartWasUpdated(true);
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('checkout')->__('Cannot update shopping cart.'));
            }
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $result = array();
            $result['hasOptions'] = true;
            $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartEstimateUpdatePost($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true'
            && $action->getRequest()->getParam('isajaxcartpage') == 1) {
            $code = (string) $action->getRequest()->getParam('estimate_method');
            if (!empty($code))
                $this->_getQuote()->getShippingAddress()->setShippingMethod($code)->save();

            $result = array();
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $result['hasOptions'] = true;
            $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartCouponPost($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true'
            && $action->getRequest()->getParam('isajaxcartpage') == 1
            && $this->_getCart()->getQuote()->getItemsCount()) {
            $couponCode = (string) $action->getRequest()->getParam('coupon_code');
            if ($action->getRequest()->getParam('remove') == 1)
                $couponCode = '';
            $oldCouponCode = $this->_getQuote()->getCouponCode();
            $result = array();
            if (strlen($couponCode) || strlen($oldCouponCode)) {
                try {
                    $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
                    $this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')->collectTotals()->save();
                    if ($couponCode) {
                        if ($couponCode == $this->_getQuote()->getCouponCode())
                            $this->_getSession()->addSuccess(Mage::helper('checkout')->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode)));
                        else
                            $this->_getSession()->addError(Mage::helper('checkout')->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode)));
                    } else {
                        $this->_getSession()->addSuccess(Mage::helper('checkout')->__('Coupon code was canceled.'));
                    }
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->addError(Mage::helper('checkout')->__('Cannot apply the coupon code.'));
                }
                $result['hasOptions'] = true;
                $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
            } else {
                $result['nothing'] = true;
            }
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function checkoutCartEstimatePost($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true'
            && $action->getRequest()->getParam('isajaxcartpage') == 1) {
            $country = (string) $action->getRequest()->getParam('country_id');
            $postcode = (string) $action->getRequest()->getParam('estimate_postcode');
            $city = (string) $action->getRequest()->getParam('estimate_city');
            $regionId = (string) $action->getRequest()->getParam('region_id');
            $region = (string) $action->getRequest()->getParam('region');

            $this->_getQuote()->getShippingAddress()
                ->setCountryId($country)
                ->setCity($city)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region)
                ->setCollectShippingRates(true);
            $this->_getQuote()->save();

            $result = array();
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            $result['hasOptions'] = true;
            $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    public function wishlistIndexCart($observer) {
        if (!Mage::helper('magenotification')->checkLicenseKey('Ajaxcart')) {
            return;
        }
        $action = $observer->getEvent()->getControllerAction();
        if ($action->getRequest()->getParam('isajaxcart') == 'true') {
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            
            $wishlist   = $this->_getCustomerWishlist();
            if (!$wishlist) return;
            
            $itemId = (int) $action->getRequest()->getParam('item');
            $item = Mage::getModel('wishlist/item')->load($itemId);
            if (!$item->getId() || $item->getWishlistId() != $wishlist->getId()) return;
            
            if (version_compare(Mage::getVersion(), '1.5.0.0', '>=')) {
                $qty = $action->getRequest()->getParam('qty');
                if (is_array($qty)) {
                    if (isset($qty[$itemId])) {
                        $qty = $qty[$itemId];
                    } else {
                        $qty = 1;
                    }
                }
                $filter = new Zend_Filter_LocalizedToNormalized(array('locale' => Mage::app()->getLocale()->getLocaleCode()));
                $qty = $filter->filter($qty);
                if ($qty < 0) $qty = null;
                if ($qty) $item->setQty($qty);
            }
            
            $session    = Mage::getSingleton('wishlist/session');
            $cart       = Mage::getSingleton('checkout/cart');
            
            $result = array();
            $product = $item->getProduct();
            try {
                if (version_compare(Mage::getVersion(), '1.5.0.0', '>=')) {
                    $options = Mage::getModel('wishlist/item_option')->getCollection()
                            ->addItemFilter(array($itemId));
                    $item->setOptions($options->getOptionsByItem($itemId));

                    $buyRequest = Mage::helper('catalog/product')->addParamsToBuyRequest(
                        $action->getRequest()->getParams(),
                        array('current_config' => $item->getBuyRequest())
                    );
                    $item->mergeBuyRequest($buyRequest);
                }
                $item->addToCart($cart, true);
                $cart->save()->getQuote()->collectTotals();
                if (version_compare(Mage::getVersion(), '1.5.0.0', '>=')) {
                    $wishlist->save();
                    Mage::helper('wishlist')->calculate();
                    $this->_getSession()->addSuccess(Mage::helper('checkout')->__('%s was added to your shopping cart.', Mage::helper('core')->htmlEscape($product->getName())));
                } else {
                    $this->_getSession()->addSuccess(Mage::helper('checkout')->__('%s was added to your shopping cart.', $product->getName()));
                }
                Mage::helper('wishlist')->calculate();
            } catch (Mage_Core_Exception $e) {
                if ($e->getCode() == Mage_Wishlist_Model_Item::EXCEPTION_CODE_NOT_SALABLE) return;
                $this->_getSession()->addError($e->getMessage());
                $result['hasOptions'] = true;
            } catch (Exception $e) {
                $result['hasOptions'] = true;
            }
            Mage::helper('wishlist')->calculate();
            
            $params = $action->getRequest()->getParams();
            if (isset($result['hasOptions'])) {
                $redirectUrl = Mage::getUrl('catalog/product/view', array(
                        'id' => $product->getId(),
                        // 'related_product' => $params['related_product']
                    ));
                $result['redirectUrl'] = $redirectUrl;
            } elseif (isset($params['groupmessage']) || isset($params['minicart']) || isset($params['ajaxlinks']) || isset($params['isajaxcartpage'])) {
                $action->loadLayout();
                try {
                    $relatedBlock = $action->getLayout()->createBlock('ajaxcart/display')->setProduct($product);
                    $result['relatedhtml'] = $relatedBlock->toHtml();
                    $wishlistBlock = $action->getLayout()->createBlock('wishlist/customer_wishlist')
                        ->setTemplate('ajaxcart/wishlist.phtml');
                    $result['wishlisthtml'] = $wishlistBlock->toHtml();
                    if (isset($params['minicart']) && $params['minicart'] && $action->getLayout()->getBlock('cart_sidebar')) {
                        $result['miniCart'] = $action->getLayout()->getBlock('cart_sidebar')->toHtml();
                    }
                    if (isset($params['groupmessage']) && $params['groupmessage'] && $action->getLayout()->getMessagesBlock()) {
                        $action->getLayout()->getMessagesBlock()->addMessages($this->_getSession()->getMessages(true));
                        $action->getLayout()->getMessagesBlock()->setEscapeMessageFlag($this->_getSession()->getEscapeMessages(true));
                        $result['message'] = $action->getLayout()->getMessagesBlock()->getGroupedHtml();
                    } else {
                        $this->_getSession()->getMessages(true);
                        $this->_getSession()->getEscapeMessages(true);
                    }
                    if (isset($params['ajaxlinks']) && $params['ajaxlinks'] && $action->getLayout()->getBlock('top.links')) {
                        $result['ajaxlinks'] = $action->getLayout()->getBlock('top.links')->toHtml();
                    }
                    if (isset($params['isajaxcartpage']) && $params['isajaxcartpage']) {
                        $result['hasOptions'] = true;
                        $result['redirectUrl'] = Mage::getUrl('checkout/cart/index');
                    }
                } catch (Exception $e) {
                    
                }
            }
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }
    
    protected function _getCustomerWishlist() {
        $wishlist = Mage::registry('wishlist');
        if ($wishlist) {
            return $wishlist;
        }
        try {
            $wishlist = Mage::getModel('wishlist/wishlist')
                ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomer(), true);
            Mage::register('wishlist', $wishlist);
        } catch (Exception $e) {
            return false;
        }
        return $wishlist;
    }
}