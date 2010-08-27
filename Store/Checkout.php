<?php

namespace ELib\Store;
use ELib\Model;
use ELib\User\CurrentUser;

class Checkout
{
  private $invoice_no;

  public function __construct($items, $c)
  {        
    $s = Model::load('ShippingAddress');
    $s->id = $_SESSION['shipping_address_id'];
    $s->load();
        
    $o = Model::load('OrderItem');
    $o->user_id = CurrentUser::getUserID();
    $o->status = 'DEFAULT';
    $o->stamp = 'DEFAULT';
    $o->first_name = $s->first_name;
    $o->last_name = $s->last_name;
    $o->address1 = $s->address1;
    $o->address2 = $s->address2;
    $o->city = $s->city;
    $o->state = $s->state;
    $o->zip = $s->zip;
    $o->country = $s->country;

    $this->invoice_no = $o->insert(Model::getTable('OrderItem'), 1, array(), 0);
    
    $l = Model::load('LineItem');
    
    foreach($items as $item)
      {
	if(is_numeric($item['qty']) && $item['qty'] > 0)
	  {
	    $l->order_id = $this->invoice_no;
	    $l->variant_id = $item['id'];
	    $l->price = $item['price'];
	    $l->quantity = $item['qty'];
	    $l->insert(Model::getTable('LineItem'), 1, array(), 0);
	  }	    	    
      }	           
  }
  
  public function getInvoiceNo()
  {
    return $this->invoice_no;
  }


  public function setInvoiceNo($id)
  {
    $this->invoice_no = $id;
  }

}
?>