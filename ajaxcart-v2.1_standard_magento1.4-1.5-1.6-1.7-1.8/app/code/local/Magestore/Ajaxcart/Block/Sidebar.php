<?php

/**
 * Ajaxcart sidebar block
 *
 * @category   Mage
 * @package    Ajaxcart
 * @author     Kun
 */
class Magestore_Ajaxcart_Block_Sidebar extends Mage_Wishlist_Block_Customer_Sidebar
{
    
    protected function _toHtml()
    {
        if (($this->getCustomWishlist() && $this->getItemCount()) || $this->hasWishlistItems()) {
            return parent::_toHtml();
        }

        return '<div class="block block-wishlist" style="display:none"></div>';
    }

}
