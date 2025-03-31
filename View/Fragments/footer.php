<footer class="container text-center py-4">
    <div class="row">
        <div class="col-md-4">
            <p>&copy; <?= date("Y"); ?> - All rights reserved.</p>
            <p>Page created on: <?= date("F j, Y", filectime($_SERVER['SCRIPT_FILENAME'])); ?></p>
        </div>
        <div class="col-md-4">
            <p>Developed by: Daniel Patiño</p>
            <p>Contact: <a href="mailto:Daniel.Cortes@3Shape.com">Daniel.Cortes@3Shape.com</a></p>
        </div>
        <div class="col-md-4">
            <p class="text-muted">Version: 3.0.0</p>
            <p class="text-muted">Last updated: <?= date("F j, Y", filemtime($_SERVER['SCRIPT_FILENAME'])); ?></p>
            <p class="text-muted">Current date: <?= date("F j, Y"); ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p class="text-muted">Disclaimer: This website is for informational purposes only.</p>
        </div>
    </div>
</footer>