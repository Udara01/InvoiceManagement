<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_logs_model extends CI_Model {

  public function __construct(){
    parent::__construct();
    $this->load->database();
  }

  public function log_action($data){
    $this->db->insert('audit_logs', $data);
  }

  
public function log_list($from_date = null, $to_date = null, $user_id = null, $action_type = null, $severity_level = null, $entity_type = null){
    $this->db->select('audit_logs.*, users.userName AS username');
    $this->db->from('audit_logs');
    $this->db->join('users', 'audit_logs.user_id = users.id', 'left');  

    if(!empty($from_date)){
        $this->db->where('created_at >=', $from_date . ' 00:00:00');  
    }
    if(!empty($to_date)){
        $this->db->where('created_at <=', $to_date . ' 23:59:59');  
    }

    if(!empty($user_id)){
        $this->db->where('user_id', $user_id);
    }

    if(!empty($action_type)){
        $this->db->where('action', $action_type);
    }

    if(!empty($severity_level)){
        $this->db->where('severity_level', $severity_level);
    }

    if(!empty($entity_type)){
        $this->db->where('entity_type', $entity_type);
    }

    $query = $this->db->get();
    return $query->result();
}

}