<!-- To prevent multiple submissions in PHP, you can use a combination of frontend and backend strategies. Here's an approach: -->
<!-- 1. Frontend Solution: Disable the Submit Button After First Click -->
<!-- You can use JavaScript to disable the submit button after the first click or form submission, preventing further submissions. -->

<!-- VERSION 1 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prevent Multiple Submissions</title>
</head>
<body>
    <form id="myForm" action="submit_form.php" method="post" onsubmit="disableButton()">
        <!-- Your form fields here -->
        <input type="text" name="exampleInput" required>
        <button type="submit" id="submitButton">Submit</button>
    </form>

    <script>
        function disableButton() {
            document.getElementById('submitButton').disabled = true;
        }
    </script>
</body>
</html>



<!-- VERSION 2 -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prevent Multiple Submissions</title>
</head>
<body>
    <form id="form1" action="submit_form1.php" method="post" id="form1" onsubmit="disableButton(this)">
        <input type="text" name="exampleInput1" required>
        <button type="submit">Submit Form 1</button>
    </form>

    <form id="form2" action="submit_form2.php" method="post" onsubmit="disableButton(this)">
        <input type="text" name="exampleInput2" required>
        <button type="submit">Submit Form 2</button>
    </form>

    <script>
        // PREVENT MULTIPLE FORM SUBMITTIONS
        function disableButton(form) {
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
        }
    </script>
</body>
</html>



<!-- FOR BUTTON -->
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