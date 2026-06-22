<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// --- Filters & Sort (server-side pre-filter) ---
$filter_status = $_GET['status'] ?? 'all'; // all | found | unfound
$sort_by       = $_GET['sort']   ?? 'date'; // date | name

$where_clauses = [];
$params        = [];

if ($filter_status === 'found') {
    $where_clauses[] = 't.IsFound = 1';
} elseif ($filter_status === 'unfound') {
    $where_clauses[] = 't.IsFound = 0';
}

$where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

$order_sql = match ($sort_by) {
    'name' => 'ORDER BY t.Name ASC',
    default => 'ORDER BY t.CreatedDate DESC',
};

try {
    $db   = get_db();
    $sql  = "SELECT t.ID, t.Name, t.ImagePath, t.IsFound, t.CreatedDate,
                    t.Latitude, t.Longitude,
                    f.Name AS ForestName, f.Latitude AS ForestLat, f.Longitude AS ForestLng
               FROM Treasure t
               LEFT JOIN Forest f ON t.ForestID = f.ID
              {$where_sql}
              {$order_sql}";
    $stmt     = $db->query($sql);
    $treasures = $stmt->fetchAll();
} catch (Exception $e) {
    $treasures = [];
    $db_error  = 'Could not load treasures.';
}

$page_title = 'Browse Treasures';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>🌿 Browse Treasures</h1>
        <p>Explore all the painted rocks waiting to be discovered in the forest.</p>
    </div>
</div>

<main class="container">

    <!-- Filter / Sort Bar -->
    <form method="get" action="browse.php" class="filter-bar" id="filter-form">
        <div>
            <label for="status-filter">Status:</label>
            <select id="status-filter" name="status" onchange="this.form.submit()">
                <option value="all"    <?= $filter_status === 'all'    ? 'selected' : '' ?>>All Treasures</option>
                <option value="unfound"<?= $filter_status === 'unfound'? 'selected' : '' ?>>🟢 Still Hidden</option>
                <option value="found"  <?= $filter_status === 'found'  ? 'selected' : '' ?>>🏆 Already Found</option>
            </select>
        </div>
        <div>
            <label for="sort-select">Sort by:</label>
            <select id="sort-select" name="sort" onchange="this.form.submit()">
                <option value="date" <?= $sort_by === 'date' ? 'selected' : '' ?>>Date Added</option>
                <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Name</option>
                <option value="distance" <?= $sort_by === 'distance' ? 'selected' : '' ?>>Distance (nearest)</option>
            </select>
        </div>
        <span id="location-status" style="font-family:var(--font-ui);font-size:0.85rem;color:var(--text-light);"></span>
    </form>

    <?php if (isset($db_error)): ?>
        <div class="alert alert-error"><?= htmlspecialchars($db_error) ?></div>
    <?php endif; ?>

    <?php if (empty($treasures)): ?>
        <div class="empty-state">
            <span class="empty-icon">🍂</span>
            No treasures found matching your filters. Try a different search!
        </div>
    <?php else: ?>
        <!-- Pass treasure data to JS for distance sorting -->
        <script>
            window.FT_TREASURES = <?= json_encode(array_map(fn($t) => [
                'id'         => $t['ID'],
                'name'       => $t['Name'],
                'lat'        => $t['Latitude'],
                'lng'        => $t['Longitude'],
                'forestLat'  => $t['ForestLat'],
                'forestLng'  => $t['ForestLng'],
                'forestName' => $t['ForestName'],
                'isFound'    => (bool)$t['IsFound'],
                'imagePath'  => $t['ImagePath'],
                'createdDate'=> $t['CreatedDate'],
            ], $treasures)) ?>;
            window.FT_SORT_BY = <?= json_encode($sort_by) ?>;
        </script>

        <div class="card-grid" id="treasure-grid">
            <?php foreach ($treasures as $t): ?>
            <a href="treasure.php?id=<?= urlencode($t['ID']) ?>"
               class="treasure-card"
               style="text-decoration:none;"
               data-id="<?= htmlspecialchars($t['ID']) ?>"
               data-lat="<?= htmlspecialchars($t['Latitude'] ?? '') ?>"
               data-lng="<?= htmlspecialchars($t['Longitude'] ?? '') ?>">
                <?php if ($t['ImagePath']): ?>
                    <img class="card-thumb"
                         src="<?= htmlspecialchars($t['ImagePath']) ?>"
                         alt="<?= htmlspecialchars($t['Name']) ?>">
                <?php else: ?>
                    <div class="card-thumb-placeholder">🪨</div>
                <?php endif; ?>
                <div class="card-body">
                    <h3><?= htmlspecialchars($t['Name']) ?></h3>
                    <p class="card-forest"><?= htmlspecialchars($t['ForestName'] ?? 'Unknown Forest') ?></p>
                    <div class="card-footer-row">
                        <span class="badge <?= $t['IsFound'] ? 'badge-found' : 'badge-unfound' ?>">
                            <?= $t['IsFound'] ? '✓ Found' : '● Still Hidden' ?>
                        </span>
                        <span class="text-muted" style="font-family:var(--font-ui);font-size:0.8rem;">
                            <?= htmlspecialchars(date('M j, Y', strtotime($t['CreatedDate']))) ?>
                        </span>
                    </div>
                    <span class="distance-label"
                          data-lat="<?= htmlspecialchars($t['Latitude'] ?? '') ?>"
                          data-lng="<?= htmlspecialchars($t['Longitude'] ?? '') ?>"
                          style="display:none;font-family:var(--font-ui);font-size:0.82rem;color:var(--green-mid);margin-top:0.4rem;">
                    </span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
