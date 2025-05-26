<?php

defined('BASEPATH') OR exit('No direct script access allowed');
require_once(APPPATH . '../vendor/autoload.php');

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
 * @property Invoice_return_model $Invoice_returns_model
 * @property CI_DB_query_builder|CI_DB_driver $db
 * 
 */


use Dompdf\Dompdf;
use Twilio\Rest\Client;


class Whatsapp_controller extends CI_Controller{

    public function __construct() {
        parent::__construct();
        $this->load->model('Invoice_model');
        $this->load->model('Item_model');
        $this->load->model('Customer_invoices_model');
        $this->load->model('Invoice_items_model');
        $this->load->model('Customer_model'); 
        $this->load->model('Invoice_returns_model');
        $this->load->library('form_validation');
        $this->load->library('Googledrive');
        $this->load->helper(array('form', 'url'));
        $this->load->model('Uploaded_files_model');
        $this->load->model('Shared_invoices_model');

        $this->config->load('twilio');
    }


  public function index() {
  }

/*
  public function invoice_pdf($id){

    $invoice = $this->Customer_invoices_model->getInvoiceWithDetails($id);
    if (!$invoice) {
      show_error("Invoice with ID $id not found.");
    }

    $invoice_items = $this->Customer_invoices_model->getInvoiceItems($id);
    $all_items = $this->Item_model->showItems(); // Fetch all item data
    $customers = $this->Customer_model->get_all_customers(); 

    // Load view and render as HTML
    $html = $this->load->view('Templates/invoice_template', [
        'invoice' => $invoice,
        'invoice_items' => $invoice_items,
        'all_items' => $all_items, 
        'customers' => $customers,
    ],true);

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();


    // Define save path
   /* $output = $dompdf->output();
    $pdfPath = FCPATH . "uploads/invoices/{$id}.pdf";
    file_put_contents($pdfPath, $output);*/

        // Save PDF
    /*$pdfPath = FCPATH . "uploads/invoices/{$id}.pdf";
    file_put_contents($pdfPath, $dompdf->output());

    // Return public URL
    //$url = base_url("uploads/invoices/{$id}.pdf");
    //echo json_encode(['url' => $url]);

    //$pdfPath = FCPATH . "uploads/invoices/{$id}.pdf";

    //$this->share_pdf_url($pdfPath, $id);

  }

*/


/*
    public function share_pdf_url($pdfPath, $id ){

    // Make sure the file was created
    if (!file_exists($pdfPath)) {
        return $this->output
            ->set_status_header(404)
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => 'PDF not found.']));
    }

    // Create public URL (base_url gives http://yourdomain.com/)
    $pdfUrl = base_url("uploads/invoices/{$id}.pdf");

    // Return JSON
    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['url' => $pdfUrl]));

    }
*/


    /*
    public function send_message() {
        $sid   = $this->config->item('twilio_account_sid');
        $token = $this->config->item('twilio_auth_token');
        $from  = $this->config->item('twilio_number');

        $to = 'whatsapp:+94720373597'; // Replace with your WhatsApp number

        $client = new Client($sid, $token);

        try {
            $client->messages->create(
                $to,
                [
                    'from' => $from,
                    'body' => 'Hello from CodeIgniter and Twilio WhatsApp!'
                ]
            );

            echo 'Message sent successfully!';
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
*/



public function invoice_pdf($id) {
    $invoice = $this->Customer_invoices_model->getInvoiceWithDetails($id);
    if (!$invoice) {
        return $this->output
            ->set_content_type('application/json')
            ->set_status_header(404)
            ->set_output(json_encode(['error' => 'Invoice not found.']));
    }

    // Load data and generate PDF 
    $invoice_items = $this->Customer_invoices_model->getInvoiceItems($id);
    $all_items = $this->Item_model->showItems(); 
    $customers = $this->Customer_model->get_all_customers(); 

    $html = $this->load->view('Templates/invoice_template', [
        'invoice' => $invoice,
        'invoice_items' => $invoice_items,
        'all_items' => $all_items, 
        'customers' => $customers,
    ], true);

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $pdfPath = FCPATH . "uploads/invoices/{$id}.pdf";
    file_put_contents($pdfPath, $dompdf->output());

    $filename = "{$id}.pdf";
    $folderId = '1XvRk8zhCi31yfZVAb7ZvZEQH1iAqoHau';
    $fileId = $this->googledrive->upload($filename, $pdfPath, $folderId);
    $mimeType = mime_content_type($pdfPath);

    $driveLink = "https://drive.google.com/uc?export=download&id={$fileId}";

    $to = $this->config->item('sender_number');

    $data = [
        'invoice_id'  => $id,
        'file_path' => $driveLink,
        'file_type'  => $mimeType,
        'file_id'    => $fileId,
        'shared_with' => $to,
    ];
    $this->Shared_invoices_model->save_shared_data($data);

    unlink($pdfPath);

    
    /*// Return JSON response
    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode(['url' => $driveLink]));*/

    $this->send_message($driveLink, $id);
}


    public function send_message($driveLink, $id) {
        $sid   = $this->config->item('twilio_account_sid');
        $token = $this->config->item('twilio_auth_token');
        $from  = $this->config->item('twilio_number');

        $to = $this->config->item('sender_number');

        $client = new Client($sid, $token);

        $body = "*Invoice #$id Available*\n\n"
      . "Please find your invoice attached below.\n\n"
      . "🔗 Download Invoice:\n{$driveLink}\n\n";

        try {
            $client->messages->create(
                $to,
                [
                    'from' => $from,
                    //'body' => $driveLink
                    'body' => $body

                ]
            );

            //echo 'Message sent successfully!';
            $this->session->set_flashdata('success', 'The Invoice link send successfully');
                redirect('Customer_invoice/print_invoice/' . $id); 
        } catch (Exception $e) {
            //echo 'Error: ' . $e->getMessage();
            $this->session->set_flashdata('error', 'Error sending message: ' . $e->getMessage());
            redirect('Customer_invoice/print_invoice/' . $id); 
        }
    }





















/*
public function invoice_pdf($id) {
    $invoice = $this->Customer_invoices_model->getInvoiceWithDetails($id);
    if (!$invoice) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => "Invoice with ID $id not found."]));
    }

    $invoice_items = $this->Customer_invoices_model->getInvoiceItems($id);
    $all_items = $this->Item_model->showItems();
    $customers = $this->Customer_model->get_all_customers();

    $html = $this->load->view('Templates/invoice_template', [
        'invoice' => $invoice,
        'invoice_items' => $invoice_items,
        'all_items' => $all_items,
        'customers' => $customers,
    ], true);

    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    $pdfPath = FCPATH . "uploads/invoices/{$id}.pdf";
    file_put_contents($pdfPath, $dompdf->output());
    

    // Upload and send via WhatsApp API
    $result = $this->upload_pdf_to_meta($pdfPath);

    if (isset($result['error'])) {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['error' => $result['error']]));
    } else {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => 'Invoice sent successfully!']));
    }
}




public function upload_pdf_to_meta($pdfPath){
    $token = "EAAH1rDx8kkkBO0ZBkbLYuk8LEqBdqKY1oADZC2F5BggtCYn4QZBUEytEJe8ZCkFbTaowkqKRMHexqXwItiZBHPqqi7OxLCfs9jlV3DgfRJADfc0En6noC42ML4MevSRzyweAipZBt5ra11c0TeccRNVihgzWTTR0ZAGq8mVnOgJoOIhyZAiiQjDoAfHX035NHQBfFw1tLkRnaZA275HZByyUCRGiU3LnrwqJpLPZCzI6wZDZD";
    $phoneNumberId = "632781819927328";

    $cfile = curl_file_create($pdfPath, 'application/pdf', basename($pdfPath));
    $postData = [
        'file' => $cfile,
        'messaging_product' => 'whatsapp'
    ];

    $ch = curl_init("https://graph.facebook.com/v18.0/{$phoneNumberId}/media");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$token}"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if (!isset($responseData['id'])) {
        return ['error' => 'Failed to upload PDF to Meta: ' . json_encode($responseData)];
    }

    $mediaId = $responseData['id'];

    return $this->pdf_to_customer($mediaId, $token, $phoneNumberId);
}




public function pdf_to_customer($mediaId, $token, $phoneNumberId){
    $recipientPhone = "94720373597"; // customer phone number (no +)

    $sendData = [
        "messaging_product" => "whatsapp",
        "to" => $recipientPhone,
        "type" => "document",
        "document" => [
            "id" => $mediaId,
            "caption" => "Here is your invoice"
        ]
    ];

    $ch = curl_init("https://graph.facebook.com/v18.0/{$phoneNumberId}/messages");
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => [
            "Authorization: Bearer {$token}",
            "Content-Type: application/json"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($sendData)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);
file_put_contents(APPPATH . 'logs/whatsapp_response.log', print_r($responseData, true), FILE_APPEND);


    if (isset($responseData['error'])) {
        return ['error' => 'Failed to send message: ' . json_encode($responseData['error'])];
    }

    return ['success' => 'Message sent successfully!'];
}




public function send_invoice_to_whatsapp($invoice_id)
{
    // Optional: allow for longer execution if needed
    ini_set('max_execution_time', 60);

    $phone_number = '94720373597';
    $access_token = 'EAAH1rDx8kkkBO732vnzXuTn9LZBn0eLZBmt8vukOiHnmfJkybYbsNNX73CNGIVmPNhWXXaPdjPnYZAiOA4Lyl4TFXx82FJPQaD3TYul8uknUxGf6jNjSVMkQ1j2m72tBYyS50bltFoZCixAzWUXOOsZAZB42SHdthXW1rYdTKZBfZBCDdYLJm1nYayXnW9AnSZBZA7kWxNn3cjZBzmtBNzLYnPulZAgRLnkVfds2D3AZD';
    $from_number_id = '632781819927328';

    $url = "https://graph.facebook.com/v19.0/$from_number_id/messages";

    //Template payload (example: hello_world template)
    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $phone_number,
        'type' => 'template',
        'template' => [
            'name' => 'hello_world', // Make sure this template is approved
            'language' => [
                'code' => 'en_US'
            ]
        ]
    ];

    // cURL setup
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $access_token
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // seconds

    $response = curl_exec($ch);

    // Error handling
    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        echo 'Error: ' . curl_error($ch);
        curl_close($ch);
        return;
    }

    curl_close($ch);

    $responseData = json_decode($response, true);

    // Logging the response
    error_log("WhatsApp cURL response: " . print_r($responseData, true));

    // Show result in browser (for dev)
    echo "<pre>"; print_r($responseData); echo "</pre>";
}
*/


}