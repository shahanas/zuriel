<?php

class Magestore_Ajaxcart_Block_Product_View extends Mage_Catalog_Block_Product_View
{
	public function _prepareLayout(){
		$this->setTemplate('ajaxcart/product/view.phtml');
		return parent::_prepareLayout();
	}
	
	public function getJsItems(){
		if (!$this->hasData('js_items')){
			$jsItems = array();
			if ($headBlock = $this->getLayout()->getBlock('head')){
				$designPackage = Mage::getDesign();
				$baseJsUrl = Mage::getBaseUrl('js');
				$mergeCallback = Mage::getStoreConfigFlag('dev/js/merge_files') ? array(Mage::getDesign(), 'getMergedJsUrl') : null;
				foreach ($headBlock->getData('items') as $item){
					$name = $item['name'];
					if ($item['type'] == 'js'){
						$jsItems[] = $mergeCallback ? Mage::getBaseDir().DS.'js'.DS .$name : $baseJsUrl.$name;
					}
					if ($item['type'] == 'skin_js'){
						$jsItems[] = $mergeCallback ? $designPackage->getFilename($name,array('_type' => 'skin')) : $designPackage->getSkinUrl($name,array());
					}
				}
			}
			$this->setData('js_items',$jsItems);
		}
		return $this->getData('js_items');
	}
	
	public function getStartFormHtml(){
		return '';
	}
	
	public function getOptionsWrapperHtml(){
		return $this->getBlockHtml('product.info.options.wrapper');
	}
	
	public function getOptionsWrapperBottomHtml(){
		return $this->getBlockHtml('product.info.options.wrapper.bottom');
	}
	
	public function getEndFormHtml(){
		return '';
	}
    
    public function isEditItem() {
        return $this->getFullActionName() == 'checkout_cart_configure';
    }
    public function isCartPage()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        return $currentUrl;
    }
    public function getFullActionName($delimiter='_') {
        $request = $this->getRequest();
        return $request->getRequestedRouteName().$delimiter.
            $request->getRequestedControllerName().$delimiter.
            $request->getRequestedActionName();
    }
    
    public function getUrlImage() {
        $config = Mage::helper('ajaxcart')->getBaseUrlImage();
        if ($config) {
            $imgSrc = Mage::getBaseUrl('media') . 'ajaxcart/animation/' . $config;
            return $imgSrc;
        } else {
            return $this->getSkinUrl('images/ajaxcart/loading.gif');
            // return $this->getSkinUrl('images/opc-ajax-loader.gif');
        }
    }
    
    public function getAutoScroll() {
        return Mage::helper('ajaxcart')->getConfig('auto_scroll');
    }
    
    public function getSubmitUrl($product, $additional = array()) {
        if (version_compare(Mage::getVersion(), '1.5.0.0', '>=')) {
            return parent::getSubmitUrl($product, $additional);
        }
        return $this->getAddToCartUrl($product, $additional);
    }
}
