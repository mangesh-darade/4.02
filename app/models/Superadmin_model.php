<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Superadmin_model extends CI_Model {
    
    private $lastSynchTime = NULL;
    
    public function __construct() {
        parent::__construct();
    }

    public function getSynchData($tableName=NULL, $action=NULL) {
                
        if(empty($tableName)) return false;
        
        if( in_array($action , ['synch_updates_deleted', 'synch_masters_deleted'])) {
            $tableFields = 'id';
            $this->lastSynchTime = NULL;
        } else {
            $tableFields = $this->getSynchTablesFields($tableName);
        }       
        
        $data = $this->getTableData($tableName, $tableFields, $this->lastSynchTime);
        
        return $data;
    }
    
    public function getTableData($tableName, $tableFields, $lastSynchTime) {
        
        if(!empty($tableName)){
            
            $tableFields = !empty($tableFields) ? $tableFields : '*';
            
            $lastSynchDate  = ($lastSynchTime) ? substr($lastSynchTime,0,10) : '';
            
            $where = ($lastSynchDate) ? " WHERE updated_at >= '$lastSynchDate' " : '';
        
            $q = $this->db->query("SELECT $tableFields FROM $tableName $where ");
                        
            if ($q->num_rows() > 0) {                
                if($tableFields == 'id'){
                    foreach ($q->result() as $row) {
                        $data[] = $row->id;
                    }
                    return ($data);
                } else {                
                    return $q->result();  
                }
            } else {
               return false;
            }
            return $data;
        }
        
        return false;
    }
    
    public function setLastSynchTime($lastSynchTime) {
        
        if($lastSynchTime) {
            $date = new DateTime();
            $date->setTimestamp($lastSynchTime);
            $this->lastSynchTime = $date->format('Y-m-d H:i:s');
        } else {
            $this->lastSynchTime = NULL;
        }
    }
    
    public function getSynchTables($dataTypes='synch_updates') {
                
        $tables['synch_masters'] = $tables['synch_masters_deleted'] = array('sma_products', 'sma_product_variants', 'sma_categories', 'sma_brands', 'sma_companies', 'sma_customer_groups', 'sma_warehouses', 'sma_variants', 'sma_units', 'sma_users', 'sma_customer_groups');                
         
        $tables['synch_updates'] = $tables['synch_updates_deleted'] = array('sma_sales', 'sma_sale_items', 'sma_sales_items_tax', 'sma_warehouses_products', 'sma_warehouses_products_variants', 'sma_purchases', 'sma_purchase_items', 'sma_purchase_items_tax', 'sma_payments'); 
        
        return $tables[$dataTypes];
    }
    
    public function getSynchTablesFields($tableName) {
        
       $selectFields['sma_sales'] = 'id AS invoice_sale_id, invoice_no, date, reference_no, customer_id, customer, biller_id, biller, seller_id, seller, warehouse_id, note, staff_note, total, product_discount, order_discount_id, total_discount, order_discount, product_tax, order_tax_id, order_tax, total_tax, shipping, grand_total, sale_status, payment_status, payment_term, due_date, created_by, updated_by, updated_at, total_items, pos, paid, return_id, surcharge, attachment, return_sale_ref, sale_id, return_sale_total, rounding, eshop_sale, offline_sale, offline_reference_no, offline_payment_id, offline_transaction_type, cf1, cf2, delivery_status, eshop_order_alert_status, offlinepos_sale_reff, offer_category, offer_description, kot_tokan, cgst, sgst, igst';
       $selectFields['sma_sale_items'] = 'id AS invoice_sale_item_id, sale_id, product_id, product_code, article_code, product_name, product_type, option_id, shade_id, warehouse_id, tax_method, tax_rate_id, tax, mrp, real_unit_price, unit_discount, unit_tax, unit_price, net_unit_price, invoice_unit_price, invoice_net_unit_price, quantity, item_discount, item_tax, net_price, invoice_total_net_unit_price, subtotal, discount, serial_no, sale_item_id, product_unit_id, product_unit_code, unit_quantity, cf1, cf2, cf1_name, cf2_name, hsn_code, note, delivery_status, pending_quantity, delivered_quantity, offlinepos_sale_reff, offlinepos_saleitem_reff, gst_rate,cgst, sgst, igst';
       $selectFields['sma_sales_items_tax'] = 'id AS invoice_sale_item_tax_id, item_id, sale_id, attr_code, attr_name, attr_per, tax_amount';
       
       $selectFields['sma_purchases'] = 'id AS reference_purchase_id, reference_no, date, supplier_id, supplier, warehouse_id, note, total, product_discount, order_discount_id, order_discount, total_discount, product_tax, order_tax_id, order_tax, total_tax, shipping, grand_total, paid, status, payment_status, created_by, updated_by, updated_at, attachment, payment_term, due_date, return_id, surcharge, return_purchase_ref, purchase_id, return_purchase_total, cgst, sgst, igst';
       $selectFields['sma_purchase_items'] = 'id AS reference_purchase_item_id, purchase_id, transfer_id, adjustment_id, product_id, product_code, product_name, option_id, shade_id, net_unit_cost, quantity, warehouse_id, item_tax, tax_rate_id, tax, tax_method, discount, item_discount, expiry, subtotal, quantity_balance, date, status, unit_cost, real_unit_cost, quantity_received, supplier_part_no, purchase_item_id, product_unit_id, product_unit_code, unit_quantity, hsn_code, batch_number, gst_rate,cgst, sgst, igst';
       $selectFields['sma_purchase_items_tax'] = 'id AS reference_purchase_item_tax_id, item_id, purchase_id, attr_code, attr_name, attr_per, tax_amount';
       
       $selectFields['sma_warehouses'] = 'id AS warehouse_id, code, name, address, phone, email, price_group_id';
       $selectFields['sma_warehouses_products'] = 'id AS warehouse_product_id, product_id, warehouse_id, quantity, rack, avg_cost';
       $selectFields['sma_warehouses_products_variants'] = 'id AS warehouse_product_variant_id, option_id, product_id, warehouse_id, quantity, rack, group_id';
       
       $selectFields['sma_payments'] = 'id AS payment_id, date, sale_id, return_id, purchase_id, reference_no, transaction_id, paid_by, cheque_no, cc_no, amount, currency, created_by, attachment, type, note, pos_paid, pos_balance';
       
       $selectFields['sma_companies'] = 'id AS company_id, group_id, group_name, customer_group_id, customer_group_name, name, company, pan_card, address, city, state, state_code, postal_code, country, phone, email, invoice_footer, payment_term, award_points, deposit_amount, price_group_id, price_group_name, dob, gstn_no';
       $selectFields['sma_categories'] = 'id AS category_id, code, name, image, parent_id';
       $selectFields['sma_brands'] = 'id AS brand_id, code, name, image';
       $selectFields['sma_customer_groups'] = 'id AS customer_group_id, name, percent';
       
       $selectFields['sma_users'] = 'id AS user_id, email, active, first_name, last_name, company, phone, group_id';
       
       $selectFields['sma_products'] = 'id AS product_id, code, article_code, name, unit, cost, price, mrp, alert_quantity, image, category_id, subcategory_id, cf1, cf2, quantity, tax_rate, track_quantity, details, warehouse, barcode_symbology, file, product_details, tax_method, type, sale_unit, purchase_unit, brand, hsn_code, is_featured';
       $selectFields['sma_product_variants'] = 'id AS product_varient_id, product_id, name, cost, price, quantity, group_id';
       $selectFields['sma_variants'] = 'id AS variant_id, name, group_id';
       
       $selectFields['sma_units'] = 'id AS unit_id, code, name, base_unit, operator, unit_value, operation_value';
            
       
       return $selectFields[$tableName];
       
    }
    
    
}
