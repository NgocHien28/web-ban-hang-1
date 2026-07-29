<?php

session_start();


// =========================
// Kiểm tra đăng nhập
// =========================

if (!isset($_SESSION["username"])) {

    header("Location: index.php");

    exit;
}


// =========================
// Khởi tạo giỏ hàng
// =========================

if (!isset($_SESSION["cart"])) {

    $_SESSION["cart"] = [];
}


// =========================
// Load dữ liệu sản phẩm
// =========================

$productsFile =
    __DIR__
    . "/data/products_data.php";


if (!file_exists($productsFile)) {

    die("Không tìm thấy file dữ liệu sản phẩm: "
        . htmlspecialchars($productsFile));
}


$allProducts =
    require $productsFile;


if (!is_array($allProducts)) {

    die("File products_data.php phải trả về một mảng sản phẩm.");
}


// =========================
// Chuyển danh sách sản phẩm
// thành mảng có key là ID
// =========================

$products = [];


foreach ($allProducts as $product) {

    $id =
        (int) (
            $product["id"]
            ?? 0
        );


    if ($id <= 0) {

        continue;
    }


    $products[$id] =
        $product;
}


// =========================
// Hàm tính giá sau khuyến mãi
// =========================

function calculateSalePrice(
    int $price,
    int $discount
): int {

    $discount =
        max(
            0,
            min(
                100,
                $discount
            )
        );


    if ($discount <= 0) {

        return $price;
    }


    return (int) round(
        $price
            -
            (
                $price
                *
                $discount
                /
                100
            )
    );
}


// =========================
// Nhận dữ liệu POST
// =========================

$action =
    $_POST["action"]
    ?? "";


$productId =
    isset($_POST["product_id"])
    ? (int) $_POST["product_id"]
    : 0;


$isAjax =
    ($_POST["ajax"] ?? "")
    === "1";


// =========================
// Add / Increase / Decrease
// =========================

if (
    $action === "add"
    ||
    $action === "increase"
    ||
    $action === "decrease"
) {


    if (
        isset(
            $products[$productId]
        )
    ) {


        // =========================
        // Add hoặc Increase
        // =========================

        if (
            $action === "add"
            ||
            $action === "increase"
        ) {


            if (
                isset(
                    $_SESSION["cart"][$productId]
                )
            ) {

                $_SESSION["cart"][$productId]["quantity"]++;
            } else {

                $_SESSION["cart"][$productId] = [

                    "id" =>
                    $productId,

                    "quantity" =>
                    1

                ];
            }
        }


        // =========================
        // Decrease
        // =========================

        if (
            $action === "decrease"
        ) {


            if (
                isset(
                    $_SESSION["cart"][$productId]
                )
            ) {

                $_SESSION["cart"][$productId]["quantity"]--;


                if (
                    $_SESSION["cart"][$productId]["quantity"]
                    <= 0
                ) {

                    unset(
                        $_SESSION["cart"][$productId]
                    );
                }
            }
        }


        // =========================
        // Số lượng sản phẩm hiện tại
        // =========================

        $itemQuantity =
            $_SESSION["cart"][$productId]["quantity"]
            ?? 0;


        // =========================
        // Tổng số lượng giỏ hàng
        // =========================

        $cartCount = 0;


        foreach (
            $_SESSION["cart"]
            as $item
        ) {

            $cartCount +=
                (int) (
                    $item["quantity"]
                    ?? 0
                );
        }


        // =========================
        // AJAX response
        // =========================

        if ($isAjax) {

            header(
                "Content-Type: application/json; charset=UTF-8"
            );


            echo json_encode(
                [

                    "success" =>
                    true,

                    "message" =>
                    "Đã cập nhật giỏ hàng!",

                    "cartCount" =>
                    $cartCount,

                    "itemQuantity" =>
                    $itemQuantity

                ],
                JSON_UNESCAPED_UNICODE
            );


            exit;
        }
    }


    // AJAX nhưng sản phẩm không tồn tại

    if ($isAjax) {

        header(
            "Content-Type: application/json; charset=UTF-8"
        );


        http_response_code(404);


        echo json_encode(
            [

                "success" =>
                false,

                "message" =>
                "Không tìm thấy sản phẩm."

            ],
            JSON_UNESCAPED_UNICODE
        );


        exit;
    }


    header(
        "Location: cart.php"
    );

    exit;
}


// =========================
// Remove
// =========================

if ($action === "remove") {


    if (
        isset(
            $_SESSION["cart"][$productId]
        )
    ) {

        unset(
            $_SESSION["cart"][$productId]
        );
    }


    header(
        "Location: cart.php"
    );

    exit;
}


// =========================
// Clear
// =========================

if ($action === "clear") {

    $_SESSION["cart"] = [];


    header(
        "Location: cart.php"
    );

    exit;
}


// =========================
// Xóa sản phẩm không còn tồn tại
// trong products_data.php
// =========================

foreach (
    $_SESSION["cart"]
    as $id => $item
) {

    if (
        !isset(
            $products[$id]
        )
    ) {

        unset(
            $_SESSION["cart"][$id]
        );
    }
}


// =========================
// Cart Count
// =========================

$cartCount = 0;


foreach (
    $_SESSION["cart"]
    as $item
) {

    $cartCount +=
        (int) (
            $item["quantity"]
            ?? 0
        );
}


// =========================
// Tổng tiền
// =========================

$totalOriginalPrice = 0;

$totalDiscountAmount = 0;

$totalPrice = 0;


foreach (
    $_SESSION["cart"]
    as $item
) {

    $id =
        (int) (
            $item["id"]
            ?? 0
        );


    $quantity =
        (int) (
            $item["quantity"]
            ?? 0
        );


    if (
        !isset(
            $products[$id]
        )
    ) {

        continue;
    }


    $product =
        $products[$id];


    $originalPrice =
        (int) (
            $product["price"]
            ?? 0
        );


    $discount =
        (int) (
            $product["discount"]
            ?? 0
        );


    $salePrice =
        calculateSalePrice(
            $originalPrice,
            $discount
        );


    $originalSubtotal =
        $originalPrice
        *
        $quantity;


    $saleSubtotal =
        $salePrice
        *
        $quantity;


    $totalOriginalPrice +=
        $originalSubtotal;


    $totalPrice +=
        $saleSubtotal;
}


$totalDiscountAmount =
    $totalOriginalPrice
    -
    $totalPrice;

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Giỏ hàng
    </title>

    <link
        rel="stylesheet"
        href="./css/cart.css?v=2">

</head>


<body>


    <!-- =========================
     Header
========================= -->

    <header class="header">

        <div class="container header-container">


            <a
                href="products.php"
                class="logo">
                MyShop
            </a>


            <nav class="nav">

                <a href="products.php">

                    Sản phẩm

                </a>


                <a
                    href="cart.php"
                    class="active">

                    Giỏ hàng

                    <span class="cart-count">

                        <?php
                        echo $cartCount;
                        ?>

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


    <!-- =========================
     Main
========================= -->

    <main class="container">


        <section class="page-header">

            <h1>
                Giỏ hàng của bạn
            </h1>

            <p>
                Kiểm tra và cập nhật sản phẩm trong giỏ hàng.
            </p>

        </section>


        <?php if (
            empty($_SESSION["cart"])
        ): ?>


            <!-- Empty Cart -->

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


            <div class="cart-layout">


                <!-- =========================
                 Cart List
            ========================= -->

                <section class="cart-list">


                    <?php
                    foreach (
                        $_SESSION["cart"]
                        as $item
                    ):
                    ?>


                        <?php

                        $id =
                            (int) (
                                $item["id"]
                                ?? 0
                            );


                        if (
                            !isset(
                                $products[$id]
                            )
                        ) {

                            continue;
                        }


                        $product =
                            $products[$id];


                        $quantity =
                            (int) (
                                $item["quantity"]
                                ?? 0
                            );


                        $productName =
                            (string) (
                                $product["name"]
                                ?? "Sản phẩm"
                            );


                        $productImage =
                            (string) (
                                $product["image"]
                                ?? ""
                            );


                        $originalPrice =
                            (int) (
                                $product["price"]
                                ?? 0
                            );


                        $discount =
                            (int) (
                                $product["discount"]
                                ?? 0
                            );


                        $discount =
                            max(
                                0,
                                min(
                                    100,
                                    $discount
                                )
                            );


                        $salePrice =
                            calculateSalePrice(
                                $originalPrice,
                                $discount
                            );


                        $originalSubtotal =
                            $originalPrice
                            *
                            $quantity;


                        $subtotal =
                            $salePrice
                            *
                            $quantity;

                        ?>


                        <article class="cart-item">


                            <!-- Image -->

                            <div class="cart-image">

                                <?php if (
                                    $discount > 0
                                ): ?>

                                    <span class="cart-sale-badge">

                                        -<?php
                                            echo $discount;
                                            ?>%

                                    </span>

                                <?php endif; ?>


                                <img
                                    src="<?php
                                            echo htmlspecialchars(
                                                $productImage
                                            );
                                            ?>"
                                    alt="<?php
                                            echo htmlspecialchars(
                                                $productName
                                            );
                                            ?>"
                                    onerror="
                                    this.onerror = null;
                                    this.src =
                                    'https://placehold.co/300x300/f3f4f6/64748b?text=Khong+co+hinh';
                                ">

                            </div>


                            <!-- Info -->

                            <div class="cart-info">


                                <h2>

                                    <?php
                                    echo htmlspecialchars(
                                        $productName
                                    );
                                    ?>

                                </h2>


                                <!-- Product Price -->

                                <div class="cart-product-price">


                                    <?php if (
                                        $discount > 0
                                    ): ?>


                                        <span class="cart-original-price">

                                            <?php
                                            echo number_format(
                                                $originalPrice,
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                            đ

                                        </span>


                                        <span class="cart-sale-price">

                                            <?php
                                            echo number_format(
                                                $salePrice,
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                            đ

                                        </span>


                                    <?php else: ?>


                                        <span class="cart-normal-price">

                                            <?php
                                            echo number_format(
                                                $originalPrice,
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                            đ

                                        </span>


                                    <?php endif; ?>


                                </div>


                                <!-- Quantity -->

                                <div class="quantity-control">


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
                                            value="<?php
                                                    echo $id;
                                                    ?>">


                                        <button
                                            type="submit"
                                            class="quantity-button"
                                            aria-label="Giảm số lượng">
                                            −
                                        </button>

                                    </form>


                                    <span class="quantity">

                                        <?php
                                        echo $quantity;
                                        ?>

                                    </span>


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
                                            value="<?php
                                                    echo $id;
                                                    ?>">


                                        <button
                                            type="submit"
                                            class="quantity-button"
                                            aria-label="Tăng số lượng">
                                            +
                                        </button>

                                    </form>

                                </div>

                            </div>


                            <!-- Right -->

                            <div class="cart-right">


                                <?php if (
                                    $discount > 0
                                ): ?>

                                    <p class="original-subtotal">

                                        <?php
                                        echo number_format(
                                            $originalSubtotal,
                                            0,
                                            ",",
                                            "."
                                        );
                                        ?>

                                        đ

                                    </p>

                                <?php endif; ?>


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
                                        value="<?php
                                                echo $id;
                                                ?>">


                                    <button
                                        type="submit"
                                        class="remove-button">
                                        Xóa
                                    </button>

                                </form>

                            </div>

                        </article>


                    <?php endforeach; ?>


                </section>


                <!-- =========================
                 Cart Summary
            ========================= -->

                <aside class="cart-summary">


                    <h2>
                        Tổng giỏ hàng
                    </h2>


                    <div class="summary-row">

                        <span>
                            Tổng sản phẩm
                        </span>

                        <span>
                            <?php
                            echo $cartCount;
                            ?>
                        </span>

                    </div>


                    <div class="summary-row">

                        <span>
                            Tổng giá gốc
                        </span>

                        <span>

                            <?php
                            echo number_format(
                                $totalOriginalPrice,
                                0,
                                ",",
                                "."
                            );
                            ?>

                            đ

                        </span>

                    </div>


                    <?php if (
                        $totalDiscountAmount > 0
                    ): ?>

                        <div class="summary-row discount-row">

                            <span>
                                Khuyến mãi
                            </span>

                            <span>

                                -<?php
                                    echo number_format(
                                        $totalDiscountAmount,
                                        0,
                                        ",",
                                        "."
                                    );
                                    ?>

                                đ

                            </span>

                        </div>

                    <?php endif; ?>


                    <div class="summary-row total">

                        <span>
                            Tổng thanh toán
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