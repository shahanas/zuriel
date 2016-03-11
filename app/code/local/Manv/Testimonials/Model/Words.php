<?php

class Manv_Testimonials_Model_Words
{
    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('testimonials')->__('left')),
            array('value'=>2, 'label'=>Mage::helper('testimonials')->__('middle')),
            array('value'=>3, 'label'=>Mage::helper('testimonials')->__('right')),
       );
    }

}
	 
