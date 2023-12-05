<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orders_model extends CI_Model
{
    private $orders;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->orders = [];
        
        $this->load->model('sales_model');
    }
     
    public function addOrder($data = array(), $items = array(), $payment = array(), $si_return = array(), $extrasPara = array() )
    {
        $this->load->model('sales_model');
        
        $cost = $this->site->costing($items);
         
        $sale_action    = $extrasPara['sale_action'] ? $extrasPara['sale_action'] : null;
        $order_id       = $extrasPara['order_id'] ? $extrasPara['order_id'] : null;
        $syncQuantity   = $extrasPara['syncQuantity'];
            
        $data['sale_as_chalan'] = ($sale_action == 'chalan' ? 1 : 0);
         
        if ($this->db->insert('orders', $data)) {
            
            $order_id = $this->db->insert_id();
            
            if(empty($data['invoice_no'])){
                //Get formated Invoice No
                $invoice_no = $this->sma->invoice_format($order_id,date());             
                //Update formated invoice no
                $this->db->where(['id'=>$order_id])->update('orders', ['invoice_no' => $invoice_no]);
            }
            
            if ($this->site->getReference(ordr) == $data['reference_no']) {
                $this->site->updateReference(ordr);
            }
            if ($this->site->getReference(re_ordr) == $data['return_sale_ref']) {
               $this->site->updateReference(re_ordr);
            }
	    $Setting =   $this->Settings;
            
            foreach ($items as $item) {
		//------------------Change For  Pharma for  saving Exp. date & Batch No ----------------//
                $_prd       =   $Setting->pos_type=='pharma' ?$this->site->getProductByID($item['product_id']):NULL;
                $item['cf1'] = $Setting->pos_type=='pharma' ?$_prd->cf1:'';
                $item['cf2'] = $Setting->pos_type=='pharma' ?$_prd->cf2:'';
                //------------------ End ----------------//
                $item['sale_id'] = $order_id;
                $this->db->insert(order_items, $item);
                $sale_item_id = $this->db->insert_id();
                    
                $_taxSaleID =  $order_id;
                
                $_tax_type = ($sale_action == 'chalan' ? 'o' : NULL);
                
                $taxAtrr = $this->sma->taxAtrrClassification($item['tax_rate_id'], $item['net_unit_price'], $item['unit_quantity'], $sale_item_id, $_taxSaleID , $_tax_type);
                
                if($data['sale_status'] == 'completed') {

                    $item_costs = $this->site->item_costing($item);
                    
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) { 
                            
                            $item_cost['order_item_id'] = $sale_item_id;
                            $item_cost['order_id'] = $order_id;
                            
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                            	if(is_array($ic)):
                                     
                                    $ic['order_item_id'] = $sale_item_id;
                                    $ic['order_id']      = $order_id;

                                    if(! isset($ic['pi_overselling'])) {
                                        $this->db->insert('costing', $ic);
                                    }
                                endif;
                            }
                        }
                    }
                }                         
            }            

            if ($data['sale_status'] == 'completed' && $syncQuantity) {
                
                $this->site->syncPurchaseItems($cost);
            }

            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) {
                            
                            $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                            $this->updatePurchaseItem(NULL,($return_item['quantity']*$combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                        }
                    } else {
                        $this->updateCostingLine($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                        $this->updatePurchaseItem(NULL, $return_item['quantity'], $return_item['id']);
                    }
                }
                $this->db->update('orders', array('return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => $data['grand_total'], 'return_id' => $order_id), array('id' => $data['sale_id']));
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
                
                $payment['order_id'] = $order_id;                 
                
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update('companies', array('deposit_amount' => $payment['cc_holder']), array('id' => $data['customer_id']));
                        //$this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$payment['amount'])), array('id' => $customer->id));
                    }
                    $this->db->insert('payments', $payment);
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncOrderPayments($order_id);
            }
            
            if($syncQuantity) {
                $this->site->syncQuantity( NULL, NULL, NULL, NULL, $order_id );
            }            
            
            if ($this->Settings->synch_reward_points) {
                $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            }
            
            return $order_id;
        }

        return false;
    }
    
    public function getAllTaxOrderItems($order_id,$return_id,$itemId=NULL)  {
        $this->db->select("attr_code,attr_name,attr_per, `tax_amount`  AS `amt`,item_id");
        $this->db->where_in('order_id', array($order_id,$return_id)); 
        $q =  $this->db->get('orders_items_tax'); 
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
               $data[$row->item_id][$row->attr_code] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getAllTaxItemsGroup($order_id,$return_id=NULL)  {
        $this->db->select("attr_code,attr_name,attr_per,sum(`tax_amount`) AS `amt`");
        $this->db->where_in('order_id', array((int)$order_id,(int)$return_id)); 
        $this->db->group_by('attr_code'); 
          $this->db->order_by('id', 'asc'); 
        $q =  $this->db->get('orders_items_tax');
        
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getAllOrderItems($order_id) {
        
        if($this->pos_settings->item_order == 0) {
            $this->db->select('order_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, products.details as details, categories.id as category_id, categories.name as category_name, product_variants.price as variant_price')
                    ->join('products', 'products.id=order_items.product_id', 'left')
                    ->join('categories', 'categories.id=products.category_id', 'left')
                    ->join('tax_rates', 'tax_rates.id=order_items.tax_rate_id', 'left')
                    ->join('product_variants', 'product_variants.id=order_items.option_id', 'left')
                    ->group_by('order_items.id');
                   // ->order_by('id', 'asc');
                    if($this->pos_settings->display_category == 0)
                            $this->db->order_by('order_items.subtotal', 'desc');
                    else
                            $this->db->order_by('categories.id', 'desc');
                    
        } elseif ($this->pos_settings->item_order == 1) {
            $this->db->select('order_items.*, tax_rates.code as tax_code, tax_rates.name as tax_name, tax_rates.rate as tax_rate, product_variants.name as variant, categories.id as category_id, categories.name as category_name, products.details as details')
                    ->join('tax_rates', 'tax_rates.id=order_items.tax_rate_id', 'left')
                    ->join('product_variants', 'product_variants.id=order_items.option_id', 'left')
                    ->join('products', 'products.id=order_items.product_id', 'left')
                    ->join('categories', 'categories.id=products.category_id', 'left')
                    ->group_by('order_items.id');
                  //  ->order_by('categories.id', 'asc')
                    if($this->pos_settings->display_category == 0)
                            $this->db->order_by('order_items.subtotal', 'desc');
                    else
                            $this->db->order_by('categories.id', 'desc');
        }//end else
        
        $q = $this->db->get_where('order_items', array('sale_id' => $order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                if ($row->product_type == 'combo') {
                    $row->combo_items = $this->sales_model->getProductComboItems($row->product_id);
                }
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function getOrderPayments($order_id) {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('order_id' => $order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getOrderByID($id)
    {
        $q = $this->db->get_where('orders', array('id' => $id ), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getOrderItemByID($id)
    {
        $q = $this->db->get_where('order_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getOrderItem($id)
    {
        $q = $this->db->get_where('order_items', array('sale_id' => $id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    
    public function actionDeleteOrder($id) {
        
        $order = $this->getOrderByID($id);
        
        $syncQuantity = $order->sale_as_chalan ? $order->sale_as_chalan : 0; 
        
        $sale_id = null;
        if($order->sale_invoice_no) {
           $sale = $this->sales_model->getSaleByInvoiceNo($inv->sale_invoice_no);
           if($sale){ $sale_id = $sale->id; }
        }
        
        return $this->deleteOrder($id, $syncQuantity, $sale_id);
    }
    
    public function deleteOrder($id, $syncQuantity=0, $sale_id=null)
    {
        if($syncQuantity) {
            $order_items = $this->resetOrderActions($id);
        }
        if ($this->db->delete('order_items', array('sale_id' => $id)) && $this->db->delete('orders', array('id' => $id)) ) {
            $this->db->delete('orders_items_tax', array('order_id' => $id));
            $this->db->delete('orders', array('sale_id' => $id));
            
            if($sale_id){
                $this->db->update('payments', array('order_id' => null, 'sale_id' => $sale_id), array('order_id' => $id));
                $this->db->update('costing', array('order_id' => null, 'sale_id' => $sale_id, 'order_item_id' => null ), array('order_id' => $id));
            } else {
                $this->db->delete('payments', array('order_id' => $id));
                $this->db->delete('costing', array('order_id' => $id));
            }
            
            if($syncQuantity) {
                $this->site->syncQuantity(NULL, NULL, $order_items);
            }
            return true;
        }
        return FALSE;
    }
    
    public function resetOrderActions($id, $return_id = NULL, $check_return = NULL)
    {
        if ($order = $this->getOrderByID($id)) {
            if ($check_return && $order->sale_status == 'returned') {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }

            if ($order->sale_status == 'completed') {
                $items = $this->getAllOrderItems($id);
                foreach ($items as $item) {
                    if ($item->product_type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            if($combo_item->type == 'standard') {
                                $qty = ($item->quantity*$combo_item->qty);                                
                                $this->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                            }
                        }
                    } else {
                        $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                        $this->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                    }
                }
                if ($order->return_id || $return_id) {
                    $rid = $return_id ? $return_id : $order->return_id;
                    $returned_items = $this->getAllOrderItems(FALSE, $rid);
                    foreach ($returned_items as $item) {

                        if ($item->product_type == 'combo') {
                            $combo_items = $this->site->getProductComboItems($item->product_id, $item->warehouse_id);
                            foreach ($combo_items as $combo_item) {
                                if($combo_item->type == 'standard') {
                                    $qty = ($item->quantity*$combo_item->qty);
                                    $this->updatePurchaseItem(NULL, $qty, NULL, $combo_item->id, $item->warehouse_id);
                                }
                            }
                        } else {
                            $option_id = isset($item->option_id) && !empty($item->option_id) ? $item->option_id : NULL;
                            $this->updatePurchaseItem(NULL, $item->quantity, $item->id, $item->product_id, $item->warehouse_id, $option_id);
                        }

                    }
                }
                $this->site->syncQuantity(NULL, NULL, $items);
                //$this->sma->update_award_points($order->grand_total, $order->customer_id, $order->created_by, TRUE);
                return $items;
            }
        }
    }


    public function updatePurchaseItem($id, $qty, $order_item_id, $product_id = NULL, $warehouse_id = NULL, $option_id = NULL)
    {
        if ($id) {
            if($pi = $this->getPurchaseItemByID($id)) {
                $pr = $this->site->getProductByID($pi->product_id);
                if ($pr->type == 'combo') {
                    $combo_items = $this->site->getProductComboItems($pr->id, $pi->warehouse_id);
                    foreach ($combo_items as $combo_item) {
                        if($combo_item->type == 'standard') {
                            $cpi = $this->site->getPurchasedItem(array('product_id' => $combo_item->id, 'warehouse_id' => $pi->warehouse_id, 'option_id' => NULL));
                            $bln = $pi->quantity_balance + ($qty*$combo_item->qty);
                            $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $combo_item->id));
                        }
                    }
                } else {
                    $bln = $pi->quantity_balance + $qty;
                    $this->db->update('purchase_items', array('quantity_balance' => $bln), array('id' => $id));
                }
            }
        } else {
            if ($order_item_id) {
                if ($order_item = $this->getOrderItemByID($order_item_id)) {
                    $option_id = isset($order_item->option_id) && !empty($order_item->option_id) ? $order_item->option_id : NULL;
                    $clause = array('product_id' => $order_item->product_id, 'warehouse_id' => $order_item->warehouse_id, 'option_id' => $option_id);
                    if ($pi = $this->site->getPurchasedItem($clause)) {
                        $quantity_balance = $pi->quantity_balance+$qty;
                        $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                    } else {
                        $clause['purchase_id'] = NULL;
                        $clause['transfer_id'] = NULL;
                        $clause['quantity'] = 0;
                        $clause['quantity_balance'] = $qty;
                        $this->db->insert('purchase_items', $clause);
                    }
                }
            } else {
                if ($product_id && $warehouse_id) {
                    $pr = $this->site->getProductByID($product_id);
                    $clause = array('product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'option_id' => $option_id);
                    if ($pr->type == 'standard') {
                        if ($pi = $this->site->getPurchasedItem($clause)) {
                            $quantity_balance = $pi->quantity_balance+$qty;
                            $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
                        } else {
                            $clause['purchase_id'] = NULL;
                            $clause['transfer_id'] = NULL;
                            $clause['quantity'] = 0;
                            $clause['quantity_balance'] = $qty;
                            $this->db->insert('purchase_items', $clause);
                        }
                    } elseif ($pr->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($pr->id, $warehouse_id);
                        foreach ($combo_items as $combo_item) {
                            $clause = array('product_id' => $combo_item->id, 'warehouse_id' => $warehouse_id, 'option_id' => NULL);
                            if($combo_item->type == 'standard') {
                                if ($pi = $this->site->getPurchasedItem($clause)) {
                                    $quantity_balance = $pi->quantity_balance+($qty*$combo_item->qty);
                                    $this->db->update('purchase_items', array('quantity_balance' => $quantity_balance), $clause);
                                } else {
                                    $clause['transfer_id'] = NULL;
                                    $clause['purchase_id'] = NULL;
                                    $clause['quantity'] = 0;
                                    $clause['quantity_balance'] = $qty;
                                    $this->db->insert('purchase_items', $clause);
                                }
                            }
                        }
                    }
                }
            }
        }
    }//end function

   
    public function updateCostingLine($order_item_id, $product_id, $quantity)
    {
        if ($costings = $this->getCostingLines($order_item_id, $product_id)) {
            foreach ($costings as $cost) {
                if ($cost->quantity >= $quantity) {
                    $qty = $cost->quantity - $quantity;
                    $bln = $cost->quantity_balance && $cost->quantity_balance >= $quantity ? $cost->quantity_balance - $quantity : 0;
                    $this->db->update('costing', array('quantity' => $qty, 'quantity_balance' => $bln), array('id' => $cost->id));
                    $quantity = 0;
                } elseif ($cost->quantity < $quantity) {
                    $qty = $quantity - $cost->quantity;
                    $this->db->delete('costing', array('id' => $cost->id));
                    $quantity = $qty;
                }
            }
            return TRUE;
        }
        return FALSE;
    }
    
    public function getCostingLines($order_item_id, $product_id, $order_id = NULL)
    {
        if ($sale_id) { $this->db->where('order_id', $order_id); }
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('costing', array('order_item_id' => $order_item_id, 'product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
    public function addSale( $data = array(), $items = array(), $payment = array(), $si_return = array(), $extrasPara = array() )
    {
        $this->load->model('sales_model');
        
        $cost = $this->site->costing($items);
         
        $sale_action    = $extrasPara['sale_action'] ? $extrasPara['sale_action'] : null;
        $order_id       = $extrasPara['order_id'] ? $extrasPara['order_id'] : null;
        $syncQuantity   = $extrasPara['syncQuantity'];
                 
        if ($this->db->insert('sales', $data)) {
            
            $sale_id = $this->db->insert_id();

            //Get formated Invoice No
            $invoice_no = $this->sma->invoice_format($sale_id,date());             
            //Update formated invoice no
            $this->db->where(['id'=>$sale_id])->update('sales', ['invoice_no' => $invoice_no]);
            
            if($order_id) {
                //Update sale_invoice_no after convert order into sales. 
               $this->db->where(['id'=>$order_id])->update('orders', ['sale_invoice_no' => $invoice_no]); 
            }
            // End Invoice No
            

            if ($this->site->getReference('so') == $data['reference_no']) {
                $this->site->updateReference('so');
            }
            if ($this->site->getReference('re') == $data['return_sale_ref']) {
               $this->site->updateReference('re');
            }
	    $Setting =   $this->Settings;
            
            foreach ($items as $item) {
		//------------------Change For  Pharma for  saving Exp. date & Batch No ----------------//
                $_prd       =   $Setting->pos_type=='pharma' ?$this->site->getProductByID($item['product_id']):NULL;
                $item['cf1'] = $Setting->pos_type=='pharma' ?$_prd->cf1:'';
                $item['cf2'] = $Setting->pos_type=='pharma' ?$_prd->cf2:'';
                //------------------ End ----------------//
                $item['sale_id'] = $sale_id;
                $this->db->insert('sale_items', $item);
                $sale_item_id = $this->db->insert_id();
                    
                $_taxSaleID =  $sale_id;
                
                $_tax_type = ($sale_action == 'chalan' ? 'o' : NULL);
                
                $taxAtrr = $this->sma->taxAtrrClassification($item['tax_rate_id'], $item['net_unit_price'], $item['unit_quantity'], $sale_item_id, $_taxSaleID , $_tax_type);
                
                if($data['sale_status'] == 'completed') {

                    $item_costs = $this->site->item_costing($item);
                    
                    foreach ($item_costs as $item_cost) {
                        if (isset($item_cost['date'])) {
                             
                            $item_cost['sale_item_id'] = $sale_item_id;
                            $item_cost['sale_id'] = $sale_id;
                                                        
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        } else {
                            foreach ($item_cost as $ic) {
                            	if(is_array($ic)):
                                    if($sale_action == 'chalan'){
                                        $ic['order_item_id'] = $sale_item_id;
                                        $ic['order_id']      = $sale_id;
                                    } else {
                                        $ic['sale_item_id'] = $sale_item_id;
                                        $ic['sale_id']      = $sale_id;
                                    }

                                    if(! isset($ic['pi_overselling'])) {
                                        $this->db->insert('costing', $ic);
                                    }
                                endif;
                            }
                        }
                    }
                }                         
            }            

            if ($data['sale_status'] == 'completed' && $syncQuantity) {
                
                $this->site->syncPurchaseItems($cost);
            }

            if (!empty($si_return)) {
                foreach ($si_return as $return_item) {
                    $product = $this->site->getProductByID($return_item['product_id']);
                    if ($product->type == 'combo') {
                        $combo_items = $this->site->getProductComboItems($return_item['product_id'], $return_item['warehouse_id']);
                        foreach ($combo_items as $combo_item) { 
                            
                            $this->updateCostingLine($return_item['id'], $combo_item->id, $return_item['quantity']);
                            $this->updatePurchaseItem(NULL,($return_item['quantity']*$combo_item->qty), NULL, $combo_item->id, $return_item['warehouse_id']);
                        }
                    } else {                        
                        $this->updateCostingLine($return_item['id'], $return_item['product_id'], $return_item['quantity']);
                        $this->updatePurchaseItem(NULL, $return_item['quantity'], $return_item['id']);
                    }
                }
                $this->db->update('sales', array('return_sale_ref' => $data['return_sale_ref'], 'surcharge' => $data['surcharge'],'return_sale_total' => $data['grand_total'], 'return_id' => $sale_id), array('id' => $data['sale_id']));
            }

            if ($data['payment_status'] == 'partial' || $data['payment_status'] == 'paid' && !empty($payment)) {
                if (empty($payment['reference_no'])) {
                    $payment['reference_no'] = $this->site->getReference('pay');
                }
               
                $payment['sale_id']  = $sale_id;
                
                if ($payment['paid_by'] == 'gift_card') {
                    $this->db->update('gift_cards', array('balance' => $payment['gc_balance']), array('card_no' => $payment['cc_no']));
                    unset($payment['gc_balance']);
                    $this->db->insert('payments', $payment);
                } else {
                    if ($payment['paid_by'] == 'deposit') {
                        $customer = $this->site->getCompanyByID($data['customer_id']);
                        $this->db->update('companies', array('deposit_amount' => $payment['cc_holder']), array('id' => $data['customer_id']));
                        //$this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$payment['amount'])), array('id' => $customer->id));
                    }
                    $this->db->insert('payments', $payment);
                }
                if ($this->site->getReference('pay') == $payment['reference_no']) {
                    $this->site->updateReference('pay');
                }
                $this->site->syncSalePayments($sale_id);
            }
            
            if($syncQuantity) {                 
                $this->site->syncQuantity($sale_id);
            }            
            
            if ($this->Settings->synch_reward_points && $syncQuantity) {
                $this->sma->update_award_points($data['grand_total'], $data['customer_id'], $data['created_by']);
            }
            
            return $sale_id;
        }

        return false;
    }

    public function updateOrdersDeliveryStatus($order_id, array $updateItemsDelivery , $deliveryStatus){
        
        if ($this->db->update('orders', ['delivery_status'=>$deliveryStatus], array('id' => $order_id))) {
           
            if(is_array($updateItemsDelivery)){
                foreach ($updateItemsDelivery as $itm_id => $itemsStatus) {
                    
                    $this->db->update('order_items', $itemsStatus, array('id' => $itm_id));
                }//end foreach
            }//end if
            return true;
        }//end if
        
        return false;
    }
    
    public function getDeliveryItemByOrderID($order_id)
    {
        $q = $this->db->query("SELECT sum(delivered_quantity) as delivered , sum(quantity) as quantity FROM sma_order_items WHERE sale_id = '$order_id' ");
        if ($q->num_rows() > 0) {
             return $q->row();
        }

        return FALSE;
    }

    public function getDeliveryByOrderID($order_id)
    {
        $q = $this->db->get_where('deliveries', array('order_id' => $order_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }
    
    public function getPaymentsForOrder($order_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount,payments.transaction_id, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('order_id' => $order_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }
    
}//End Class
