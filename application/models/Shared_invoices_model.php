<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Shared_invoices_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }


    public function save_shared_data($data){
      return $this->db->insert('shared_invoices', $data);
    }
  }