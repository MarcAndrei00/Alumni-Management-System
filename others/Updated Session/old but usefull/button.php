<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prevent Multiple Clicks</title>
    <script>
        function disableButton(btn) {
            // Disable the button to prevent multiple clicks
            btn.disabled = true;

            // Optionally, change the button text or show a loading indicator
            btn.innerHTML = 'Processing...';
        }
    </script>
</head>
<body>
    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Your form processing code here
        echo "<p>Form has been submitted!</p>";
    }
    ?>

    <form method="post" action="">
        <button type="submit" onclick="disableButton(this)">Submit</button>
    </form>
</body>
</html>
