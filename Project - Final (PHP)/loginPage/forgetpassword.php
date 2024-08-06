<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body, html {
      height: 100%;
      margin: 0;
    }
    .background {
      background-image: url('bg2.png'); /* Update the path accordingly if necessary */
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .card {
      max-width: 600px;
      border-radius: 10px;
      border: none;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .card-body {
      padding: 100px;
    }
    .form-control {
      border-radius: 5px;
    }
    .btn {
      border-radius: 5px;
    }
    .back-to-login {
      display: block;
      text-align: center;
      margin-top: 10px;
    }
    .icon-size {
      width: 48px;
      height: 48px;
    }
    .footer {
      margin-top: 20px;
      text-align: center;
      color: #777;
    }
  </style>
</head>
<body>
<div class="background">
  <div class="card">
    <div class="card-body">
      <div class="text-center mb-4">
        <img src="cvsu.png" alt="Warning Icon" class="icon-size">
      </div>
      <h5 class="text-center mb-4">Forgot Password</h5>
      <p class="text-center mb-4">Enter your email and we'll send you a link to reset your password.</p>
      <form>
        <div class="form-group">
          <input type="email" class="form-control" id="email" placeholder="Enter your email">
        </div>
        <button type="submit" class="btn btn-primary btn-block">Submit</button>
      </form>
      <a href="#" class="back-to-login">Back to Login</a>
    </div>
  </div>
</div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
