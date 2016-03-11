<?php
class Manv_Testimonials_Model_Mysql4_Testimonials extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("testimonials/testimonials", "id");
    }
}