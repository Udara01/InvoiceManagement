<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">

<style> 
    td {
  max-width: 200px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

</style>
<div class="filter-section">
    <form method="get" action="" class="filter-form">
    <div class="filter-group">
        <label>From Date</label>
        <input type="date" name="from_date" value="<?= htmlspecialchars($_GET['from_date'] ?? '') ?>" class="form-control">
    </div>
    
    <div class="filter-group">
        <label>To Date</label>
        <input type="date" name="to_date" value="<?= htmlspecialchars($_GET['to_date'] ?? '') ?>" class="form-control">
    </div>
    
    <div class="filter-group">
        <label>User</label>
        <select name="user_id" class="form-select">
        <option value="">All Users</option>
        <?php foreach ($users as $user): ?>
            <option value="<?= $user->id ?>" <?= (($_GET['user_id'] ?? '') == $user->id) ? 'selected' : '' ?>>
            <?= htmlspecialchars($user->userName) ?>
            </option>
        <?php endforeach; ?>
        </select>
    </div>
    
    <div class="filter-group">
        <label>Action type</label>
        <select name="action_type" class="form-select">
        <option value="">All Action types</option>
        <option value="login" <?= (($_GET['action_type'] ?? '') == 'login') ? 'selected' : '' ?>>Login</option>
        <option value="logout" <?= (($_GET['action_type'] ?? '') == 'logout') ? 'selected' : '' ?>>Logout</option>
        <option value="create" <?= (($_GET['action_type'] ?? '') == 'create') ? 'selected' : '' ?>>Create</option>
        <option value="update" <?= (($_GET['action_type'] ?? '') == 'update') ? 'selected' : '' ?>>Update</option>
        <option value="delete" <?= (($_GET['action_type'] ?? '') == 'delete') ? 'selected' : '' ?>>Delete</option>
        </select>
    </div>

     <div class="filter-group">
        <label>Severity level</label>
        <select name="severity_level" class="form-select">
        <option value="">Severity types</option>
        <option value="info" <?= (($_GET['severity_level'] ?? '') == 'info') ? 'selected' : '' ?>>Info</option>
        <option value="warning" <?= (($_GET['severity_level'] ?? '') == 'warning') ? 'selected' : '' ?>>Warning</option>
        <option value="critical" <?= (($_GET['severity_level'] ?? '') == 'critical') ? 'selected' : '' ?>>Critical</option>
        </select>
    </div>

    <div class="filter-group">
        <label>Entity type</label>
        <select name="entity_type" class="form-select">
        <option value="">Entity types</option>
        <option value="user" <?= (($_GET['entity_type'] ?? '') == 'user') ? 'selected' : '' ?>>User</option>
        <option value="product" <?= (($_GET['entity_type'] ?? '') == 'product') ? 'selected' : '' ?>>Product</option>
        <option value="invoice" <?= (($_GET['entity_type'] ?? '') == 'invoice') ? 'selected' : '' ?>>Invoice</option>
        </select>
    </div>
    
    <div class="filter-group filter-actions">
        <button type="submit" class="btn btn-primary">
        <i class="bi bi-funnel"></i> Filter
        </button>
        <a href="<?= current_url() ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-counterclockwise"></i> Reset
        </a>
    </div>
    </form>
</div>




<table id="auditTable" class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Date</th>
            <th>User</th>
            <th>Action</th>
            <th>Entity</th>
            <th>Entity ID</th>
            <th>Details</th>
            <th>Severity</th>
            <th>Browser</th>
            <th>IP</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= $log->created_at ?></td>
            <td><?= $log->username ?></td>
            <td><?= $log->action ?></td>
            <td><?= $log->entity_type ?></td>
            <td><?= $log->entity_id ?></td>
            <td><?= $log->details ?></td>
            <td><?= $log->severity_level ?></td>
            <td><?= $log->user_agent ?></td>
            <td><?= $log->ip_address ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
