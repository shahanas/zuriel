<?php
class Manv_Testimonials_TestimonialsController extends Mage_Core_Controller_Front_Action{
		public function IndexAction() {
				  
				  $this->loadLayout();   
				  $this->getLayout()->getBlock("head")->setTitle($this->__("Testimonials"));
						$breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
				  $breadcrumbs->addCrumb("home", array(
							"label" => $this->__("Home Page"),
							"title" => $this->__("Home Page"),
							"link"  => Mage::getBaseUrl()
					   ));

				  $breadcrumbs->addCrumb("testimonials", array(
							"label" => $this->__("Testimonials"),
							"title" => $this->__("Testimonials")
					   ));

				  $this->renderLayout(); 
				  
		}
}