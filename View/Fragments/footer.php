<footer class="container text-center py-4">
    <div class="row">
        <div class="col-md-4">
            <p>&copy; <?php echo date("Y"); ?> - All rights reserved.</p>
            <p>Page created on: <?php echo date("F j, Y", filectime($_SERVER['SCRIPT_FILENAME'])); ?></p>
        </div>
        <div class="col-md-4">
            <p>Developed by: Daniel Patiño</p>
            <p>Contact: <a href="mailto:Daniel.Cortes@3Shape.com">Daniel.Cortes@3Shape.com</a></p>
        </div>
        <div class="col-md-4">
            <p class="text-muted">Version: 3.0.0</p>
            <p class="text-muted">Last updated: <?php echo date("F j, Y", filemtime($_SERVER['SCRIPT_FILENAME'])); ?></p>
        </div>
    </div>
    <div class="social-media mt-3">
        <a href="" target="_blank" class="me-2">
            <i class="bi bi-linkedin"></i>
        </a>
        <a href="https://github.com/pati1005986/3Shape_project" target="_blank" class="me-2">
            <i class="bi bi-github"></i>
        </a>
    </div>
</footer>
