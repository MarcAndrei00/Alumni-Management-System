

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification</title>
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
      background-color: #f8f9fa;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }
    .card {
      width: 600px;
      border-radius: 10px;
      border: none;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .card-body {
      padding: 100px;
      height: 517px;
    }
    .form-control {
      border-radius: 5px;
      height: 45px;
    }
    .btn {
      border-radius: 5px;
      height: 40px;
      font-size: 16px;
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
  </style>
</head>
<body>
<div class="background">
  <div class="card">
    <div class="card-body">
      <div class="text-center mb-4">
        <img src="cvsu.png" alt="Logo" class="icon-size">
      </div>
      <h5 class="text-center mb-4">Verification Code</h5>
      <form>
        <div class="form-group">
          <input type="text" class="form-control" id="code" placeholder="Enter a Code">
        </div>
        <a href="#" class="back-to-login">Resend Code</a>
        <br>
        <button type="submit" class="btn btn-primary btn-block" style="width: 48%; float: left;">Submit</button>
        <button type="submit" class="btn btn-primary btn-block" style="width: 48%; float: right; margin: 0px;">Back</button>
      </form>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
