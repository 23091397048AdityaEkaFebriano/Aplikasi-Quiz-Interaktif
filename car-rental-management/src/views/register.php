<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #ffffff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      padding: 16px;
      margin: 0;
    }

    .register-card {
      background: #ffffff;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
    }

    .register-title {
      text-align: center;
      font-size: 32px;
      font-weight: 600;
      color: #007bff;
      margin-bottom: 30px;
    }

    label {
      font-weight: 500;
      margin-bottom: 8px;
      color: #333;
      display: block;
    }

    .form-control {
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 14px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
      width: 100%;
      display: block;
    }

    .form-control:focus {
      border-color: #6a11cb;
      box-shadow: 0 0 0 0.15rem rgba(106, 17, 203, 0.25);
    }

    .btn-primary {
      background-color: #007bff;
      border: none;
      font-weight: 600;
      border-radius: 10px;
      padding: 12px;
      color: #ffffff;
      width: 100%;
      transition: background-color 0.3s;
    }

    .btn-primary:hover {
      background-color: #0056b3;
    }

    .text-link {
      text-align: center;
      margin-top: 20px;
      font-size: 14px;
      color: #555;
    }

    .text-link a {
      color: #6a11cb;
      text-decoration: none;
      transition: text-decoration 0.3s;
    }

    .text-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="register-card">
    <h2 class="register-title">Register</h2>
    <form action="../controllers/auth.php?action=register" method="POST">
      <div>
        <label for="username">Username</label>
        <input type="text" id="username" name="username" class="form-control" required>
      </div>
      <div>
        <label for="password">Kata Sandi</label>
        <input type="password" id="password" name="password" class="form-control" required>
      </div>
      <div>
        <label for="role">Peran</label>
        <select id="role" name="role" class="form-control" required>
          <option value="borrower">Borrower</option>
        </select>
      </div>
      <button type="submit" class="btn-primary">Register</button>
    </form>
    <p class="text-link">Sudah punya akun? <a href="login.php">Login di sini</a></p>
  </div>
</body>
</html>
