<?php
$page_title = 'Welcome';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <div class="hero-trees">🌲 🌳 🌲 🌳 🌲</div>
    <h1>Forest Trove</h1>
    <p class="tagline">Hand-painted rock treasures hidden deep in the forest, waiting to be discovered by you.</p>
    <div class="cta-buttons">
        <a href="browse.php" class="btn btn-earth">Browse Treasures</a>
        <a href="map.php"    class="btn btn-secondary" style="border-color:var(--green-pale);color:var(--green-pale);">🗺 View Map</a>
    </div>
</section>

<!-- What is Forest Trove -->
<section style="padding:3.5rem 1.5rem; background:var(--white);">
    <div class="container">
        <h2 class="section-title">What is Forest Trove?</h2>
        <p style="text-align:center;max-width:700px;margin:0 auto 0;font-size:1.05rem;color:var(--text-mid);">
            Forest Trove is a scavenger hunt hidden in nature. Each <strong>Treasure</strong> is a small hand-painted rock
            with a unique QR code on the back. Treasures are secretly placed in forests around the area —
            and it's your job to find them!
        </p>
    </div>
</section>

<!-- How it Works -->
<section class="how-section">
    <div class="container">
        <h2>How it Works</h2>
        <div class="steps">

            <div class="step-card">
                <span class="step-number">1</span>
                <div class="step-illustration">🪨</div>
                <h3>A Rock is Painted</h3>
                <p>Each treasure starts as a plain rock, hand-painted with a unique woodland design and given its own ID and QR code.</p>
            </div>

            <div class="step-card">
                <span class="step-number">2</span>
                <div class="step-illustration">🌲</div>
                <h3>Hidden in the Forest</h3>
                <p>The painted rock is carefully placed somewhere in a real forest. Its location is registered on this website for seekers to discover.</p>
            </div>

            <div class="step-card">
                <span class="step-number">3</span>
                <div class="step-illustration">🔍</div>
                <h3>You Go Seek!</h3>
                <p>Browse the treasure list or check the map to find a treasure near you. Head out into the forest and go exploring!</p>
            </div>

            <div class="step-card">
                <span class="step-number">4</span>
                <div class="step-illustration">📱</div>
                <h3>Scan &amp; Claim</h3>
                <p>When you find a treasure, flip it over and scan the QR code. You'll land on this site where you can claim it as your own find!</p>
            </div>

        </div>
    </div>
</section>

<!-- Featured / Recent Treasures -->
<?php
require_once __DIR__ . '/config/db.php';
try {
    $db   = get_db();
    $stmt = $db->query(
        "SELECT t.ID, t.Name, t.ImagePath, t.IsFound, f.Name AS ForestName
           FROM Treasure t
           LEFT JOIN Forest f ON t.ForestID = f.ID
          ORDER BY t.CreatedDate DESC
          LIMIT 3"
    );
    $featured = $stmt->fetchAll();
} catch (Exception $e) {
    $featured = [];
}
?>
<?php if (!empty($featured)): ?>
<section style="padding:3.5rem 1.5rem; background:var(--cream);">
    <div class="container">
        <h2 class="section-title">Recently Added Treasures</h2>
        <div class="card-grid">
            <?php foreach ($featured as $t): ?>
            <a href="treasure.php?id=<?= urlencode($t['ID']) ?>" style="text-decoration:none;" class="treasure-card">
                <?php if ($t['ImagePath']): ?>
                    <img class="card-thumb" src="<?= htmlspecialchars($t['ImagePath']) ?>" alt="<?= htmlspecialchars($t['Name']) ?>">
                <?php else: ?>
                    <div class="card-thumb-placeholder">🪨</div>
                <?php endif; ?>
                <div class="card-body">
                    <h3><?= htmlspecialchars($t['Name']) ?></h3>
                    <p class="card-forest"><?= htmlspecialchars($t['ForestName'] ?? 'Unknown Forest') ?></p>
                    <div class="card-footer-row">
                        <span class="badge <?= $t['IsFound'] ? 'badge-found' : 'badge-unfound' ?>">
                            <?= $t['IsFound'] ? '✓ Found' : '● Waiting' ?>
                        </span>
                        <span class="btn btn-sm btn-secondary">View</span>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="browse.php" class="btn btn-primary">See All Treasures</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA -->
<section class="cta-band">
    <div class="container">
        <h2>Ready to Start Your Adventure?</h2>
        <p>Check the map to see where treasures are hiding, or browse the full collection.</p>
        <div class="cta-buttons">
            <a href="map.php"    class="btn btn-earth">🗺 Open the Map</a>
            <a href="browse.php" class="btn btn-secondary" style="border-color:var(--brown-pale);color:var(--brown-pale);">Browse All</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
