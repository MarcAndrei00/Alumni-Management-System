<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Message Box</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      margin: 0;
    }
    .background {
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .card {
      width: 350px; /* Adjusted to match the image size */
      height: 150px; /* Adjusted to match the image size */
      border-radius: 10px;
      border: none;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      text-align: center; /* Center align the text */
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8f9fa; /* Background color for better visibility */
    }
    .card-body {
      padding: 20px;
    }
    .login-now-btn {
      background-color: #6c63ff; /* Adjusted to match the image button color */
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
    }
    .login-now-btn:hover {
      background-color: #5750d7; /* Slightly darker on hover */
    }
  </style>
</head>
<body>
<div class="background">
  <div class="card">
    <div class="card-body">
      <div class="text-center mb-4">
        <p>Your password changed. Now you can login with your new password.</p>
        <button type="submit" class="btn btn-primary btn-block">Login Now</button>
      </div>
    </div>
  </div>
</div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
