<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$id = trim($_GET['id'] ?? '');
if (!$id) {
    header('Location: browse.php');
    exit;
}

try {
    $db   = get_db();
    $stmt = $db->prepare(
        "SELECT t.*,
                f.Name AS ForestName, f.GoogleLink AS ForestLink,
                s.FirstName AS SeekerName, s.ImagePath AS SeekerImage, s.Description AS SeekerDesc
           FROM Treasure t
           LEFT JOIN Forest  f ON t.ForestID  = f.ID
           LEFT JOIN Seeker  s ON t.SeekerID  = s.ID
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

$page_title = htmlspecialchars($treasure['Name']);
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <a href="browse.php" style="color:var(--green-pale);font-family:var(--font-ui);font-size:0.9rem;">← Back to Browse</a>
        <h1 class="mt-1"><?= htmlspecialchars($treasure['Name']) ?></h1>
    </div>
</div>

<main class="container">
    <div class="treasure-detail">

        <!-- Left: Image -->
        <div>
            <?php if ($treasure['ImagePath']): ?>
                <img class="treasure-detail-img"
                     src="<?= htmlspecialchars($treasure['ImagePath']) ?>"
                     alt="<?= htmlspecialchars($treasure['Name']) ?>">
            <?php else: ?>
                <div style="width:100%;min-height:300px;background:var(--green-mist);border-radius:var(--radius-lg);display:flex;align-items:center;justify-content:center;font-size:5rem;">🪨</div>
            <?php endif; ?>

            <?php if (!$treasure['IsFound']): ?>
                <a href="claim.php?id=<?= urlencode($treasure['ID']) ?>" class="btn btn-primary btn-full mt-3">
                    🪨 I Found This Treasure!
                </a>
            <?php endif; ?>

            <?php if (isAdmin()): ?>
                <a href="admin/edit-treasure.php?id=<?= urlencode($treasure['ID']) ?>" class="btn btn-secondary btn-full mt-2">
                    ✏ Edit This Treasure
                </a>
            <?php endif; ?>
        </div>

        <!-- Right: Details -->
        <div>
            <div class="flex-row mb-2">
                <span class="badge <?= $treasure['IsFound'] ? 'badge-found' : 'badge-unfound' ?>" style="font-size:0.9rem;padding:0.3rem 0.9rem;">
                    <?= $treasure['IsFound'] ? '✓ Found' : '● Still Hidden' ?>
                </span>
            </div>

            <?php if ($treasure['Description']): ?>
                <p style="font-size:1.05rem;color:var(--text-mid);margin-bottom:1.5rem;"><?= nl2br(htmlspecialchars($treasure['Description'])) ?></p>
            <?php endif; ?>

            <ul class="treasure-meta">
                <li>
                    <span class="meta-label">Treasure ID</span>
                    <span style="font-family:monospace;"><?= htmlspecialchars($treasure['ID']) ?></span>
                </li>
                <li>
                    <span class="meta-label">Forest</span>
                    <span>
                        <?php if ($treasure['ForestLink']): ?>
                            <a href="<?= htmlspecialchars($treasure['ForestLink']) ?>" target="_blank" rel="noopener">
                                <?= htmlspecialchars($treasure['ForestName'] ?? 'Unknown') ?> ↗
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars($treasure['ForestName'] ?? 'Unknown') ?>
                        <?php endif; ?>
                    </span>
                </li>
                <?php if ($treasure['Latitude'] && $treasure['Longitude']): ?>
                <li>
                    <span class="meta-label">Location</span>
                    <span>
                        <?= htmlspecialchars(number_format($treasure['Latitude'], 6)) ?>,
                        <?= htmlspecialchars(number_format($treasure['Longitude'], 6)) ?>
                        <a href="https://maps.google.com/?q=<?= urlencode($treasure['Latitude'] . ',' . $treasure['Longitude']) ?>"
                           target="_blank" rel="noopener" style="font-size:0.85rem;margin-left:0.5rem;">View on Google Maps ↗</a>
                    </span>
                </li>
                <?php endif; ?>
                <li>
                    <span class="meta-label">Date Placed</span>
                    <span><?= $treasure['PlacedDate'] ? htmlspecialchars(date('F j, Y', strtotime($treasure['PlacedDate']))) : 'Unknown' ?></span>
                </li>
                <li>
                    <span class="meta-label">Date Registered</span>
                    <span><?= htmlspecialchars(date('F j, Y', strtotime($treasure['CreatedDate']))) ?></span>
                </li>
                <li>
                    <span class="meta-label">Status</span>
                    <span><?= $treasure['IsFound'] ? '<strong style="color:var(--found-color);">Found!</strong>' : '<strong style="color:var(--unfound-color);">Still in the wild</strong>' ?></span>
                </li>
                <?php if ($treasure['IsFound'] && $treasure['FoundDate']): ?>
                <li>
                    <span class="meta-label">Date Found</span>
                    <span><?= htmlspecialchars(date('F j, Y', strtotime($treasure['FoundDate']))) ?></span>
                </li>
                <?php endif; ?>
                <?php if ($treasure['IsFound'] && $treasure['SeekerID']): ?>
                <li>
                    <span class="meta-label">Finder ID</span>
                    <span>#<?= htmlspecialchars($treasure['SeekerID']) ?></span>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Seeker info -->
            <?php if ($treasure['IsFound'] && $treasure['SeekerName']): ?>
            <h3 class="mt-3 mb-2">🏆 Found by</h3>
            <div class="seeker-card">
                <?php if ($treasure['SeekerImage']): ?>
                    <img class="seeker-avatar"
                         src="<?= htmlspecialchars($treasure['SeekerImage']) ?>"
                         alt="<?= htmlspecialchars($treasure['SeekerName']) ?>">
                <?php else: ?>
                    <div class="seeker-avatar-placeholder">🧑</div>
                <?php endif; ?>
                <div>
                    <strong><?= htmlspecialchars($treasure['SeekerName']) ?></strong>
                    <?php if ($treasure['SeekerDesc']): ?>
                        <p style="margin:0.5rem 0 0;font-size:0.95rem;color:var(--text-mid);">
                            <?= nl2br(htmlspecialchars($treasure['SeekerDesc'])) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
