<?php include 'includes/header.php'; ?>

<?php
$currentMonth = $_GET['month'] ?? date('m');
$currentYear = $_GET['year'] ?? date('Y');

// Handle form submission
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $plan_date = $_POST['plan_date'];
        $item_id = $_POST['item_id'];
        $planned_quantity = $_POST['planned_quantity'];
        
        $stmt = $pdo->prepare("INSERT INTO production_plans (plan_date, item_id, planned_quantity) VALUES (?, ?, ?)");
        $stmt->execute([$plan_date, $item_id, $planned_quantity]);
        
        header("Location: production-planning.php?month=$currentMonth&year=$currentYear&success=1");
        exit();
    }
    
    if ($action == 'edit') {
        $id = $_POST['id'];
        $plan_date = $_POST['plan_date'];
        $item_id = $_POST['item_id'];
        $planned_quantity = $_POST['planned_quantity'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE production_plans SET plan_date = ?, item_id = ?, planned_quantity = ?, status = ? WHERE id = ?");
        $stmt->execute([$plan_date, $item_id, $planned_quantity, $status, $id]);
        
        header("Location: production-planning.php?month=$currentMonth&year=$currentYear&success=2");
        exit();
    }
    
    if ($action == 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM production_plans WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: production-planning.php?month=$currentMonth&year=$currentYear&success=3");
        exit();
    }
}

$plans = getProductionPlans($currentMonth, $currentYear);

// Get all items for dropdown
$stmt = $pdo->query("SELECT i.*, c.name as category_name FROM items i JOIN categories c ON i.category_id = c.id ORDER BY c.name, i.name");
$allItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate calendar days
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $currentMonth, $currentYear);
$firstDay = date('w', mktime(0, 0, 0, $currentMonth, 1, $currentYear));

$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-calendar-alt"></i> Planning Produksi</h2>
        <p class="text-muted">Perencanaan produksi harian untuk bulan <?php echo $monthNames[(int)$currentMonth] . ' ' . $currentYear; ?></p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        <?php
        switch ($_GET['success']) {
            case '1': echo 'Rencana produksi berhasil ditambahkan!'; break;
            case '2': echo 'Rencana produksi berhasil diupdate!'; break;
            case '3': echo 'Rencana produksi berhasil dihapus!'; break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="d-flex align-items-center">
            <label class="form-label me-2 mb-0">Bulan/Tahun:</label>
            <select class="form-select me-2" style="width: auto;" onchange="changeMonth(this.value)">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?php echo $m; ?>" <?php echo $m == $currentMonth ? 'selected' : ''; ?>>
                        <?php echo $monthNames[$m]; ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select class="form-select" style="width: auto;" onchange="changeYear(this.value)">
                <?php for ($y = date('Y') - 1; $y <= date('Y') + 2; $y++): ?>
                    <option value="<?php echo $y; ?>" <?php echo $y == $currentYear ? 'selected' : ''; ?>>
                        <?php echo $y; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Tambah Rencana
        </button>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-calendar"></i> Kalender Produksi</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">Min</th>
                                <th class="text-center">Sen</th>
                                <th class="text-center">Sel</th>
                                <th class="text-center">Rab</th>
                                <th class="text-center">Kam</th>
                                <th class="text-center">Jum</th>
                                <th class="text-center">Sab</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $day = 1;
                            $plansByDate = [];
                            foreach ($plans as $plan) {
                                $date = date('j', strtotime($plan['plan_date']));
                                if (!isset($plansByDate[$date])) {
                                    $plansByDate[$date] = [];
                                }
                                $plansByDate[$date][] = $plan;
                            }
                            
                            for ($week = 0; $week < 6; $week++):
                                if ($day > $daysInMonth) break;
                            ?>
                                <tr>
                                    <?php for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++): ?>
                                        <td style="height: 100px; vertical-align: top; width: 14.28%;">
                                            <?php
                                            if (($week == 0 && $dayOfWeek < $firstDay) || $day > $daysInMonth) {
                                                echo '';
                                            } else {
                                                echo '<div class="fw-bold mb-1">' . $day . '</div>';
                                                if (isset($plansByDate[$day])) {
                                                    foreach ($plansByDate[$day] as $plan) {
                                                        $statusClass = $plan['status'] == 'completed' ? 'success' : ($plan['status'] == 'in_progress' ? 'warning' : 'primary');
                                                        echo '<div class="badge bg-' . $statusClass . ' d-block mb-1 text-start small">';
                                                        echo substr($plan['item_name'], 0, 15) . ($plan['planned_quantity'] ? ' (' . $plan['planned_quantity'] . ')' : '');
                                                        echo '</div>';
                                                    }
                                                }
                                                $day++;
                                            }
                                            ?>
                                        </td>
                                    <?php endfor; ?>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list"></i> Daftar Rencana</h5>
            </div>
            <div class="card-body">
                <?php if (empty($plans)): ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-calendar-times fa-3x mb-3"></i>
                        <p>Belum ada rencana produksi</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($plans as $plan): ?>
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1"><?php echo $plan['item_name']; ?></h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y', strtotime($plan['plan_date'])); ?>
                                        </p>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-cubes"></i> <?php echo formatNumber($plan['planned_quantity']); ?> <?php echo $plan['unit']; ?>
                                        </p>
                                        <span class="badge bg-<?php echo $plan['status'] == 'completed' ? 'success' : ($plan['status'] == 'in_progress' ? 'warning' : 'primary'); ?>">
                                            <?php 
                                            echo $plan['status'] == 'completed' ? 'Selesai' : 
                                                ($plan['status'] == 'in_progress' ? 'Proses' : 'Rencana'); 
                                            ?>
                                        </span>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="editPlan(<?php echo htmlspecialchars(json_encode($plan)); ?>)">
                                                <i class="fas fa-edit"></i> Edit
                                            </a></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="deletePlan(<?php echo $plan['id']; ?>, '<?php echo $plan['item_name']; ?>')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Rencana Produksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Produksi</label>
                        <input type="date" name="plan_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Produksi</label>
                        <select name="item_id" class="form-select" required>
                            <option value="">Pilih Item</option>
                            <?php foreach ($allItems as $item): ?>
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo $item['name']; ?> (<?php echo $item['category_name']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Rencana</label>
                        <input type="number" name="planned_quantity" class="form-control" required>
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
                <h5 class="modal-title">Edit Rencana Produksi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Produksi</label>
                        <input type="date" name="plan_date" id="edit_plan_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Produksi</label>
                        <select name="item_id" id="edit_item_id" class="form-select" required>
                            <option value="">Pilih Item</option>
                            <?php foreach ($allItems as $item): ?>
                                <option value="<?php echo $item['id']; ?>">
                                    <?php echo $item['name']; ?> (<?php echo $item['category_name']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Rencana</label>
                        <input type="number" name="planned_quantity" id="edit_planned_quantity" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="edit_status" class="form-select" required>
                            <option value="planned">Rencana</option>
                            <option value="in_progress">Dalam Proses</option>
                            <option value="completed">Selesai</option>
                        </select>
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
function changeMonth(month) {
    window.location.href = `production-planning.php?month=${month}&year=<?php echo $currentYear; ?>`;
}

function changeYear(year) {
    window.location.href = `production-planning.php?month=<?php echo $currentMonth; ?>&year=${year}`;
}

function editPlan(plan) {
    document.getElementById('edit_id').value = plan.id;
    document.getElementById('edit_plan_date').value = plan.plan_date;
    document.getElementById('edit_item_id').value = plan.item_id;
    document.getElementById('edit_planned_quantity').value = plan.planned_quantity;
    document.getElementById('edit_status').value = plan.status;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deletePlan(id, itemName) {
    if (confirm('Apakah Anda yakin ingin menghapus rencana produksi "' + itemName + '"?')) {
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
