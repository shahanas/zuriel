<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table manv_testimonials(id int not null auto_increment, name varchar(100), pic varchar(255), message varchar(255),primary key(id));
  
SQLTEXT;

$installer->run($sql);
//demo 
//Mage::getModel('core/url_rewrite')->setId(null);
//demo 
$installer->endSetup();
	 