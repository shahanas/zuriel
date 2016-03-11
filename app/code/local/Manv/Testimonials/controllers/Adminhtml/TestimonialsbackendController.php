<?php
class Manv_Testimonials_Adminhtml_TestimonialsbackendController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
    {
       $this->loadLayout();
	   $this->_title($this->__("Testimonials"));
	   $this->renderLayout();
    }
}