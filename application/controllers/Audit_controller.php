<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @property CI_Input $input
 * @property User_model $User_model
 * @property Audit_logs_model $Audit_logs_model
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 */
class Audit_controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('User_model'); // Load the User_model
        $this->load->helper('audit');
        $this->load->model('Audit_logs_model');

    }


    public function show_audit_log_list(){
      $from_date = $this->input->get('from_date');
      $to_date = $this->input->get('to_date');
      $user_id = $this->input->get('user_id');
      $action_type = $this->input->get('action_type');
      $severity_level = $this->input->get('severity_level');
      $entity_type = $this->input->get('entity_type');

      $data['logs'] = $this->Audit_logs_model->log_list($from_date, $to_date, $user_id, $action_type, $severity_level, $entity_type);
      $data ['users'] = $this->User_model->get_all_user();
      $this->load->view('AuditLogs/logList', $data);
    }

}