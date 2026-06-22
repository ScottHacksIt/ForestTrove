<footer class="site-footer">
    <div class="footer-trees">🌲🌳🌲🌳🌲</div>
    <p>&copy; <?= date('Y') ?> Forest Trove &mdash; Hand-painted rock treasures hidden in the wild.</p>
    <p class="mt-1">
        <a href="<?= $site_root ?>index.php">Home</a> &bull;
        <a href="<?= $site_root ?>browse.php">Browse Treasures</a> &bull;
        <a href="<?= $site_root ?>map.php">Map</a>
    </p>
</footer>

<?php if (empty($skip_main_js)): ?>
<script src="<?= $site_root ?>assets/js/main.js"></script>
<?php endif; ?>
</body>
</html>
