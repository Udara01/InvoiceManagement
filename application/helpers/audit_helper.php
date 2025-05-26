<?php
/*

public function log_audit($action, $entity_type, $entity_id = null, $details = '') {
    $CI =& get_instance();
    $CI->load->model('Audit_log_model');

    $user_id = $CI->session->userdata('user_id');
    $username = $CI->session->userdata('username');

    $data = [
        'user_id'     => $user_id,
        'username'    => $username,
        'action'      => $action,
        'entity_type' => $entity_type,
        'entity_id'   => $entity_id,
        'details'     => $details,
        'ip_address'  => $CI->input->ip_address(),
        'user_agent'  => $CI->input->user_agent(),
    ];

    $CI->Audit_log_model->log_action($data);
}
*/

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('log_audit')) {
    function log_audit($action, $entity_type, $entity_id = null, $details = '', $severity = 'info') {
        
        $CI =& get_instance();
        $CI->load->model('Audit_logs_model');

        $user_id = $CI->session->userdata('user_id');
        $username = $CI->session->userdata('username');

        $data = [
            'user_id'     => $user_id,
            'username'    => $username,
            'action'      => $action,
            'entity_type' => $entity_type,
            'entity_id'   => $entity_id,
            'details'     => $details,
            'severity_level' => $severity,
            'ip_address'  => $CI->input->ip_address(),
            'user_agent'  => $CI->input->user_agent(),
        ];

        $CI->Audit_logs_model->log_action($data);
    }
}
