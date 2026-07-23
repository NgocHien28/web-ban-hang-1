<?php
session_start();

// Nếu chưa đăng nhập thì quay về trang đăng nhập
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit;
}

// Nếu chưa có giỏ hàng thì tạo giỏ hàng rỗng
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}


// Danh sách sản phẩm
$products = [
    1 => [
        "id" => 1,
        "name" => "Laptop",
        "price" => 15000000,
        "image" => "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600"
    ],

    2 => [
        "id" => 2,
        "name" => "Smartphone",
        "price" => 8000000,
        "image" => "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600"
    ],

    3 => [
        "id" => 3,
        "name" => "Tai nghe",
        "price" => 1200000,
        "image" => "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600"
    ],

    4 => [
        "id" => 4,
        "name" => "Bàn phím",
        "price" => 900000,
        "image" => "https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=600"
    ],

    5 => [
        "id" => 5,
        "name" => "Chuột",
        "price" => 500000,
        "image" => "https://images.unsplash.com/photo-1527814050087-3793815479db?w=600"
    ],

    6 => [
        "id" => 6,
        "name" => "Màn hình",
        "price" => 4500000,
        "image" => "https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=600"
    ]
];


// Lấy action được gửi từ form
$action = $_POST["action"] ?? "";

// Lấy product_id
$productId = isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;


/* =========================
   Thêm sản phẩm
========================= */

if ($action === "add") {

    // Kiểm tra sản phẩm có tồn tại không
    if (isset($products[$productId])) {

        // Nếu sản phẩm đã có trong giỏ hàng
        if (isset($_SESSION["cart"][$productId])) {

            // Tăng số lượng lên 1
            $_SESSION["cart"][$productId]["quantity"]++;
        } else {

            // Nếu chưa có thì thêm mới
            $_SESSION["cart"][$productId] = [
                "id" => $productId,
                "quantity" => 1
            ];
        }
    }

    header("Location: cart.php");
    exit;
}


/* =========================
   Tăng số lượng
========================= */

if ($action === "increase") {

    if (isset($_SESSION["cart"][$productId])) {
        $_SESSION["cart"][$productId]["quantity"]++;
    }

    header("Location: cart.php");
    exit;
}


/* =========================
   Giảm số lượng
========================= */

if ($action === "decrease") {

    if (isset($_SESSION["cart"][$productId])) {

        $_SESSION["cart"][$productId]["quantity"]--;

        // Nếu số lượng bằng 0 thì xóa sản phẩm
        if ($_SESSION["cart"][$productId]["quantity"] <= 0) {
            unset($_SESSION["cart"][$productId]);
        }
    }

    header("Location: cart.php");
    exit;
}


/* =========================
   Xóa sản phẩm
========================= */

if ($action === "remove") {

    if (isset($_SESSION["cart"][$productId])) {
        unset($_SESSION["cart"][$productId]);
    }

    header("Location: cart.php");
    exit;
}


/* =========================
   Xóa toàn bộ giỏ hàng
========================= */

if ($action === "clear") {

    $_SESSION["cart"] = [];

    header("Location: cart.php");
    exit;
}


/* =========================
   Tính tổng số lượng
========================= */

$cartCount = 0;

foreach ($_SESSION["cart"] as $item) {
    $cartCount += $item["quantity"];
}


/* =========================
   Tính tổng tiền
========================= */

$totalPrice = 0;

foreach ($_SESSION["cart"] as $item) {

    $id = $item["id"];

    if (isset($products[$id])) {

        $totalPrice +=
            $products[$id]["price"]
            *
            $item["quantity"];
    }
}
?>

<!DOCTYPE html>
<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Giỏ hàng</title>

    <link
        rel="stylesheet"
        href="./css/cart.css">

</head>

<body>


    <!-- Header -->

    <header class="header">

        <div class="container header-container">

            <div class="logo">
                MyShop
            </div>

            <nav class="nav">

                <a href="products.php">
                    Sản phẩm
                </a>

                <a
                    href="cart.php"
                    class="active">

                    Giỏ hàng

                    <span class="cart-count">

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


    <!-- Main -->

    <main class="container">


        <!-- Page Header -->

        <section class="page-header">

            <div>

                <h1>
                    Giỏ hàng của bạn
                </h1>

                <p>
                    Kiểm tra và cập nhật sản phẩm trong giỏ hàng.
                </p>

            </div>

        </section>


        <?php if (empty($_SESSION["cart"])): ?>


            <!-- Giỏ hàng trống -->

            <div class="empty-cart">

                <div class="empty-icon">
                    🛒
                </div>

                <h2>
                    Giỏ hàng của bạn đang trống
                </h2>

                <p>
                    Hãy thêm sản phẩm mà bạn yêu thích vào giỏ hàng.
                </p>

                <a
                    href="products.php"
                    class="continue-shopping">

                    Tiếp tục mua sắm

                </a>

            </div>


        <?php else: ?>


            <!-- Cart Layout -->

            <div class="cart-layout">


                <!-- Danh sách sản phẩm -->

                <section class="cart-list">

                    <?php foreach ($_SESSION["cart"] as $item): ?>

                        <?php

                        $id = $item["id"];

                        if (!isset($products[$id])) {
                            continue;
                        }

                        $product = $products[$id];

                        $quantity = $item["quantity"];

                        $subtotal =
                            $product["price"]
                            *
                            $quantity;

                        ?>


                        <div class="cart-item">


                            <!-- Image -->

                            <div class="cart-image">

                                <img
                                    src="<?php echo $product["image"]; ?>"
                                    alt="<?php echo htmlspecialchars($product["name"]); ?>">

                            </div>


                            <!-- Product Info -->

                            <div class="cart-info">

                                <h2>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["name"]
                                    );
                                    ?>

                                </h2>

                                <p class="product-price">

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


                                <!-- Quantity -->

                                <div class="quantity-control">


                                    <!-- Decrease -->

                                    <form
                                        action="cart.php"
                                        method="post">

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="decrease">

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?php echo $id; ?>">

                                        <button
                                            type="submit"
                                            class="quantity-button">

                                            −

                                        </button>

                                    </form>


                                    <span class="quantity">

                                        <?php echo $quantity; ?>

                                    </span>


                                    <!-- Increase -->

                                    <form
                                        action="cart.php"
                                        method="post">

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="increase">

                                        <input
                                            type="hidden"
                                            name="product_id"
                                            value="<?php echo $id; ?>">

                                        <button
                                            type="submit"
                                            class="quantity-button">

                                            +

                                        </button>

                                    </form>

                                </div>

                            </div>


                            <!-- Right -->

                            <div class="cart-right">

                                <p class="subtotal">

                                    <?php
                                    echo number_format(
                                        $subtotal,
                                        0,
                                        ",",
                                        "."
                                    );
                                    ?>

                                    đ

                                </p>


                                <!-- Remove -->

                                <form
                                    action="cart.php"
                                    method="post">

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="remove">

                                    <input
                                        type="hidden"
                                        name="product_id"
                                        value="<?php echo $id; ?>">

                                    <button
                                        type="submit"
                                        class="remove-button">

                                        Xóa

                                    </button>

                                </form>

                            </div>


                        </div>


                    <?php endforeach; ?>

                </section>


                <!-- Summary -->

                <aside class="cart-summary">

                    <h2>
                        Tổng giỏ hàng
                    </h2>


                    <div class="summary-row">

                        <span>
                            Tổng sản phẩm
                        </span>

                        <span>

                            <?php echo $cartCount; ?>

                        </span>

                    </div>


                    <div class="summary-row total">

                        <span>
                            Tổng tiền
                        </span>

                        <span>

                            <?php
                            echo number_format(
                                $totalPrice,
                                0,
                                ",",
                                "."
                            );
                            ?>

                            đ

                        </span>

                    </div>


                    <a
                        href="products.php"
                        class="continue-button">

                        Tiếp tục mua sắm

                    </a>


                    <form
                        action="cart.php"
                        method="post">

                        <input
                            type="hidden"
                            name="action"
                            value="clear">

                        <button
                            type="submit"
                            class="clear-button">

                            Xóa toàn bộ giỏ hàng

                        </button>

                    </form>

                </aside>


            </div>


        <?php endif; ?>


    </main>


</body>

</html>