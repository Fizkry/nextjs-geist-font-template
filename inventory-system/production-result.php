<?php include 'includes/header.php'; ?>

<?php
// Handle form submission
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $plan_id = $_POST['plan_id'];
        $actual_quantity = $_POST['actual_quantity'];
        $result_date = $_POST['result_date'];
        $notes = $_POST['notes'];
        
        $stmt = $pdo->prepare("INSERT INTO production_results (plan_id, actual_quantity, result_date, notes) VALUES (?, ?, ?, ?)");
        $stmt->execute([$plan_id, $actual_quantity, $result_date, $notes]);
        
        // Update production plan status to completed
        $stmt = $pdo->prepare("UPDATE production_plans SET status = 'completed' WHERE id = ?");
        $stmt->execute([$plan_id]);
        
        header("Location: production-result.php?success=1");
        exit();
    }
    
    if ($action == 'edit') {
        $id = $_POST['id'];
        $actual_quantity = $_POST['actual_quantity'];
        $result_date = $_POST['result_date'];
        $notes = $_POST['notes'];
        
        $stmt = $pdo->prepare("UPDATE production_results SET actual_quantity = ?, result_date = ?, notes = ? WHERE id = ?");
        $stmt->execute([$actual_quantity, $result_date, $notes, $id]);
        
        header("Location: production-result.php?success=2");
        exit();
    }
    
    if ($action == 'delete') {
        $id = $_POST['id'];
        
        // Get plan_id before deleting
        $stmt = $pdo->prepare("SELECT plan_id FROM production_results WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            // Delete production result
            $stmt = $pdo->prepare("DELETE FROM production_results WHERE id = ?");
            $stmt->execute([$id]);
            
            // Update plan status back to planned
            $stmt = $pdo->prepare("UPDATE production_plans SET status = 'planned' WHERE id = ?");
            $stmt->execute([$result['plan_id']]);
        }
        
        header("Location: production-result.php?success=3");
        exit();
    }
}

// Get production results with plan details
$stmt = $pdo->query("
    SELECT pr.*, pp.plan_date, pp.planned_quantity, i.name as item_name, i.unit, c.name as category_name
    FROM production_results pr
    JOIN production_plans pp ON pr.plan_id = pp.id
    JOIN items i ON pp.item_id = i.id
    JOIN categories c ON i.category_id = c.id
    ORDER BY pr.result_date DESC, pr.created_at DESC
");
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get available production plans (completed status)
$stmt = $pdo->query("
    SELECT pp.*, i.name as item_name, i.unit, c.name as category_name
    FROM production_plans pp
    JOIN items i ON pp.item_id = i.id
    JOIN categories c ON i.category_id = c.id
    WHERE pp.status IN ('planned', 'in_progress')
    ORDER BY pp.plan_date DESC
");
$availablePlans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-chart-line"></i> Production Result</h2>
        <p class="text-muted">Input dan kelola hasil produksi berdasarkan planning yang telah dibuat</p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        <?php
        switch ($_GET['success']) {
            case '1': echo 'Hasil produksi berhasil ditambahkan!'; break;
            case '2': echo 'Hasil produksi berhasil diupdate!'; break;
            case '3': echo 'Hasil produksi berhasil dihapus!'; break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo count($results); ?></h4>
                        <p class="mb-0">Total Hasil</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-chart-line fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <?php
                        $totalActual = array_sum(array_column($results, 'actual_quantity'));
                        ?>
                        <h4><?php echo formatNumber($totalActual); ?></h4>
                        <p class="mb-0">Total Produksi</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-cubes fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo count($availablePlans); ?></h4>
                        <p class="mb-0">Pending Plans</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <?php
                        $thisMonth = date('Y-m');
                        $monthlyResults = array_filter($results, function($r) use ($thisMonth) {
                            return strpos($r['result_date'], $thisMonth) === 0;
                        });
                        ?>
                        <h4><?php echo count($monthlyResults); ?></h4>
                        <p class="mb-0">Bulan Ini</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Input Hasil Produksi
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-table"></i> Daftar Hasil Produksi</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Hasil</th>
                        <th>Item</th>
                        <th>Kategori</th>
                        <th>Rencana</th>
                        <th>Hasil Aktual</th>
                        <th>Persentase</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($results)): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada hasil produksi</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($results as $index => $result): ?>
                            <?php
                            $percentage = $result['planned_quantity'] > 0 ? 
                                round(($result['actual_quantity'] / $result['planned_quantity']) * 100, 1) : 0;
                            $percentageClass = $percentage >= 100 ? 'success' : ($percentage >= 80 ? 'warning' : 'danger');
                            ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($result['result_date'])); ?></td>
                                <td><?php echo $result['item_name']; ?></td>
                                <td><?php echo $result['category_name']; ?></td>
                                <td><?php echo formatNumber($result['planned_quantity']); ?> <?php echo $result['unit']; ?></td>
                                <td><?php echo formatNumber($result['actual_quantity']); ?> <?php echo $result['unit']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $percentageClass; ?>">
                                        <?php echo $percentage; ?>%
                                    </span>
                                </td>
                                <td>
                                    <?php if ($result['notes']): ?>
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="<?php echo htmlspecialchars($result['notes']); ?>">
                                            <?php echo htmlspecialchars($result['notes']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editResult(<?php echo htmlspecialchars(json_encode($result)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteResult(<?php echo $result['id']; ?>, '<?php echo $result['item_name']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Input Hasil Produksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Pilih Rencana Produksi</label>
                        <select name="plan_id" class="form-select" required onchange="updatePlanDetails(this)">
                            <option value="">Pilih Rencana</option>
                            <?php foreach ($availablePlans as $plan): ?>
                                <option value="<?php echo $plan['id']; ?>" 
                                        data-item="<?php echo $plan['item_name']; ?>"
                                        data-planned="<?php echo $plan['planned_quantity']; ?>"
                                        data-unit="<?php echo $plan['unit']; ?>"
                                        data-date="<?php echo $plan['plan_date']; ?>">
                                    <?php echo date('d/m/Y', strtotime($plan['plan_date'])); ?> - 
                                    <?php echo $plan['item_name']; ?> 
                                    (<?php echo formatNumber($plan['planned_quantity']); ?> <?php echo $plan['unit']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Item Produksi</label>
                                <input type="text" id="selected_item" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jumlah Rencana</label>
                                <input type="text" id="selected_planned" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Hasil</label>
                                <input type="date" name="result_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jumlah Hasil Aktual</label>
                                <input type="number" name="actual_quantity" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan tentang hasil produksi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Hasil Produksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Item Produksi</label>
                        <input type="text" id="edit_item_name" class="form-control" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tanggal Hasil</label>
                                <input type="date" name="result_date" id="edit_result_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Jumlah Hasil Aktual</label>
                                <input type="number" name="actual_quantity" id="edit_actual_quantity" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" id="edit_notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updatePlanDetails(select) {
    const option = select.selectedOptions[0];
    if (option && option.value) {
        document.getElementById('selected_item').value = option.dataset.item;
        document.getElementById('selected_planned').value = option.dataset.planned + ' ' + option.dataset.unit;
    } else {
        document.getElementById('selected_item').value = '';
        document.getElementById('selected_planned').value = '';
    }
}

function editResult(result) {
    document.getElementById('edit_id').value = result.id;
    document.getElementById('edit_item_name').value = result.item_name;
    document.getElementById('edit_result_date').value = result.result_date;
    document.getElementById('edit_actual_quantity').value = result.actual_quantity;
    document.getElementById('edit_notes').value = result.notes || '';
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteResult(id, itemName) {
    if (confirm('Apakah Anda yakin ingin menghapus hasil produksi "' + itemName + '"?\n\nPerhatian: Status rencana produksi akan dikembalikan ke "Planned".')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'includes/footer.php'; ?>
