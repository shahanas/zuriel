<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Configurable
 *
 * @author kienkun1990
 */
class Magestore_Ajaxcart_Block_Product_Configurable extends Magestore_Ajaxcart_Block_Product_View{
    
    public function _prepareLayout(){
		parent::_prepareLayout();
		//$this->setTemplate('ajaxcart/product/configurable.phtml');
		return $this;
	}
    
    public function getStartFormHtml(){
		return $this->getBlockHtml('product.info.configurable');
	}
//	
//	public function getOptionsWrapperBottomHtml(){
//		return $this->getBlockHtml('product.info.addtocart');
//	}
}

?>
