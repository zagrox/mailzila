<?php
require_once __DIR__ . '/../../config/init.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('/auth/login');
}

// Get domains from database
try {
    $sql = "SELECT * FROM domains WHERE user_id = ? ORDER BY created_at DESC";
    $domains = $db->select($sql, [$_SESSION['user_id']]);

    // Get verification status for each domain
    foreach ($domains as &$domain) {
        try {
            $verificationStatus = $api->getDomainVerificationStatus($domain['domain_name']);
            $domain['verification_details'] = $verificationStatus;
        } catch (Exception $e) {
            $domain['verification_details'] = ['error' => $e->getMessage()];
        }
    }
} catch (Exception $e) {
    $error = "Error fetching domains: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $domainName = trim($_POST['domain_name']);
        $trackingType = $_POST['tracking_type'] ?? 'http';

        // Validate domain name
        if (!filter_var($domainName, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new Exception("Invalid domain name format");
        }

        // Add domain to ElasticEmail
        $result = $api->addDomain($domainName, $trackingType);

        // Add domain to database
        $sql = "INSERT INTO domains (user_id, domain_name, tracking_type) VALUES (?, ?, ?)";
        $db->query($sql, [$_SESSION['user_id'], $domainName, $trackingType]);

        // Get verification records
        $spfRecord = $api->getDomainSpfRecord($domainName);
        $dkimRecord = $api->getDomainDkimRecord($domainName);

        // Update records in database
        $sql = "UPDATE domains SET spf_record = ?, dkim_record = ? WHERE domain_name = ? AND user_id = ?";
        $db->query($sql, [
            json_encode($spfRecord),
            json_encode($dkimRecord),
            $domainName,
            $_SESSION['user_id']
        ]);

        $success = "Domain added successfully. Please configure DNS records to verify the domain.";
        
        // Redirect to prevent form resubmission
        header('Location: ' . APP_URL . '/domains?success=' . urlencode($success));
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1>Domain Management</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDomainModal">
                    <i class="fas fa-plus"></i> Add Domain
                </button>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <?php if (empty($domains)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-globe fa-3x text-muted mb-3"></i>
                            <h3>No Domains Added</h3>
                            <p>Add your first domain to start sending emails from your own domain.</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDomainModal">
                                Add Domain
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Domain</th>
                                        <th>Status</th>
                                        <th>Tracking</th>
                                        <th>Added</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($domains as $domain): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($domain['domain_name']); ?></td>
                                            <td>
                                                <?php if ($domain['is_verified']): ?>
                                                    <span class="badge bg-success">Verified</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Pending</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($domain['tracking_type']); ?></td>
                                            <td><?php echo date('M j, Y', strtotime($domain['created_at'])); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info me-2" 
                                                        onclick="showDomainDetails('<?php echo htmlspecialchars($domain['domain_name']); ?>')">
                                                    <i class="fas fa-info-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteDomain('<?php echo htmlspecialchars($domain['domain_name']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Domain Modal -->
<div class="modal fade" id="addDomainModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Domain</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="domain_name" class="form-label">Domain Name</label>
                        <input type="text" class="form-control" id="domain_name" name="domain_name" required
                               placeholder="example.com">
                        <div class="form-text">Enter your domain name without http:// or www</div>
                    </div>
                    <div class="mb-3">
                        <label for="tracking_type" class="form-label">Tracking Type</label>
                        <select class="form-select" id="tracking_type" name="tracking_type">
                            <option value="http">HTTP</option>
                            <option value="https">HTTPS</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Domain</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Domain Details Modal -->
<div class="modal fade" id="domainDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Domain Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="domainDetailsContent">
                    Loading...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showDomainDetails(domainName) {
    const modal = new bootstrap.Modal(document.getElementById('domainDetailsModal'));
    modal.show();
    
    fetch(`${APP_URL}/api/domains/details.php?domain=${encodeURIComponent(domainName)}`)
        .then(response => response.json())
        .then(data => {
            const content = document.getElementById('domainDetailsContent');
            if (data.error) {
                content.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                return;
            }

            let html = `
                <div class="mb-4">
                    <h6>SPF Record</h6>
                    <div class="alert alert-info">
                        <code>${data.spf_record}</code>
                    </div>
                </div>
                <div class="mb-4">
                    <h6>DKIM Record</h6>
                    <div class="alert alert-info">
                        <code>${data.dkim_record}</code>
                    </div>
                </div>
                <div>
                    <h6>Verification Status</h6>
                    <ul class="list-group">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            SPF
                            <span class="badge ${data.verification.spf ? 'bg-success' : 'bg-warning'}">
                                ${data.verification.spf ? 'Verified' : 'Pending'}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            DKIM
                            <span class="badge ${data.verification.dkim ? 'bg-success' : 'bg-warning'}">
                                ${data.verification.dkim ? 'Verified' : 'Pending'}
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Tracking
                            <span class="badge ${data.verification.tracking ? 'bg-success' : 'bg-warning'}">
                                ${data.verification.tracking ? 'Verified' : 'Pending'}
                            </span>
                        </li>
                    </ul>
                </div>
            `;
            content.innerHTML = html;
        })
        .catch(error => {
            document.getElementById('domainDetailsContent').innerHTML = `
                <div class="alert alert-danger">Error loading domain details: ${error.message}</div>
            `;
        });
}

function deleteDomain(domainName) {
    if (!confirm('Are you sure you want to delete this domain? This action cannot be undone.')) {
        return;
    }

    fetch(`${APP_URL}/api/domains/delete.php`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': '<?php echo csrf_token(); ?>'
        },
        body: JSON.stringify({ domain: domainName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Failed to delete domain');
        }
    })
    .catch(error => {
        alert('Error deleting domain: ' + error.message);
    });
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 