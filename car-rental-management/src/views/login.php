<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Car Rental</title>
  <link rel="stylesheet" href="../public/bootstrap/bootstrap.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
  <style>
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

    .login-card {
      background: #ffffff;
      padding: 40px 30px;
      border-radius: 20px;
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
      width: 100%;
      max-width: 400px;
    }

    .login-title {
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
      padding: 12px; /* Sama rata untuk semua sisi */
      font-size: 14px;
      border: 1px solid #ccc;
      transition: border-color 0.3s;
      margin-bottom: 20px;
      width: 100%;
      box-sizing: border-box; /* Pastikan padding tidak menambah lebar */
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
      transition: background-color 0.3s;
      width: 100%;
      box-sizing: border-box;
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

    .alert {
      background-color: #f8d7da;
      color: #721c24;
      padding: 10px;
      border-radius: 5px;
      margin-bottom: 20px;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="login-card">
    <h2 class="login-title">Login</h2>
    <?php if (isset($_GET['error'])): ?>
      <div class="alert">Username, password, atau role salah!</div>
    <?php endif; ?>
    <form action="../controllers/auth.php?action=login" method="POST">
      <div class="mb-3">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="password">Kata Sandi</label>
        <input type="password" name="password" id="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="role">Peran</label>
        <select name="role" id="role" class="form-control" required>
          <option value="admin">Admin</option>
          <option value="borrower">Borrower</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
    </form>
    <div class="text-link">
      Belum punya akun? <a href="register.php">Daftar di sini</a>
    </div>
  </div>

</body>
</html>
