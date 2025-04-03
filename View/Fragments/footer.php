<footer class="container-fluid bg-light py-4 mt-5 border-top">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="footer-section">
                    <p class="mb-1">&copy; <?= date("Y"); ?> - All rights reserved</p>
                    <p class="mb-0 text-muted small">Page created on: <?= date("F j, Y", filectime($_SERVER['SCRIPT_FILENAME'])); ?></p>
                </div>
            </div>
            
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="footer-section">
                    <p class="mb-1">Developed by: Daniel Patiño</p>
                    <p class="mb-0">Contact: <a href="mailto:Daniel.Cortes@3Shape.com" class="text-decoration-none">Daniel.Cortes@3Shape.com</a></p>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="footer-section">
                    <p class="mb-1">Version: 3.0.0</p>
                    <p class="mb-1 text-muted small">Last updated: <?= date("F j, Y", filemtime($_SERVER['SCRIPT_FILENAME'])); ?></p>
                    <p class="mb-0 text-muted small">Current date: <?= date("F j, Y"); ?></p>
                </div>
            </div>
        </div>
        
        <div class="row mt-3">
            <div class="col-12">
                <p class="text-center text-muted small fw-bold mb-0">
                    Disclaimer: This website is for informational purposes only.
                </p>
            </div>
        </div>
    </div>
</footer>