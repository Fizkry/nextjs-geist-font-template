<?php include 'includes/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
        <p class="text-muted">Overview stok barang berdasarkan kategori</p>
    </div>
</div>

<?php
$stockData = getStockData();
$categories = [];
$actualStock = [];
$minStock = [];
$maxStock = [];

foreach ($stockData as $data) {
    $categories[] = $data['category'];
    $actualStock[] = (int)$data['total_stock'];
    $minStock[] = (int)$data['total_min'];
    $maxStock[] = (int)$data['total_max'];
}
?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4><?php echo array_sum($actualStock); ?></h4>
                        <p class="mb-0">Total Stok</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-boxes fa-2x"></i>
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
                        <h4><?php echo count($stockData); ?></h4>
                        <p class="mb-0">Kategori</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-list fa-2x"></i>
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
                        <?php
                        $lowStock = 0;
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM items WHERE current_stock <= min_stock");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $lowStock = $result['count'];
                        ?>
                        <h4><?php echo $lowStock; ?></h4>
                        <p class="mb-0">Stok Rendah</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
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
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM items");
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $totalItems = $result['count'];
                        ?>
                        <h4><?php echo $totalItems; ?></h4>
                        <p class="mb-0">Total Item</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-cube fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Grafik Stok Berdasarkan Kategori</h5>
            </div>
            <div class="card-body">
                <canvas id="stockChart" height="100"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Stok Rendah</h5>
            </div>
            <div class="card-body">
                <?php
                $stmt = $pdo->query("
                    SELECT i.name, i.current_stock, i.min_stock, c.name as category 
                    FROM items i 
                    JOIN categories c ON i.category_id = c.id 
                    WHERE i.current_stock <= i.min_stock 
                    ORDER BY i.current_stock ASC 
                    LIMIT 10
                ");
                $lowStockItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <?php if (empty($lowStockItems)): ?>
                    <div class="text-center text-muted">
                        <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <p>Semua stok dalam kondisi baik</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($lowStockItems as $item): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <h6 class="mb-1"><?php echo $item['name']; ?></h6>
                                    <small class="text-muted"><?php echo $item['category']; ?></small>
                                </div>
                                <span class="badge bg-danger rounded-pill">
                                    <?php echo $item['current_stock']; ?>/<?php echo $item['min_stock']; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-table"></i> Ringkasan Stok per Kategori</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Jumlah Item</th>
                                <th>Total Stok</th>
                                <th>Min Stok</th>
                                <th>Max Stok</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($stockData as $data): ?>
                                <tr>
                                    <td><?php echo $data['category']; ?></td>
                                    <td><?php echo $data['item_count']; ?></td>
                                    <td><?php echo formatNumber($data['total_stock']); ?></td>
                                    <td><?php echo formatNumber($data['total_min']); ?></td>
                                    <td><?php echo formatNumber($data['total_max']); ?></td>
                                    <td>
                                        <?php if ($data['total_stock'] <= $data['total_min']): ?>
                                            <span class="badge bg-danger">Rendah</span>
                                        <?php elseif ($data['total_stock'] >= $data['total_max']): ?>
                                            <span class="badge bg-warning">Tinggi</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Normal</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Chart configuration
const ctx = document.getElementById('stockChart').getContext('2d');
const stockChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($categories); ?>,
        datasets: [{
            label: 'Stok Aktual',
            data: <?php echo json_encode($actualStock); ?>,
            backgroundColor: 'rgba(102, 126, 234, 0.8)',
            borderColor: 'rgba(102, 126, 234, 1)',
            borderWidth: 1,
            type: 'bar'
        }, {
            label: 'Stok Minimum',
            data: <?php echo json_encode($minStock); ?>,
            borderColor: 'rgba(255, 99, 132, 1)',
            backgroundColor: 'rgba(255, 99, 132, 0.2)',
            borderWidth: 2,
            type: 'line',
            fill: false
        }, {
            label: 'Stok Maximum',
            data: <?php echo json_encode($maxStock); ?>,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderWidth: 2,
            type: 'line',
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Jumlah Stok'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Kategori'
                }
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            title: {
                display: true,
                text: 'Grafik Kombinasi Stok Barang'
            }
        }
    }
});
</script>

<?php include 'includes/footer.php'; ?>
