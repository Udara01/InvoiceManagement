<?php

defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @property CI_Input $input
 * @property Item_model $Item_model
 * @property Invoice_model $Invoice_model
 * @property CI_DB_query_builder $db
 * @property CI_Loader $load
 * @property CI_Session $session
 * @property CI_output $output
 * @property CI_Router $router
 * @property Invoice_items_model $Invoice_items_model
 * @property Customer_invoices_model $Customer_invoices_model
 * @property Customer_model $Customer_model
 * @property Invoice_returns_model $Invoice_returns_model
 * @property Uploaded_files_model $Uploaded_files_model
 * @property CI_DB_query_builder|CI_DB_driver $db
 * @property CI_Upload $upload
 * @property GoogleDrive $googledrive
 * 
 */

class GoogleDriveUpload_controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Invoice_model');
        $this->load->model('Item_model');
        $this->load->model('Customer_invoices_model');
        $this->load->model('Invoice_items_model');
        $this->load->model('Customer_model'); 
        $this->load->model('Invoice_returns_model');
        $this->load->model('Uploaded_files_model');
        $this->load->library('form_validation');
        $this->load->library('Googledrive');
        $this->load->helper(array('form', 'url'));
        $this->load->helper('audit');

    }


    public function index(){
      $this->load->view('Drive/upload');
    }

/**
 * This is the method for handles file upload from the user and uploads it to Google Drive.
 *
 * Steps will be,
 * 1. Define configuration options for CodeIgniter's Upload Library (local upload settings).
 * 2. Load and initialize the Upload Library with those settings.
 * 3. Check if file upload was successful:
 *    - If failed: Show the upload form again with error messages.
 *    - If successful:
 *      a. Retrieve the uploaded file's name and full local path.
 *      b. Define the target Google Drive folder ID.
 *      c. Call the custom GoogleDrive library's `upload()` method to upload the file.
 *      d. Get the returned Google Drive file ID and generate a viewable link.
 *      e. Extract the MIME type of the file (e.g., PDF, JPEG, etc.).
 *      f. Save file information (name, ID, type, link) into the database via `Uploaded_files_model`.
 *      g. Set a success flash message and redirect to the list view.
 */
public function do_upload() {
    // Step 1: Configuration for local file upload
    $config['upload_path']   = './uploads/';       // Temporary upload directory (within the project)
    $config['allowed_types'] = '*';                // Allow all file types (can be customized)
    $config['max_size']      = 10000;              // Max file size in KB (10000KB = 10MB)

    // Step 2: Load the Upload Library with the above config
    $this->load->library('upload', $config);

    // Step 3: Attempt to upload the file
    if (!$this->upload->do_upload('file')) {
        // Upload failed: Show the upload view with error messages
        $error = array('error' => $this->upload->display_errors());
        $this->load->view('Drive/upload', $error);

    } else {
        // Upload success: Process the uploaded file
        $data = $this->upload->data(); // Get details about the uploaded file

        $filename = $data['file_name'];     
        $filepath = $data['full_path'];  
        $folderId = '1zl3rO1Lr6DUP74Uy2FJlPj4PG3m_LzVV'; // Target Google Drive folder ID

        // Upload to Google Drive using the custom library
        $fileId = $this->googledrive->upload($filename, $filepath, $folderId);

        //Generate a public Drive view link
        $driveLink = "https://drive.google.com/file/d/{$fileId}/view";

        // Get MIME type of the file 
        $mimeType = mime_content_type($filepath);

        // Prepare data to save in database
        $data = [
            'file_name'  => $filename,
            'mime_type'  => $mimeType,
            'file_id'    => $fileId,
            'drive_link' => $driveLink,
            'uploaded_by' => $this->session->userdata('user_id'),
        ];

        // Save the uploaded file info to Uploaded_files table
        $this->Uploaded_files_model->save_file_data($data);

        /*
        $this->load->view('Drive/upload_success', [
            'fileId'    => $fileId,
            'driveLink' => $driveLink,
        ]);
        */
        // Delete the file from local uploads folder
        unlink($filepath);

        // Set a flash message and redirect
        $this->session->set_flashdata('success', 'File uploaded successfully to Google Drive.');


        //Log adding 
        log_audit('Create', 'File', null, 'Upload file to google drive'. $filename, 'Info');

        redirect('GoogleDriveUpload_controller/list_uploads');
    }
}


    public function list_uploads() {
      $data['files'] = $this->Uploaded_files_model->get_file_date();
      $this->load->view('Drive/list_files', $data);
    }


/*
    public function delete_file($fileId){

        if($this->googledrive->delete($fileId)){

            $this->Uploaded_files_model->delete_by_file_id($fileId);

            $this->session->set_flashdata('success', 'File deleted successfully from Google Drive.');
        }

        else{
            $this->session->set_flashdata('error', 'Failed to delete file from Google Drive.');
        }

        redirect('GoogleDriveUpload_controller/list_uploads');
    }
*/



/**
 * This is an method for the delete an file on the Google drive.
 * 
 * steps will be,
 * first check the user login is admin or not. if admin send the fileid saved on the db to the library and delete the file from the Google Drive folder and then that file info also delete from the database table 
 */
    public function delete_file($fileId){

        $currentUserRole = $this->session->userdata('user_role'); // get logged in user

        log_message('debug', 'Current user role: ' . $currentUserRole);

        if($currentUserRole == 'admin'){
            if($this->googledrive->delete($fileId)){

            $this->Uploaded_files_model->delete_by_file_id($fileId);

            $this->session->set_flashdata('success', 'File deleted successfully from Google Drive.');
            //Log adding 
            log_audit('Delete', 'File', $fileId, 'Delete file from google drive', 'Critical');

            }
            else{
                $this->session->set_flashdata('error', 'Failed to delete file from Google Drive.');
            }
            redirect('GoogleDriveUpload_controller/list_uploads');
        }

        else{
            $this->session->set_flashdata('error', 'You do not have permission to delete this file.');
        }
        
        redirect('GoogleDriveUpload_controller/list_uploads');
    }
}