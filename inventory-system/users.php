<?php 
include 'includes/header.php';

// Check if user is admin
if (!hasRole('admin')) {
    header('Location: dashboard.php');
    exit();
}

// Handle form submission
if ($_POST) {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $permissions = json_encode($_POST['permissions'] ?? []);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, permissions) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $role, $permissions]);
        
        header("Location: users.php?success=1");
        exit();
    }
    
    if ($action == 'edit') {
        $id = $_POST['id'];
        $username = $_POST['username'];
        $role = $_POST['role'];
        $permissions = json_encode($_POST['permissions'] ?? []);
        
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ?, permissions = ? WHERE id = ?");
            $stmt->execute([$username, $password, $role, $permissions, $id]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ?, permissions = ? WHERE id = ?");
            $stmt->execute([$username, $role, $permissions, $id]);
        }
        
        header("Location: users.php?success=2");
        exit();
    }
    
    if ($action == 'delete') {
        $id = $_POST['id'];
        
        // Don't allow deleting current user
        if ($id != $_SESSION['user_id']) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
        }
        
        header("Location: users.php?success=3");
        exit();
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$availablePermissions = [
    'dashboard' => 'Dashboard',
    'stock' => 'Data Stock',
    'production_planning' => 'Planning Produksi',
    'production_result' => 'Production Result',
    'users' => 'User Management',
    'settings' => 'Pengaturan'
];
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-users"></i> User Management</h2>
        <p class="text-muted">Kelola pengguna dan hak akses sistem</p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        <?php
        switch ($_GET['success']) {
            case '1': echo 'User berhasil ditambahkan!'; break;
            case '2': echo 'User berhasil diupdate!'; break;
            case '3': echo 'User berhasil dihapus!'; break;
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
                        <h4><?php echo count($users); ?></h4>
                        <p class="mb-0">Total Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
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
                        $adminCount = count(array_filter($users, function($u) { return $u['role'] == 'admin'; }));
                        ?>
                        <h4><?php echo $adminCount; ?></h4>
                        <p class="mb-0">Admin</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user-shield fa-2x"></i>
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
                        $userCount = count(array_filter($users, function($u) { return $u['role'] == 'user'; }));
                        ?>
                        <h4><?php echo $userCount; ?></h4>
                        <p class="mb-0">Regular Users</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-user fa-2x"></i>
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
                        <h4>1</h4>
                        <p class="mb-0">Online</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12 text-end">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="fas fa-plus"></i> Tambah User
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-table"></i> Daftar Users</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Dibuat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $index => $user): ?>
                        <?php
                        $permissions = json_decode($user['permissions'], true) ?? [];
                        $isCurrentUser = $user['id'] == $_SESSION['user_id'];
                        ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <?php echo $user['username']; ?>
                                <?php if ($isCurrentUser): ?>
                                    <span class="badge bg-primary ms-1">You</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['role'] == 'admin'): ?>
                                    <span class="text-muted">All Access</span>
                                <?php elseif (!empty($permissions)): ?>
                                    <?php foreach ($permissions as $perm): ?>
                                        <span class="badge bg-light text-dark me-1"><?php echo $availablePermissions[$perm] ?? $perm; ?></span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted">No permissions</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <span class="badge bg-success">Active</span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editUser(<?php echo htmlspecialchars(json_encode($user)); ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if (!$isCurrentUser): ?>
                                    <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
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
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required onchange="togglePermissions(this.value, 'add')">
                            <option value="">Pilih Role</option>
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="mb-3" id="add_permissions_section" style="display: none;">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            <?php foreach ($availablePermissions as $key => $label): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $key; ?>" id="add_perm_<?php echo $key; ?>">
                                        <label class="form-check-label" for="add_perm_<?php echo $key; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" id="edit_username" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Password Baru (Kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" id="edit_role" class="form-select" required onchange="togglePermissions(this.value, 'edit')">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                    <div class="mb-3" id="edit_permissions_section">
                        <label class="form-label">Permissions</label>
                        <div class="row">
                            <?php foreach ($availablePermissions as $key => $label): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo $key; ?>" id="edit_perm_<?php echo $key; ?>">
                                        <label class="form-check-label" for="edit_perm_<?php echo $key; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
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
function togglePermissions(role, prefix) {
    const section = document.getElementById(prefix + '_permissions_section');
    if (role === 'admin') {
        section.style.display = 'none';
    } else {
        section.style.display = 'block';
    }
}

function editUser(user) {
    document.getElementById('edit_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_role').value = user.role;
    
    // Clear all checkboxes first
    const checkboxes = document.querySelectorAll('#editModal input[type="checkbox"]');
    checkboxes.forEach(cb => cb.checked = false);
    
    // Set permissions
    const permissions = JSON.parse(user.permissions || '[]');
    permissions.forEach(perm => {
        const checkbox = document.getElementById('edit_perm_' + perm);
        if (checkbox) checkbox.checked = true;
    });
    
    togglePermissions(user.role, 'edit');
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function deleteUser(id, username) {
    if (confirm('Apakah Anda yakin ingin menghapus user "' + username + '"?')) {
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
