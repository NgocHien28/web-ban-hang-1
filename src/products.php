<?php
session_start();

// Nếu chưa đăng nhập thì quay về trang đăng nhập
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

// Tạo giỏ hàng nếu chưa tồn tại
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// Danh sách sản phẩm
$products = [
    [
        "id" => 1,
        "name" => "Laptop",
        "price" => 15000000,
        "description" => "Laptop hiện đại, phù hợp cho học tập và làm việc.",
        "image" => "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600"
    ],
    [
        "id" => 2,
        "name" => "Smartphone",
        "price" => 8000000,
        "description" => "Điện thoại thông minh với thiết kế hiện đại.",
        "image" => "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600"
    ],
    [
        "id" => 3,
        "name" => "Tai nghe",
        "price" => 1200000,
        "description" => "Tai nghe không dây với chất lượng âm thanh tốt.",
        "image" => "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600"
    ],
    [
        "id" => 4,
        "name" => "Bàn phím",
        "price" => 900000,
        "description" => "Bàn phím hiện đại, phù hợp cho công việc và giải trí.",
        "image" => "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600"
    ],
    [
        "id" => 5,
        "name" => "Chuột",
        "price" => 500000,
        "description" => "Chuột không dây nhỏ gọn và tiện lợi.",
        "image" => "https://images.unsplash.com/photo-1527814050087-3793815479db?w=600"
    ],
    [
        "id" => 6,
        "name" => "Màn hình",
        "price" => 4500000,
        "description" => "Màn hình sắc nét, phù hợp cho công việc và giải trí.",
        "image" => "https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600"
    ]
];

// Tính tổng số lượng sản phẩm trong giỏ hàng
$cartCount = 0;

foreach ($_SESSION["cart"] as $item) {
    $cartCount += $item["quantity"];
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Danh sách sản phẩm</title>

    <link
        rel="stylesheet"
        href="./css/products.css">
</head>

<body>
    <div id="success-message" class="success-message"></div>
    <!-- Header -->
    <header class="header">

        <div class="container header-container">

            <div class="logo">
                MyShop
            </div>

            <nav class="nav">

                <a
                    href="products.php"
                    class="active">
                    Sản phẩm
                </a>

                <a href="cart.php">
                    Giỏ hàng

                    <span
                        class="cart-count"
                        id="cart-count">

                        <?php echo $cartCount; ?>

                    </span>
                </a>

                <a
                    href="logout.php"
                    class="logout">
                    Đăng xuất
                </a>

            </nav>

        </div>

    </header>


    <!-- Main Content -->
    <main class="container">

        <!-- Page Title -->
        <section class="page-header">

            <div>

                <h1>
                    Danh sách sản phẩm
                </h1>

                <p>
                    Xin chào,
                    <?php echo htmlspecialchars($_SESSION["username"]); ?>!
                </p>

            </div>

        </section>


        <!-- Product List -->
        <section class="product-grid">

            <?php foreach ($products as $product): ?>

                <div class="product-card">

                    <!-- Product Image -->
                    <div class="product-image">

                        <img
                            src="<?php echo $product["image"]; ?>"
                            alt="<?php echo htmlspecialchars($product["name"]); ?>">

                    </div>


                    <!-- Product Information -->
                    <div class="product-content">

                        <h2>
                            <?php echo htmlspecialchars($product["name"]); ?>
                        </h2>

                        <p class="description">
                            <?php echo htmlspecialchars($product["description"]); ?>
                        </p>

                        <p class="price">

                            <?php
                            echo number_format(
                                $product["price"],
                                0,
                                ",",
                                "."
                            );
                            ?>

                            đ

                        </p>


                        <!-- Add To Cart -->
                        <form class="add-to-cart-form">

                            <input
                                type="hidden"
                                name="action"
                                value="add">

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?php echo $product["id"]; ?>">

                            <button type="submit">
                                Thêm vào giỏ hàng
                            </button>

                        </form>
                    </div>

                </div>

            <?php endforeach; ?>

        </section>

    </main>
    <script>
        const forms = document.querySelectorAll(".add-to-cart-form");

        const successMessage =
            document.getElementById("success-message");

        const cartCount =
            document.getElementById("cart-count");

        let hideMessageTimer;


        forms.forEach(function(form) {

            form.addEventListener("submit", function(event) {

                // Không reload trang
                event.preventDefault();


                // Lấy dữ liệu từ form
                const formData = new FormData(form);


                // Gửi dữ liệu sang cart.php, AJAX giúp chạy ngầm trang cart.php
                fetch("cart.php", {

                        method: "POST",
                        body: formData

                    })

                    .then(function(response) {

                        return response.json();

                    })

                    .then(function(data) {


                        if (data.success) {


                            // =========================
                            // Cập nhật số lượng giỏ hàng
                            // =========================

                            cartCount.textContent =
                                data.cartCount;


                            // =========================
                            // Hiển thị thông báo
                            // =========================

                            successMessage.textContent =
                                data.message;

                            successMessage.classList.add(
                                "show"
                            );


                            // Xóa timer cũ
                            clearTimeout(
                                hideMessageTimer
                            );


                            // Sau 2.5 giây ẩn thông báo
                            hideMessageTimer =
                                setTimeout(function() {

                                    successMessage
                                        .classList
                                        .remove("show");

                                }, 2500);

                        }

                    })

                    .catch(function(error) {

                        console.error(
                            "Error:",
                            error
                        );

                    });

            });

        });
    </script>
</body>

</html>