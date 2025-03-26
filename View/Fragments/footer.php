<!-- ../View/Fragments/footer.php -->
<footer class="container align-items-center text-center mt-5">
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
</footer>