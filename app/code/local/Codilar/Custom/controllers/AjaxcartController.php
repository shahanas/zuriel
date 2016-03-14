<?php
class Codilar_Custom_AjaxcartController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
    public function checkpincodeAction()
    {
        $dest_zip=$this->getRequest()->getParam('dest_zip');
        $tablerateColl = Mage::getResourceModel('shipping/carrier_tablerate_collection');
        $match_flag=false;
        if(count($tablerateColl)) {
            foreach ($tablerateColl as $tablerate) {
                if ($tablerate['dest_zip'] == $dest_zip) {
                    $match_flag = true;
                }
            }
        }
        else
            $match_flag = true;
        if($match_flag)
            echo "1";
        else
            echo "0";
    }
}
