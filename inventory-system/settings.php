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
    
    if ($action == 'update_general') {
        updateSetting('site_title', $_POST['site_title']);
        updateSetting('language', $_POST['language']);
        
        // Handle logo upload
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
            $uploadDir = 'assets/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = 'logo_' . time() . '.' . pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
                updateSetting('logo_path', $uploadPath);
            }
        }
        
        header("Location: settings.php?success=1");
        exit();
    }
    
    if ($action == 'update_database') {
        updateSetting('db_backup_enabled', $_POST['db_backup_enabled'] ?? '0');
        updateSetting('db_backup_frequency', $_POST['db_backup_frequency']);
        
        header("Location: settings.php?success=2");
        exit();
    }
    
    if ($action == 'backup_database') {
        // Simple database backup
        $backupDir = 'backups/';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupFile = $backupDir . 'backup_' . date('Y-m-d_H-i-s') . '.sql';
        $command = "mysqldump --host=localhost --user=root --password= inventory_db > $backupFile";
        
        // Note: In production, use proper database credentials and error handling
        exec($command, $output, $return_var);
        
        if ($return_var === 0) {
            header("Location: settings.php?success=3");
        } else {
            header("Location: settings.php?error=1");
        }
        exit();
    }
}

// Get current settings
$siteTitle = getSetting('site_title', 'Inventory Control System');
$language = getSetting('language', 'id');
$logoPath = getSetting('logo_path', 'assets/images/logo.png');
$dbBackupEnabled = getSetting('db_backup_enabled', '0');
$dbBackupFrequency = getSetting('db_backup_frequency', 'weekly');

// Get system info
$phpVersion = phpversion();
$mysqlVersion = $pdo->query('SELECT VERSION()')->fetchColumn();
$serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-cog"></i> Pengaturan</h2>
        <p class="text-muted">Konfigurasi sistem dan pengaturan aplikasi</p>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle"></i>
        <?php
        switch ($_GET['success']) {
            case '1': echo 'Pengaturan umum berhasil disimpan!'; break;
            case '2': echo 'Pengaturan database berhasil disimpan!'; break;
            case '3': echo 'Backup database berhasil dibuat!'; break;
        }
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <i class="fas fa-exclamation-triangle"></i>
        Gagal membuat backup database!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <!-- General Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sliders-h"></i> Pengaturan Umum</h5>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_general">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Judul Website</label>
                                <input type="text" name="site_title" class="form-control" value="<?php echo htmlspecialchars($siteTitle); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Bahasa</label>
                                <select name="language" class="form-select">
                                    <option value="id" <?php echo $language == 'id' ? 'selected' : ''; ?>>Bahasa Indonesia</option>
                                    <option value="en" <?php echo $language == 'en' ? 'selected' : ''; ?>>English</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Logo Website</label>
                        <input type="file" name="logo" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB.</small>
                        <?php if (file_exists($logoPath)): ?>
                            <div class="mt-2">
                                <img src="<?php echo $logoPath; ?>" alt="Current Logo" style="max-height: 50px;">
                                <small class="text-muted d-block">Logo saat ini</small>
                            </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>

        <!-- Database Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-database"></i> Pengaturan Database</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="action" value="update_database">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="db_backup_enabled" value="1" 
                                           <?php echo $dbBackupEnabled ? 'checked' : ''; ?> id="backupEnabled">
                                    <label class="form-check-label" for="backupEnabled">
                                        Aktifkan Backup Otomatis
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Frekuensi Backup</label>
                                <select name="db_backup_frequency" class="form-select">
                                    <option value="daily" <?php echo $dbBackupFrequency == 'daily' ? 'selected' : ''; ?>>Harian</option>
                                    <option value="weekly" <?php echo $dbBackupFrequency == 'weekly' ? 'selected' : ''; ?>>Mingguan</option>
                                    <option value="monthly" <?php echo $dbBackupFrequency == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Pengaturan
                        </button>
                        <button type="button" class="btn btn-success" onclick="backupDatabase()">
                            <i class="fas fa-download"></i> Backup Sekarang
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tools"></i> Maintenance Sistem</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Pembersihan Data</h6>
                        <p class="text-muted">Bersihkan data lama dan file temporary</p>
                        <button class="btn btn-warning" onclick="cleanupData()">
                            <i class="fas fa-broom"></i> Bersihkan Data
                        </button>
                    </div>
                    <div class="col-md-6">
                        <h6>Reset Sistem</h6>
                        <p class="text-muted">Reset semua data ke pengaturan awal</p>
                        <button class="btn btn-danger" onclick="resetSystem()">
                            <i class="fas fa-redo"></i> Reset Sistem
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- System Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informasi Sistem</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>PHP Version:</strong></td>
                        <td><?php echo $phpVersion; ?></td>
                    </tr>
                    <tr>
                        <td><strong>MySQL Version:</strong></td>
                        <td><?php echo $mysqlVersion; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Server:</strong></td>
                        <td><?php echo $serverSoftware; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Disk Space:</strong></td>
                        <td>
                            <?php
                            $bytes = disk_free_space(".");
                            $gb = round($bytes / 1024 / 1024 / 1024, 2);
                            echo $gb . " GB free";
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Memory Limit:</strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Statistik Cepat</h5>
            </div>
            <div class="card-body">
                <?php
                $stats = [
                    'Total Items' => $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn(),
                    'Total Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
                    'Production Plans' => $pdo->query("SELECT COUNT(*) FROM production_plans")->fetchColumn(),
                    'Production Results' => $pdo->query("SELECT COUNT(*) FROM production_results")->fetchColumn()
                ];
                ?>
                <?php foreach ($stats as $label => $value): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <span><?php echo $label; ?>:</span>
                        <strong><?php echo formatNumber($value); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Recent Backups -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-history"></i> Backup Terbaru</h5>
            </div>
            <div class="card-body">
                <?php
                $backupDir = 'backups/';
                $backups = [];
                if (is_dir($backupDir)) {
                    $files = glob($backupDir . '*.sql');
                    rsort($files);
                    $backups = array_slice($files, 0, 5);
                }
                ?>
                
                <?php if (empty($backups)): ?>
                    <p class="text-muted">Belum ada backup</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($backups as $backup): ?>
                            <?php
                            $fileName = basename($backup);
                            $fileSize = round(filesize($backup) / 1024, 2);
                            $fileTime = filemtime($backup);
                            ?>
                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="d-block"><?php echo $fileName; ?></small>
                                    <small class="text-muted"><?php echo date('d/m/Y H:i', $fileTime); ?> - <?php echo $fileSize; ?> KB</small>
                                </div>
                                <a href="<?php echo $backup; ?>" class="btn btn-sm btn-outline-primary" download>
                                    <i class="fas fa-download"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function backupDatabase() {
    if (confirm('Apakah Anda yakin ingin membuat backup database sekarang?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = '<input type="hidden" name="action" value="backup_database">';
        document.body.appendChild(form);
        form.submit();
    }
}

function cleanupData() {
    if (confirm('Apakah Anda yakin ingin membersihkan data lama?\n\nPerhatian: Tindakan ini tidak dapat dibatalkan!')) {
        // Implement cleanup logic
        alert('Fitur pembersihan data akan diimplementasikan.');
    }
}

function resetSystem() {
    if (confirm('PERINGATAN: Ini akan menghapus SEMUA data dan mengembalikan sistem ke pengaturan awal!\n\nApakah Anda benar-benar yakin?')) {
        if (confirm('Konfirmasi sekali lagi: SEMUA DATA AKAN HILANG!')) {
            // Implement reset logic
            alert('Fitur reset sistem akan diimplementasikan.');
        }
    }
}
</script>

<?php include 'includes/footer.php'; ?>
