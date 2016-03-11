<?php

class Magestore_Ajaxcart_Block_Product_Grouped extends Magestore_Ajaxcart_Block_Product_View
{
	public function _prepareLayout(){
		parent::_prepareLayout();
		$this->setTemplate('ajaxcart/product/grouped.phtml');
		return $this;
	}
	
	public function getStartFormHtml(){
		return $this->getBlockHtml('product.info.grouped');
	}
	
	public function getOptionsWrapperBottomHtml(){
		return $this->getBlockHtml('product.info.addtocart');
	}
        public function isCartPage()
    {
        $currentUrl = Mage::helper('core/url')->getCurrentUrl();
        return $currentUrl;
    }
}