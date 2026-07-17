<?php
session_start();

if (isset($_SESSION["username"])) {
    header("Location: products.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"] ?? "";
    $password = $_POST["password"] ?? "";

    $correctUsername = "admin";
    $correctPassword = "123456";

    if ($username === $correctUsername && $password === $correctPassword) {
        $_SESSION["username"] = $username;

        header("Location: products.php");
        exit;
    } else {
        $error = "Username hoặc Password không đúng.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Đăng nhập</title>

    <link rel="stylesheet" href="./css/index.css">
</head>

<body>

    <div class="background-shape shape-one"></div>
    <div class="background-shape shape-two"></div>

    <div class="login-container">

        <div class="logo">
            🛒
        </div>

        <h1>Đăng nhập</h1>

        <p class="subtitle">
            Vui lòng đăng nhập để tiếp tục
        </p>

        <?php if ($error !== ""): ?>
            <div class="error">
                <span class="error-icon">!</span>
                <span>
                    <?php echo htmlspecialchars($error); ?>
                </span>
            </div>
        <?php endif; ?>

        <form method="post" action="">

            <div class="input-group">
                <span class="input-icon">👤</span>

                <input
                    type="text"
                    name="username"
                    placeholder="Username"
                    required>
            </div>

            <div class="input-group">
                <span class="input-icon">🔒</span>

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required>
            </div>

            <div class="login-options">

                <label class="remember">
                    <input type="checkbox">
                    <span>Ghi nhớ đăng nhập</span>
                </label>

                <a href="#" class="forgot-password">
                    Quên mật khẩu?
                </a>

            </div>

            <button type="submit" class="login-button">
                Login
            </button>

        </form>

    </div>

</body>

</html>