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
            <p>Version: 3.0.0</p>
            <p>Last updated: <?= date("F j, Y", filemtime($_SERVER['SCRIPT_FILENAME'])); ?></p>
            <p>Current date: <?= date("F j, Y"); ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <strong> <p >Disclaimer: This website is for informational purposes only.</p></strong>
        </div>
    </div>
</footer>