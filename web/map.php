<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch all unfound treasures with coordinates
try {
    $db   = get_db();
    $stmt = $db->query(
        "SELECT t.ID, t.Name, t.ImagePath, t.Latitude, t.Longitude,
                f.Name AS ForestName
           FROM Treasure t
           LEFT JOIN Forest f ON t.ForestID = f.ID
          WHERE t.IsFound = 0
            AND t.Latitude  IS NOT NULL
            AND t.Longitude IS NOT NULL"
    );
    $pins = $stmt->fetchAll();
} catch (Exception $e) {
    $pins = [];
}

$page_title = 'Treasure Map';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="container">
        <h1>🗺 Treasure Map</h1>
        <p>Showing all unfound treasures. Allow location access to center the map on you.</p>
    </div>
</div>

<main class="container">

    <div class="map-controls">
        <button class="btn btn-primary btn-sm" id="btn-locate">📍 Find My Location</button>
        <span id="map-status" style="font-family:var(--font-ui);font-size:0.9rem;color:var(--text-light);">
            <?= count($pins) ?> treasure<?= count($pins) !== 1 ? 's' : '' ?> shown
        </span>
    </div>

    <div id="map-container"></div>

    <?php if (empty($pins)): ?>
        <div class="alert alert-info mt-3">No unfound treasures have coordinates yet. Check back soon!</div>
    <?php endif; ?>

</main>

<!-- Pass pin data to JS -->
<script>
window.FT_MAP_PINS = <?= json_encode(array_map(fn($p) => [
    'id'         => $p['ID'],
    'name'       => $p['Name'],
    'lat'        => (float)$p['Latitude'],
    'lng'        => (float)$p['Longitude'],
    'forestName' => $p['ForestName'] ?? 'Unknown Forest',
    'imagePath'  => $p['ImagePath'],
    'url'        => 'treasure.php?id=' . urlencode($p['ID']),
], $pins)) ?>;
window.FT_MAPS_KEY = <?= json_encode(GOOGLE_MAPS_API_KEY) ?>;
</script>

<!-- Load Google Maps after page JS defines the callback -->
<script src="assets/js/main.js"></script>
<script
    src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars(GOOGLE_MAPS_API_KEY) ?>&callback=initForestMap&libraries=marker"
    async defer>
</script>

<?php
// Override footer to avoid double main.js load
$skip_main_js = true;
require_once __DIR__ . '/includes/footer.php';
?>
