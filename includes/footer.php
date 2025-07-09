</main>
            </div>
        </div>

        <!-- Mobile Layout -->
        <div class="mobile-layout">
            <?php include 'components/mobile-header.php'; ?>
            <main class="mobile-content">
                <!-- Mobile content will be injected here -->
            </main>
            <?php include 'components/mobile-navigation.php'; ?>
        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/dialer.js"></script>
    <?php if (isset($additionalScripts)): ?>
        <?php foreach ($additionalScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>