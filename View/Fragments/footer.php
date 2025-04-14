
<link rel="stylesheet" href="/3Shape_project/View/Css/footer.css">
<footer class="container py-5 border-top bg-light">
    <div class="row justify-content-center">
        <div class="col-md-4 mb-4 footer-section">
            <h6 class="text-uppercase fw-bold mb-3 d-flex align-items-center footer-title">
                <i class="fas fa-balance-scale me-2 footer-icon"></i>Legal Information
            </h6>
            <p class="mb-2"><i class="far fa-copyright me-2 footer-icon"></i><?= date("Y"); ?> - All rights reserved.</p>
            <p class="text-muted small"><i class="fas fa-info-circle me-2 footer-icon"></i>Notice: This website is for informational purposes only.</p>
        </div>
        <div class="col-md-4 mb-4 footer-section">
            <h6 class="text-uppercase fw-bold mb-3 d-flex align-items-center footer-title">
                <i class="fas fa-address-card me-2 footer-icon"></i>Contact Information
            </h6>
            <p class="mb-2"><i class="fas fa-user me-2 footer-icon"></i>Developed by: Daniel Patiño</p>
            <p class="mb-2"><i class="fas fa-envelope me-2 footer-icon"></i><a href="mailto:Daniel.Cortes@3Shape.com" class="text-decoration-none footer-link">Daniel.Cortes@3Shape.com</a></p>
            <p class="mb-2"><i class="fab fa-github me-2 footer-icon"></i><a href="https://github.com/pati1005986" class="text-decoration-none footer-link">pati1005986</a></p>
            <p class="mb-2"><i class="fab fa-linkedin me-2 footer-icon"></i><a href="#" class="text-decoration-none footer-link">LinkedIn</a></p>
            <p><i class="fas fa-book me-2 footer-icon"></i><a href="/3Shape_project/View/Int_manual.html" class="text-decoration-none footer-link">User Manual</a></p>
        </div>
        <div class="col-md-4 mb-4 footer-section">
            <h6 class="text-uppercase fw-bold mb-3 d-flex align-items-center footer-title">
                <i class="fas fa-cogs me-2 footer-icon"></i>Technical Details
            </h6>
            <p class="text-muted mb-2"><i class="fas fa-code-branch me-2 footer-icon"></i>Version: 4.0.0</p>
            <p class="text-muted mb-2"><i class="fas fa-calendar-plus me-2 footer-icon"></i>Created: <?= date("F j, Y", filectime($_SERVER['SCRIPT_FILENAME'])); ?></p>
            <p class="text-muted mb-2"><i class="fas fa-calendar-check me-2 footer-icon"></i>Last Updated: <?= date("F j, Y", filemtime($_SERVER['SCRIPT_FILENAME'])); ?></p>
        </div>
    </div>
</footer>