<?php
$in_admin = true;
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

// Stats
try {
    $db = get_db();
    $total   = (int)$db->query('SELECT COUNT(*) FROM Treasure')->fetchColumn();
    $found   = (int)$db->query('SELECT COUNT(*) FROM Treasure WHERE IsFound = 1')->fetchColumn();
    $unfound = $total - $found;
    $forests = (int)$db->query('SELECT COUNT(*) FROM Forest')->fetchColumn();

    // Recent treasures
    $recent = $db->query(
        "SELECT t.ID, t.Name, t.ImagePath, t.IsFound, t.CreatedDate, f.Name AS ForestName
           FROM Treasure t
           LEFT JOIN Forest f ON t.ForestID = f.ID
          ORDER BY t.CreatedDate DESC
          LIMIT 10"
    )->fetchAll();
} catch (Exception $e) {
    $total = $found = $unfound = $forests = 0;
    $recent = [];
}

$page_title = 'Admin Dashboard';
$site_root  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>⚙ Admin Dashboard</h1>
        <p>Welcome back, <?= htmlspecialchars($_SESSION['first_name'] ?? 'Admin') ?>. Manage your Forest Trove.</p>
    </div>
</div>

<main class="container">

    <!-- Stats -->
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $total ?></div>
            <div class="stat-label">Total Treasures</div>
        </div>
        <div class="stat-card" style="border-top-color:var(--unfound-color);">
            <div class="stat-number" style="color:var(--unfound-color);"><?= $unfound ?></div>
            <div class="stat-label">Still Hidden</div>
        </div>
        <div class="stat-card" style="border-top-color:var(--found-color);">
            <div class="stat-number" style="color:var(--found-color);"><?= $found ?></div>
            <div class="stat-label">Found</div>
        </div>
        <div class="stat-card" style="border-top-color:var(--brown-mid);">
            <div class="stat-number" style="color:var(--brown-mid);"><?= $forests ?></div>
            <div class="stat-label">Forests</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="mb-3">Quick Actions</h2>
    <div class="admin-actions mb-4">
        <div class="admin-action-card">
            <div class="action-icon">🪨</div>
            <h3>Register Treasure</h3>
            <p>Add a new painted rock to the collection.</p>
            <a href="register-treasure.php" class="btn btn-primary">Add Treasure</a>
        </div>
        <div class="admin-action-card">
            <div class="action-icon">✏️</div>
            <h3>Edit Treasures</h3>
            <p>Update details or mark treasures as found.</p>
            <a href="#recent-table" class="btn btn-secondary">View List Below</a>
        </div>
        <div class="admin-action-card">
            <div class="action-icon">🌲</div>
            <h3>Manage Forests</h3>
            <p>Add or edit the forest locations.</p>
            <a href="forests.php" class="btn btn-earth">Manage Forests</a>
        </div>
    </div>

    <!-- Recent Treasures Table -->
    <h2 id="recent-table" class="mb-3">All Treasures</h2>
    <?php if (empty($recent)): ?>
        <div class="empty-state">
            <span class="empty-icon">🍂</span>
            No treasures registered yet. <a href="register-treasure.php">Add your first one!</a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Forest</th>
                        <th>Status</th>
                        <th>Date Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $t): ?>
                    <tr>
                        <td>
                            <?php if ($t['ImagePath']): ?>
                                <img class="table-thumb" src="../<?= htmlspecialchars($t['ImagePath']) ?>" alt="<?= htmlspecialchars($t['Name']) ?>">
                            <?php else: ?>
                                <div class="table-thumb-placeholder">🪨</div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($t['Name']) ?></strong></td>
                        <td><?= htmlspecialchars($t['ForestName'] ?? '—') ?></td>
                        <td>
                            <span class="badge <?= $t['IsFound'] ? 'badge-found' : 'badge-unfound' ?>">
                                <?= $t['IsFound'] ? 'Found' : 'Hidden' ?>
                            </span>
                        </td>
                        <td style="font-family:var(--font-ui);font-size:0.88rem;">
                            <?= htmlspecialchars(date('M j, Y', strtotime($t['CreatedDate']))) ?>
                        </td>
                        <td>
                            <a href="edit-treasure.php?id=<?= urlencode($t['ID']) ?>" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="../treasure.php?id=<?= urlencode($t['ID']) ?>" class="btn btn-sm btn-earth" style="margin-left:0.4rem;">View</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
