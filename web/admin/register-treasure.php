<?php
$in_admin = true;
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$error   = '';
$success = '';

// Load forest list for dropdown
try {
    $db      = get_db();
    $forests = $db->query('SELECT ID, Name FROM Forest ORDER BY Name ASC')->fetchAll();
} catch (Exception $e) {
    $forests = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Validate & sanitize ---
    $name        = trim($_POST['name']        ?? '');
    $description = trim($_POST['description'] ?? '');
    $forest_id   = (int)($_POST['forest_id']  ?? 0);
    $latitude    = trim($_POST['latitude']    ?? '');
    $longitude   = trim($_POST['longitude']   ?? '');
    $placed_date = trim($_POST['placed_date'] ?? '');

    if (!$name) {
        $error = 'Treasure name is required.';
    } else {
        // Generate unique ID
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $slug = trim($slug, '-');
        $unique_id = $slug . '-' . bin2hex(random_bytes(4));

        // Image upload
        $image_path = null;
        if (!empty($_FILES['image']['name'])) {
            $image_path = saveUploadedImage($_FILES['image'], 'treasure_');
            if ($image_path === null) {
                $error = 'Could not save image. Use JPEG, PNG, GIF, or WebP.';
            }
        }

        if (!$error) {
            try {
                $stmt = $db->prepare(
                    "INSERT INTO Treasure
                       (ID, ImagePath, Name, Description, ForestID, Latitude, Longitude, CreatedDate, PlacedDate, IsFound)
                     VALUES (?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, 0)"
                );
                $stmt->execute([
                    $unique_id,
                    $image_path,
                    $name,
                    $description ?: null,
                    $forest_id ?: null,
                    $latitude  !== '' ? (float)$latitude  : null,
                    $longitude !== '' ? (float)$longitude : null,
                    $placed_date ?: null,
                ]);
                $success = $unique_id;
                // Clear form
                $_POST = [];
            } catch (Exception $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

$page_title = 'Register Treasure';
$site_root  = '../';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <a href="index.php" style="color:var(--green-pale);font-family:var(--font-ui);font-size:0.9rem;">← Back to Dashboard</a>
        <h1 class="mt-1">🪨 Register New Treasure</h1>
    </div>
</div>

<main class="container" style="max-width:760px;">

    <?php if ($success): ?>
        <div class="alert alert-success">
            <strong>Treasure registered!</strong> ID: <code><?= htmlspecialchars($success) ?></code><br>
            <a href="../treasure.php?id=<?= urlencode($success) ?>">View it →</a> &nbsp;|&nbsp;
            <a href="register-treasure.php">Add another</a>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="form-section">
        <form method="post" enctype="multipart/form-data" action="register-treasure.php">

            <div class="form-group">
                <label for="name">Treasure Name <span style="color:var(--danger);">*</span></label>
                <input type="text" id="name" name="name" required maxlength="250"
                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                       placeholder="e.g. The Sleeping Owl">
            </div>

            <div class="form-group">
                <label for="image">Treasure Photo</label>
                <input type="file" id="image" name="image" accept="image/*"
                       onchange="previewImage(this,'img-preview')">
                <img id="img-preview" class="img-preview" src="" alt="" style="display:none;">
                <p class="hint">JPEG, PNG, GIF, or WebP.</p>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" maxlength="5000"
                          placeholder="Describe the painting, the rock, anything special about this treasure…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="forest_id">Forest</label>
                <select id="forest_id" name="forest_id">
                    <option value="">— Select a Forest —</option>
                    <?php foreach ($forests as $f): ?>
                        <option value="<?= $f['ID'] ?>"
                            <?= (isset($_POST['forest_id']) && (int)$_POST['forest_id'] === $f['ID']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($f['Name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (empty($forests)): ?>
                    <p class="hint" style="color:var(--danger);">No forests yet. <a href="forests.php">Add a forest first.</a></p>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="latitude">Latitude</label>
                    <input type="number" id="latitude" name="latitude" step="any"
                           value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>"
                           placeholder="e.g. 45.123456">
                </div>
                <div class="form-group">
                    <label for="longitude">Longitude</label>
                    <input type="number" id="longitude" name="longitude" step="any"
                           value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>"
                           placeholder="e.g. -93.654321">
                </div>
            </div>

            <div class="form-group">
                <label for="placed_date">Date Placed in Forest</label>
                <input type="date" id="placed_date" name="placed_date"
                       value="<?= htmlspecialchars($_POST['placed_date'] ?? '') ?>">
            </div>

            <button type="submit" class="btn btn-primary btn-full">Register Treasure</button>
        </form>
    </div>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
