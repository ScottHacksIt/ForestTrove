<?php
$in_admin = true;
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$error   = '';
$success = '';

try {
    $db = get_db();

    // DELETE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action']) && $_POST['_action'] === 'delete') {
        $db->prepare('DELETE FROM Forest WHERE ID = ?')->execute([(int)$_POST['forest_id']]);
        $success = 'Forest deleted.';
    }

    // ADD / EDIT
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['_action'])) {
        $name       = trim($_POST['name']       ?? '');
        $google_lnk = trim($_POST['google_link'] ?? '');
        $latitude   = trim($_POST['latitude']   ?? '');
        $longitude  = trim($_POST['longitude']  ?? '');
        $edit_id    = (int)($_POST['edit_id']   ?? 0);

        if (!$name) {
            $error = 'Forest name is required.';
        } else {
            if ($edit_id) {
                $db->prepare('UPDATE Forest SET Name=?, GoogleLink=?, Latitude=?, Longitude=? WHERE ID=?')
                   ->execute([$name, $google_lnk ?: null, $latitude !== '' ? (float)$latitude : null, $longitude !== '' ? (float)$longitude : null, $edit_id]);
                $success = 'Forest updated.';
            } else {
                $db->prepare('INSERT INTO Forest (Name, GoogleLink, Latitude, Longitude) VALUES (?,?,?,?)')
                   ->execute([$name, $google_lnk ?: null, $latitude !== '' ? (float)$latitude : null, $longitude !== '' ? (float)$longitude : null]);
                $success = 'Forest added.';
            }
            $_POST = [];
        }
    }

    $forests = $db->query('SELECT * FROM Forest ORDER BY Name ASC')->fetchAll();
} catch (Exception $e) {
    $forests = [];
    $error   = 'Database error: ' . $e->getMessage();
}

$page_title = 'Manage Forests';
$site_root  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <a href="index.php" style="color:var(--green-pale);font-family:var(--font-ui);font-size:0.9rem;">← Back to Dashboard</a>
        <h1 class="mt-1">🌲 Manage Forests</h1>
    </div>
</div>

<main class="container" style="max-width:800px;">

    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
    <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <!-- Add Forest Form -->
    <div class="form-section mb-4">
        <h3 class="mb-3">Add / Edit Forest</h3>
        <form method="post" action="forests.php">
            <input type="hidden" name="edit_id" id="edit_id" value="0">
            <div class="form-group">
                <label for="name">Forest Name <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name" name="name" required maxlength="50"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       placeholder="e.g. Whispering Pines State Forest">
            </div>
            <div class="form-group">
                <label for="google_link">Google Maps Link</label>
                <input type="text" id="google_link" name="google_link" maxlength="250"
                       value="<?= htmlspecialchars($_POST['google_link'] ?? '') ?>"
                       placeholder="https://maps.google.com/?q=...">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="lat-forest">Latitude</label>
                    <input type="number" id="lat-forest" name="latitude" step="any"
                           value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="lng-forest">Longitude</label>
                    <input type="number" id="lng-forest" name="longitude" step="any"
                           value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>">
                </div>
            </div>
            <div class="flex-row">
                <button type="submit" class="btn btn-primary" id="submit-btn">Add Forest</button>
                <button type="button" class="btn btn-secondary" onclick="resetForm()">Clear</button>
            </div>
        </form>
    </div>

    <!-- Forest Table -->
    <?php if (empty($forests)): ?>
        <div class="empty-state"><span class="empty-icon">🌲</span>No forests yet.</div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr><th>ID</th><th>Name</th><th>Coordinates</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($forests as $f): ?>
                    <tr>
                        <td><?= $f['ID'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($f['Name']) ?></strong>
                            <?php if ($f['GoogleLink']): ?>
                                <br><a href="<?= htmlspecialchars($f['GoogleLink']) ?>" target="_blank" rel="noopener" style="font-size:0.82rem;">View on Maps ↗</a>
                            <?php endif; ?>
                        </td>
                        <td style="font-family:monospace;font-size:0.85rem;">
                            <?= $f['Latitude'] !== null ? htmlspecialchars($f['Latitude'] . ', ' . $f['Longitude']) : '—' ?>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-secondary"
                                    onclick="editForest(<?= htmlspecialchars(json_encode($f)) ?>)">Edit</button>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete forest <?= addslashes(htmlspecialchars($f['Name'])) ?>?')">
                                <input type="hidden" name="_action"   value="delete">
                                <input type="hidden" name="forest_id" value="<?= $f['ID'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger" style="margin-left:0.4rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</main>

<script>
function editForest(f) {
    document.getElementById('edit_id').value    = f.ID;
    document.getElementById('name').value       = f.Name;
    document.getElementById('google_link').value = f.GoogleLink ?? '';
    document.getElementById('lat-forest').value  = f.Latitude  ?? '';
    document.getElementById('lng-forest').value  = f.Longitude ?? '';
    document.getElementById('submit-btn').textContent = 'Update Forest';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
function resetForm() {
    document.getElementById('edit_id').value    = '0';
    document.getElementById('name').value       = '';
    document.getElementById('google_link').value = '';
    document.getElementById('lat-forest').value  = '';
    document.getElementById('lng-forest').value  = '';
    document.getElementById('submit-btn').textContent = 'Add Forest';
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
