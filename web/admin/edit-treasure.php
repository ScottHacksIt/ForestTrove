<?php
$in_admin = true;
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$id = trim($_GET['id'] ?? '');
if (!$id) {
    header('Location: index.php');
    exit;
}

$error   = '';
$success = '';

try {
    $db = get_db();

    // Handle DELETE
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_action']) && $_POST['_action'] === 'delete') {
        $db->prepare('DELETE FROM Treasure WHERE ID = ?')->execute([$id]);
        header('Location: index.php?deleted=1');
        exit;
    }

    // Load forests
    $forests = $db->query('SELECT ID, Name FROM Forest ORDER BY Name ASC')->fetchAll();

    // Load seekers (for manual assignment)
    $seekers = $db->query('SELECT ID, FirstName FROM Seeker ORDER BY ID DESC')->fetchAll();

    // Load treasure
    $stmt = $db->prepare('SELECT * FROM Treasure WHERE ID = ? LIMIT 1');
    $stmt->execute([$id]);
    $treasure = $stmt->fetch();
} catch (Exception $e) {
    $treasure = null;
}

if (!$treasure) {
    header('Location: index.php');
    exit;
}

// Handle EDIT save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['_action'])) {
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $forest_id   = (int)($_POST['forest_id']  ?? 0);
    $latitude    = trim($_POST['latitude']    ?? '');
    $longitude   = trim($_POST['longitude']   ?? '');
    $placed_date = trim($_POST['placed_date'] ?? '');
    $is_found    = isset($_POST['is_found']) ? 1 : 0;
    $seeker_id   = (int)($_POST['seeker_id']  ?? 0);
    $found_date  = trim($_POST['found_date']  ?? '');

    if (!$name) {
        $error = 'Treasure name is required.';
    } else {
        // New image?
        $image_path = $treasure['ImagePath'];
        if (!empty($_FILES['image']['name'])) {
            $new_path = saveUploadedImage($_FILES['image'], 'treasure_');
            if ($new_path === null) {
                $error = 'Could not save image. Use JPEG, PNG, GIF, or WebP.';
            } else {
                $image_path = $new_path;
            }
        }

        if (!$error) {
            try {
                $stmt = $db->prepare(
                    "UPDATE Treasure SET
                        Name        = ?,
                        ImagePath   = ?,
                        Description = ?,
                        ForestID    = ?,
                        Latitude    = ?,
                        Longitude   = ?,
                        PlacedDate  = ?,
                        IsFound     = ?,
                        SeekerID    = ?,
                        FoundDate   = ?
                     WHERE ID = ?"
                );
                $stmt->execute([
                    $name,
                    $image_path,
                    $description ?: null,
                    $forest_id  ?: null,
                    $latitude   !== '' ? (float)$latitude  : null,
                    $longitude  !== '' ? (float)$longitude : null,
                    $placed_date ?: null,
                    $is_found,
                    ($is_found && $seeker_id) ? $seeker_id : null,
                    ($is_found && $found_date) ? $found_date : null,
                    $id,
                ]);
                $success = 'Treasure updated successfully.';

                // Reload
                $stmt = $db->prepare('SELECT * FROM Treasure WHERE ID = ? LIMIT 1');
                $stmt->execute([$id]);
                $treasure = $stmt->fetch();
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Edit: ' . $treasure['Name'];
$site_root  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <a href="index.php" style="color:var(--green-pale);font-family:var(--font-ui);font-size:0.9rem;">← Back to Dashboard</a>
        <h1 class="mt-1">✏ Edit Treasure</h1>
    </div>
</div>

<main class="container" style="max-width:800px;">

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-section">
        <form method="post" enctype="multipart/form-data" action="edit-treasure.php?id=<?= urlencode($id) ?>">

            <div class="form-group">
                <label>Treasure ID</label>
                <input type="text" value="<?= htmlspecialchars($id) ?>" disabled style="background:var(--cream-dark);color:var(--text-light);font-family:monospace;">
            </div>

            <div class="form-group">
                <label for="name">Name <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name" name="name" required maxlength="250"
                       value="<?= htmlspecialchars($treasure['Name']) ?>">
            </div>

            <div class="form-group">
                <label>Current Image</label>
                <?php if ($treasure['ImagePath']): ?>
                    <img src="../<?= htmlspecialchars($treasure['ImagePath']) ?>" alt="" class="img-preview" style="display:block;">
                <?php else: ?>
                    <p class="hint">No image uploaded yet.</p>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="image">Replace Image</label>
                <input type="file" id="image" name="image" accept="image/*"
                       onchange="previewImage(this,'new-img-preview')">
                <img id="new-img-preview" class="img-preview" src="" alt="" style="display:none;">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="5000"><?= htmlspecialchars($treasure['Description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="forest_id">Forest</label>
                <select id="forest_id" name="forest_id">
                    <option value="">— Select a Forest —</option>
                    <?php foreach ($forests as $f): ?>
                        <option value="<?= $f['ID'] ?>" <?= $treasure['ForestID'] == $f['ID'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="number" id="latitude" name="latitude" step="any"
                           value="<?= htmlspecialchars($treasure['Latitude'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="number" id="longitude" name="longitude" step="any"
                           value="<?= htmlspecialchars($treasure['Longitude'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="placed_date">Date Placed</label>
                    <input type="date" id="placed_date" name="placed_date"
                           value="<?= htmlspecialchars($treasure['PlacedDate'] ?? '') ?>">
                </div>
            </div>

            <hr class="divider">
            <h3 class="mb-3">Found Status</h3>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:0.6rem;cursor:pointer;text-transform:none;font-size:1rem;">
                    <input type="checkbox" id="is_found" name="is_found" value="1"
                           <?= $treasure['IsFound'] ? 'checked' : '' ?>
                           onchange="toggleFoundFields(this.checked)">
                    Mark as Found
                </label>
            </div>

            <div id="found-fields" style="<?= $treasure['IsFound'] ? '' : 'display:none;' ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="found_date">Date Found</label>
                        <input type="date" id="found_date" name="found_date"
                               value="<?= htmlspecialchars($treasure['FoundDate'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="seeker_id">Seeker (Finder)</label>
                    <select id="seeker_id" name="seeker_id">
                        <option value="">— None —</option>
                        <?php foreach ($seekers as $s): ?>
                            <option value="<?= $s['ID'] ?>" <?= $treasure['SeekerID'] == $s['ID'] ? 'selected' : '' ?>>
                                #<?= $s['ID'] ?> — <?= htmlspecialchars($s['FirstName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="flex-row mt-3">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <a href="../treasure.php?id=<?= urlencode($id) ?>" class="btn btn-secondary">View Public Page</a>
            </div>
        </form>
    </div>

    <!-- Delete -->
    <div class="form-section mt-4" style="border:2px solid var(--danger);">
        <h3 style="color:var(--danger);">⚠ Danger Zone</h3>
        <p>Permanently delete this treasure. This cannot be undone.</p>
        <form method="post" action="edit-treasure.php?id=<?= urlencode($id) ?>" id="delete-form">
            <input type="hidden" name="_action" value="delete">
            <button type="button" class="btn btn-danger"
                    onclick="confirmDelete('<?= addslashes(htmlspecialchars($treasure['Name'])) ?>')">
                🗑 Delete This Treasure
            </button>
        </form>
    </div>

</main>

<script>
function toggleFoundFields(checked) {
    document.getElementById('found-fields').style.display = checked ? '' : 'none';
}
function confirmDelete(name) {
    if (confirm('Are you sure you want to permanently delete "' + name + '"? This cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
