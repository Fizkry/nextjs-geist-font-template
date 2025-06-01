<?php include 'includes/header.php'; ?>

<?php
$type = $_GET['type'] ?? 'raw_material';
$typeNames = [
    'raw_material' => 'Raw Material',
    'semi_finished' => 'Barang Setengah Jadi',
    'finished_good' => 'Finish Good',
    'consumable' => 'Consumable'
];

$typeName = $typeNames[$type] ?? 'Raw Material';
$items = getItemsByCategory($type);

// Handle form submission for adding/editing items
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $name = $_POST['name'];
        $current_stock = $_POST['current_stock'];
        $min_stock = $_POST['min_stock'];
        $max_stock = $_POST['max_stock'];
        $unit = $_POST['unit'];
        
        $stmt = $pdo->prepare("SELECT id FROM categories WHERE type = ?");
        $stmt->execute([$type]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $pdo->prepare("INSERT INTO items (name, category_id, current_stock, min_stock, max_stock, unit) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category['id'], $current_stock, $min_stock, $max_stock, $unit]);
        
        header("Location: stock.php?type=$type&success=1");
        exit();
    }
    
    if ($action == 'edit') {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $current_stock = $_POST['current_stock'];
        $min_stock = $_POST['min_stock'];
        $max_stock = $_POST['max_stock'];
        $unit = $_POST['unit'];
        
        $stmt = $pdo->prepare("UPDATE items SET name = ?, current_stock = ?, min_stock = ?, max_stock = ?, unit = ? WHERE id = ?");
        $stmt->execute([$name, $current_stock, $min_stock, $max_stock, $unit, $id]);
        
        header("Location: stock.php?type=$type&success=2");
        exit();
    }
    
    if ($action == 'delete') {
        $id = $_POST['id'];
        $stmt = $pdo->prepare("DELETE FROM items WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: stock.php?type=$type&success=3");
        exit();
    }
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-boxes"></i> Data Stock - <?php echo $typeName; ?></h2>
        <p class="text-muted">Kelola data stok <?php echo strtolower($typeName); ?></p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        <?php
        switch ($_GET['success']) {
            case '1': echo 'Item berhasil ditambahkan!'; break;
            case '2': echo 'Item berhasil diupdate!'; break;
            case '3': echo 'Item berhasil dihapus!'; break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-6">
        <div class="btn-group" role="group">
            <a href="stock.php?type=raw_material" class="btn <?php echo $type == 'raw_material' ? 'btn-primary' : 'btn-outline-primary'; ?>">Raw Material</a>
            <a href="stock.php?type=semi_finished" class="btn <?php echo $type == 'semi_finished' ? 'btn-primary' : 'btn-outline-primary'; ?>">Setengah Jadi</a>
            <a href="stock.php?type=finished_good" class="btn <?php echo $type == 'finished_good' ? 'btn-primary' : 'btn-outline-primary'; ?>">Finish Good</a>
            <a href="stock.php?type=consumable" class="btn <?php echo $type == 'consumable' ? 'btn-primary' : 'btn-outline-primary'; ?>">Consumable</a>
        </div>
    </div>
    <div class="col-md-6 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Tambah Item
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-table"></i> Daftar <?php echo $typeName; ?></h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Item</th>
                        <th>Stok Saat Ini</th>
                        <th>Stok Minimum</th>
                        <th>Stok Maximum</th>
                        <th>Satuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>Belum ada data item</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($items as $index => $item): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo $item['name']; ?></td>
                                <td><?php echo formatNumber($item['current_stock']); ?></td>
                                <td><?php echo formatNumber($item['min_stock']); ?></td>
                                <td><?php echo formatNumber($item['max_stock']); ?></td>
                                <td><?php echo $item['unit']; ?></td>
                                <td>
                                    <?php if ($item['current_stock'] <= $item['min_stock']): ?>
                                        <span class="badge bg-danger">Rendah</span>
                                    <?php elseif ($item['current_stock'] >= $item['max_stock']): ?>
                                        <span class="badge bg-warning">Tinggi</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Normal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning" onclick="editItem(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteItem(<?php echo $item['id']; ?>, '<?php echo $item['name']; ?>')">
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Item Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="mb-3">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Saat Ini</label>
                                <input type="number" name="current_stock" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Satuan</label>
                                <input type="text" name="unit" class="form-control" placeholder="kg, pcs, liter" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Minimum</label>
                                <input type="number" name="min_stock" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Maximum</label>
                                <input type="number" name="max_stock" class="form-control" required>
                            </div>
                        </div>
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
                <h5 class="modal-title">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Item</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Saat Ini</label>
                                <input type="number" name="current_stock" id="edit_current_stock" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Satuan</label>
                                <input type="text" name="unit" id="edit_unit" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Minimum</label>
                                <input type="number" name="min_stock" id="edit_min_stock" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Stok Maximum</label>
                                <input type="number" name="max_stock" id="edit_max_stock" class="form-control" required>
                            </div>
                        </div>
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
function editItem(item) {
    document.getElementById('edit_id').value = item.id;
    document.getElementById('edit_name').value = item.name;
    document.getElementById('edit_current_stock').value = item.current_stock;
    document.getElementById('edit_min_stock').value = item.min_stock;
    document.getElementById('edit_max_stock').value = item.max_stock;
    document.getElementById('edit_unit').value = item.unit;
    
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteItem(id, name) {
    if (confirm('Apakah Anda yakin ingin menghapus item "' + name + '"?')) {
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
