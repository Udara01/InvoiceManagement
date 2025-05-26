<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Invoice - <?= $invoice->invoiceNo ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">


  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f8f9fa;
      color: #333;
    }

    .invoice-header {
      border-bottom: 2px solid #343a40;
      margin-bottom: 2rem;
      padding-bottom: 1rem;
    }

    .invoice-header h2 {
      font-weight: 700;
    }

    .invoice-meta p {
      margin: 0.25rem 0;
    }

    .table thead th {
      background-color: #343a40;
      color: white;
    }

    .table-striped tbody tr:nth-of-type(odd) {
      background-color: #f2f2f2;
    }

    .card {
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    }

    @media print {
      .no-print {
        display: none !important;
      }

      body {
        background: white;
      }

      .container {
        padding: 0;
      }
    }
  </style>
</head>

<body>
<?php $this->load->view('layouts/navbar'); ?>

<div class="container my-5">
  <div class="invoice-header d-flex justify-content-between align-items-center">
    <h2>Invoice</h2>
    <h5 class="text-muted">#<?= $invoice->invoiceNo ?></h5>
  </div>

  <?php if (isset($invoice)): ?>
  <div class="card p-4 mb-4">
    <div class="row invoice-meta">
      <div class="col-md-6">
        <p><strong>Customer:</strong> 
          <a href="<?= site_url('Customer_controller/customer_transactions/' . $invoice->customer_id) ?>" 
            class="text-decoration-none text-primary">
            <?= $invoice->customer_name ?>
          </a>
        </p>

        <p><strong>Address:</strong> <?= $invoice->customer_address ?></p>
        <p><strong>Phone:</strong> <?= $invoice->customer_phone ?></p>
      </div>
      <div class="col-md-6 text-md-end">
        <p><strong>Total Amount:</strong> Rs.<?= number_format($invoice->total_amount, 2) ?></p>
        <p><strong>Created By:</strong> <?= $invoice->created_by_name ?> at <?= $invoice->created_at ?></p>
        <p><strong>Updated By:</strong> <?= $invoice->updated_by_name ?> at <?= $invoice->updated_at ?></p>
      </div>
    </div>
  </div>

  <h4 class="mb-3">Invoice Items</h4>
  <div class="table-responsive">
    <table class="table table-bordered table-striped">
      <thead class="text-center">
        <tr>
          <th>Item Name</th>
          <th>Description</th>
          <th>Unit Price</th>
          <th>Quantity</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($invoice_items as $item): ?>
        <tr>
          <td class="text-center"><?= $item->itemName ?></td>
          <td><?= $item->productDescription ?></td>
          <td class="text-end">Rs. <?= number_format($item->price, 2) ?></td>
          <td class="text-end"><?= $item->quantity ?></td>
          <td class="text-end">Rs. <?= number_format($item->price * $item->quantity, 2) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="fw-bold bg-light">
        <tr>
          <td colspan="3" class="text-center">Total</td>
          <td class="text-end"><?= number_format(array_sum(array_column($invoice_items, 'quantity')), 2) ?></td>
          <td class="text-end">Rs. <?= number_format(array_reduce($invoice_items, fn($sum, $i) => $sum + ($i->price * $i->quantity), 0), 2) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>

  <?php else: ?>
    <div class="alert alert-warning">Invoice not found!</div>
  <?php endif; ?>

    <div class="text-center mt-4">
      <button class="btn btn-dark btn-icon no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print
      </button>
      <a href="<?= site_url('ReturnInvoice_controller/return_invoice_entry/' . $invoice->id) ?>" 
         class="btn btn-warning btn-icon no-print">
        <i class="fas fa-undo-alt"></i> Return Invoice
      </a>
      <!--a href="<?= site_url('Whatsapp_controller/invoice_pdf/' . $invoice->id); ?>" 
         class="btn btn-success btn-icon no-print">
        <i class="fab fa-whatsapp"></i> Send via WhatsApp
      </a-->

      <button class="btn btn-primary btn-sm px-4 py-2 no-print" data-bs-toggle="modal" data-bs-target="#shareModal">
          <i class="fas fa-share-alt me-2"></i> Share Invoice
      </button>
    </div>

    
    <div class="text-center mt-3">
      <a href="<?= site_url('invoicelist') ?>" class="btn btn-secondary no-print">
        <i class="fas fa-arrow-left"></i> Back to List
      </a>
    </div>

  </div>
</div>

<!-- Share Modal -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-header">
        <h5 class="modal-title" id="shareModalLabel">Share Invoice</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <div class="d-flex justify-content-center gap-4">
          <a href="<?= site_url('Whatsapp_controller/invoice_pdf/' . $invoice->id); ?>"
             class="text-success fs-2" title="WhatsApp">
            <i class="fab fa-whatsapp"></i>
          </a>
          <a href="javascript:void(0);" class="text-danger fs-2" title="Email">
            <i class="fas fa-envelope"></i>
          </a>
          <a href="javascript:void(0);" class="text-primary fs-2" title="Telegram">
            <i class="fab fa-telegram-plane"></i>
          </a>
          <a href="javascript:void(0);" class="text-primary fs-2" title="Messenger">
            <i class="fab fa-facebook-messenger"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function shareInvoice(invoiceId) {
    fetch(`/Whatsapp_controller/invoice_pdf/${invoiceId}`)
        .then(res => res.json())
        .then(data => {
            if (data.url) {
                const msg = `*Invoice Ready!*\n\nYou can download your invoice from the link below:\n ${data.url}\n\nThank you!`;
                const waUrl = `https://wa.me/?text=${encodeURIComponent(msg)}`;
                window.open(waUrl, '_blank');
            } else {
                alert('Error: Could not share invoice.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error: Could not connect to server.');
        });
}
</script>
</body>
</html>
