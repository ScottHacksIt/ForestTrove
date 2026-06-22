<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = trim($_GET['id'] ?? '');
if (!$id) {
    header('Location: browse.php');
    exit;
}

// Load treasure
try {
    $db   = get_db();
    $stmt = $db->prepare(
        "SELECT t.*, f.Name AS ForestName
           FROM Treasure t
           LEFT JOIN Forest f ON t.ForestID = f.ID
          WHERE t.ID = ?
          LIMIT 1"
    );
    $stmt->execute([$id]);
    $treasure = $stmt->fetch();
} catch (Exception $e) {
    $treasure = null;
}

if (!$treasure) {
    header('Location: browse.php');
    exit;
}

$already_found = (bool)$treasure['IsFound'];
$success       = false;
$error         = '';

// Handle claim form submission
if (!$already_found && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name  = trim($_POST['first_name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (!$first_name) {
        $error = 'Please enter your first name.';
    } else {
        // Save image
        $image_path = null;
        if (!empty($_FILES['photo']['name'])) {
            $image_path = saveUploadedImage($_FILES['photo'], 'seeker_');
            if ($image_path === null) {
                $error = 'Could not save your photo. Please use JPEG, PNG, GIF, or WebP under 10MB.';
            }
        }

        if (!$error) {
            try {
                $db->beginTransaction();

                // Insert Seeker
                $stmt = $db->prepare(
                    'INSERT INTO Seeker (FirstName, ImagePath, Description) VALUES (?, ?, ?)'
                );
                $stmt->execute([$first_name, $image_path, $description ?: null]);
                $seeker_id = (int)$db->lastInsertId();

                // Update Treasure
                $stmt = $db->prepare(
                    'UPDATE Treasure SET IsFound = 1, SeekerID = ?, FoundDate = CURDATE() WHERE ID = ?'
                );
                $stmt->execute([$seeker_id, $id]);

                $db->commit();
                $success = true;

                // Reload treasure to show updated info
                $stmt = $db->prepare(
                    "SELECT t.*, f.Name AS ForestName,
                            s.FirstName AS SeekerName, s.ImagePath AS SeekerImage, s.Description AS SeekerDesc
                       FROM Treasure t
                       LEFT JOIN Forest f ON t.ForestID = f.ID
                       LEFT JOIN Seeker s ON t.SeekerID = s.ID
                      WHERE t.ID = ? LIMIT 1"
                );
                $stmt->execute([$id]);
                $treasure = $stmt->fetch();
            } catch (Exception $e) {
                $db->rollBack();
                $error = 'An error occurred while saving. Please try again.';
            }
        }
    }
}

$page_title = $success ? 'Treasure Claimed!' : 'Claim Treasure';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <a href="treasure.php?id=<?= urlencode($id) ?>" style="color:var(--green-pale);font-family:var(--font-ui);font-size:0.9rem;">← View Treasure Details</a>
        <h1 class="mt-1">
            <?php if ($already_found && !$success): ?>
                🏆 Already Claimed
            <?php elseif ($success): ?>
                🎉 You Found It!
            <?php else: ?>
                🪨 Claim This Treasure
            <?php endif; ?>
        </h1>
    </div>
</div>

<main class="container" style="max-width:760px;">

    <!-- Success state -->
    <?php if ($success): ?>
        <div class="claim-success">
            <div class="success-icon">🎉</div>
            <h2>Congratulations, <?= htmlspecialchars($treasure['SeekerName']) ?>!</h2>
            <p>
                You've officially claimed <strong><?= htmlspecialchars($treasure['Name']) ?></strong>!
                Your discovery has been recorded. Well done, forest explorer!
            </p>
            <a href="treasure.php?id=<?= urlencode($id) ?>" class="btn btn-primary">View Your Treasure</a>
            <a href="browse.php" class="btn btn-secondary mt-2" style="display:inline-block;margin-left:0.75rem;">Browse More</a>
        </div>

    <!-- Already found state -->
    <?php elseif ($already_found): ?>
        <div class="alert alert-info">
            This treasure has already been claimed! Keep exploring — there are more waiting to be found.
        </div>
        <div style="text-align:center;margin-top:1.5rem;">
            <a href="treasure.php?id=<?= urlencode($id) ?>" class="btn btn-secondary">See Who Found It</a>
            <a href="browse.php" class="btn btn-primary" style="margin-left:0.75rem;">Browse Unfound Treasures</a>
        </div>

    <!-- Claim form -->
    <?php else: ?>

        <!-- Treasure summary card -->
        <div class="form-section mb-4" style="display:flex;gap:1.5rem;align-items:flex-start;flex-wrap:wrap;">
            <?php if ($treasure['ImagePath']): ?>
                <img src="<?= htmlspecialchars($treasure['ImagePath']) ?>"
                     alt="<?= htmlspecialchars($treasure['Name']) ?>"
                     style="width:110px;height:110px;object-fit:cover;border-radius:var(--radius);flex-shrink:0;">
            <?php else: ?>
                <div style="width:110px;height:110px;background:var(--green-mist);border-radius:var(--radius);display:flex;align-items:center;justify-content:center;font-size:2.5rem;flex-shrink:0;">🪨</div>
            <?php endif; ?>
            <div>
                <h2 style="margin-bottom:0.25rem;"><?= htmlspecialchars($treasure['Name']) ?></h2>
                <p class="text-muted" style="margin-bottom:0.5rem;">🌲 <?= htmlspecialchars($treasure['ForestName'] ?? 'Unknown Forest') ?></p>
                <?php if ($treasure['Description']): ?>
                    <p style="font-size:0.95rem;color:var(--text-mid);margin:0;"><?= htmlspecialchars(mb_substr($treasure['Description'], 0, 200)) ?>…</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-section">
            <h3 class="mb-3">Tell us about your find!</h3>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" action="claim.php?id=<?= urlencode($id) ?>">
                <div class="form-group">
                    <label for="first_name">Your First Name <span style="color:var(--danger);">*</span></label>
                    <input type="text" id="first_name" name="first_name" required
                           maxlength="50"
                           value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>"
                           placeholder="e.g. Alex">
                </div>

                <div class="form-group">
                    <label for="photo">Photo of You with the Treasure</label>
                    <input type="file" id="photo" name="photo" accept="image/*">
                    <p class="hint">Optional. Accepted formats: JPEG, PNG, GIF, WebP.</p>
                </div>

                <div class="form-group">
                    <label for="description">Tell the Story of Your Find</label>
                    <textarea id="description" name="description" rows="4"
                              maxlength="5000"
                              placeholder="When did you find it? Were you surprised? What was the adventure like?"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-full">🪨 Claim This Treasure!</button>
            </form>
        </div>

    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
