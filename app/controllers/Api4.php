<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Api4 extends MY_Controller {

    private $api_private_key = '';
    private $posVersion = '';
    private $pos_type = 'amstead';
    private $ci = '';

    public function __construct() {
        parent::__construct();

        $this->load->model('Superadmin_model');

        $this->posVersion = json_decode($this->Settings->pos_version);
        $this->pos_type = $this->Settings->pos_type;
        $this->api_private_key = isset($this->Settings->api_privatekey) && !empty($this->Settings->api_privatekey) ? $this->Settings->api_privatekey : $config->config['api3_private_key'];

        $this->ci = $ci = get_instance();
        $config = $ci->config;
        $this->merchant_phone = isset($config->config['merchant_phone']) && !empty($config->config['merchant_phone']) ? $config->config['merchant_phone'] : NULL;
        
        if ($this->posVersion->version < 1.03) {
            $data['status'] = 'ERROR';
            $data['error_code'] = 404;
            $data['current_pos_version'] = $this->posVersion->version;
            $data['pos_version'] = $this->posVersion->version;
            $data['pos_type'] = $this->pos_type;
            $data['api_access_status'] = $this->Settings->api_access ? 'Active' : 'Blocked';
            $data['mag'] = 'API required the pos version 1.03 or above.';
            echo $this->json_op($data);
            exit;
        }//end if

        if (!$this->Settings->api_access) {
            $data['status'] = 'ERROR';
            $data['error_code'] = 405;
            $data['current_pos_version'] = $this->posVersion->version;
            $data['pos_version'] = $this->posVersion->version;
            $data['pos_type'] = $this->pos_type;
            $data['api_access_status'] = $this->Settings->api_access ? 'Active' : 'Blocked';
            $data['mag'] = 'API access is blocked.';
            echo $this->json_op($data);
            exit;
        }//end if

        if (!isset($_POST)) {
            $data['status'] = 'ERROR';
            $data['error_code'] = 101;
            $data['mag'] = 'Invalid api request method';
            $data['private_key_msg'] = 'mismatch';
            echo $this->json_op($data);
            exit;
        } else {

            $privatekey = $this->input->post('privatekey');
            $this->action = $this->input->post('action');
            
            if ($this->api_private_key == NULL) {
                $data['status'] = 'ERROR';
                $data['error_code'] = 100;
                $data['mag'] = 'POS API private key not available or generated';
                $data['private_key_msg'] = 'mismatch';
                echo $this->json_op($data);
                exit;
            } elseif ($this->api_private_key !== $privatekey) {
                $data['status'] = 'ERROR';
                $data['error_code'] = 102;
                $data['mag'] = 'Private key mismatch';
                $data['private_key_msg'] = 'mismatch';
                echo $this->json_op($data);
                exit;
            }
        }//end else
    }
    
     
    public function index() { 
        
        $action = $this->input->post('action');

        $this->synchdate = ($this->input->post('synchdate') !== '') ? $this->input->post('synchdate') : NULL;

        $this->Superadmin_model->setLastSynchTime($this->synchdate);

        $data = $this->getSuperadminUpdatesData($action);
        
        $this->json_op($data);
    }
    
    public function getSuperadminUpdatesData($action) {
        
        $tables = $this->Superadmin_model->getSynchTables($action);       
        
        $data['status'] = "ERROR";
         
        if(is_array($tables)) {
            
            foreach ($tables as $key => $tableName) {                
                $tableNameData = ($tableName == 'sma_users') ? 'sma_pos_users' : $tableName;
                $data['data'][$tableNameData] = $this->Superadmin_model->getSynchData($tableName, $action);
            }
            
            if($data){
                $data['status'] = "SUCCESS";
            }
            
            return $data;
        }
        return false;
    }

    private function json_op($arr) {
        $arr = is_array($arr) ? $arr : array();
        echo @json_encode($arr);
        exit;
    }       

  
    
}

?>