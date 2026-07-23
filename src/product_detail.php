<?php

session_start();


// =========================
// Kiểm tra đăng nhập
// =========================

if (
    !isset(
        $_SESSION["username"]
    )
) {

    header(
        "Location: index.php"
    );

    exit;
}


// =========================
// Load danh sách sản phẩm
// =========================

$allProducts =
    require __DIR__
    . "/data/products_data.php";


// =========================
// Lấy product id
// =========================

$productId =
    isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;


// =========================
// Tìm sản phẩm
// =========================

$product = null;


foreach (
    $allProducts
    as $item
) {

    if (
        $item["id"]
        ===
        $productId
    ) {

        $product =
            $item;

        break;
    }
}


// =========================
// Không tìm thấy
// =========================

if (
    $product === null
) {

    http_response_code(404);

    echo
    "Không tìm thấy sản phẩm.";

    exit;
}


// =========================
// Cart count
// =========================

$cartCount = 0;


if (
    isset(
        $_SESSION["cart"]
    )
) {

    foreach (
        $_SESSION["cart"]
        as $item
    ) {

        $cartCount +=
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

    <title>

        <?php
        echo htmlspecialchars(
            $product["name"]
        );
        ?>

    </title>

    <link
        rel="stylesheet"
        href="./css/product_detail.css?v=1">

</head>


<body>


    <header class="header">

        <div class="header-container">

            <a
                href="products.php"
                class="logo">

                MyShop

            </a>


            <nav class="nav">

                <a href="products.php">

                    Sản phẩm

                </a>


                <a href="cart.php">

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



    <main class="product-detail-container">


        <a
            href="products.php"
            class="back-link">

            ← Quay lại danh sách sản phẩm

        </a>


        <div class="product-detail-card">


            <div class="detail-image">

                <img

                    src="<?php
                            echo htmlspecialchars(
                                $product["image"]
                            );
                            ?>"

                    alt="<?php
                            echo htmlspecialchars(
                                $product["name"]
                            );
                            ?>">

            </div>



            <div class="detail-content">


                <span class="detail-category">

                    <?php
                    echo htmlspecialchars(
                        $product["category"]
                    );
                    ?>

                </span>


                <h1>

                    <?php
                    echo htmlspecialchars(
                        $product["name"]
                    );
                    ?>

                </h1>


                <p class="detail-description">

                    <?php
                    echo htmlspecialchars(
                        $product["description"]
                    );
                    ?>

                </p>


                <div class="detail-price">

                    <?php

                    echo number_format(

                        $product["price"],

                        0,

                        ",",

                        "."

                    );

                    ?>

                    đ

                </div>


                <div class="product-info">

                    <div>

                        <span>
                            Mã sản phẩm
                        </span>

                        <strong>

                            SP-<?php
                                echo $product["id"];
                                ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            Danh mục
                        </span>

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $product["category"]
                            );
                            ?>

                        </strong>

                    </div>


                    <div>

                        <span>
                            Trạng thái
                        </span>

                        <strong class="in-stock">

                            Còn hàng

                        </strong>

                    </div>

                </div>


                <form
                    class="add-to-cart-form"
                    action="cart.php"
                    method="post">

                    <input
                        type="hidden"
                        name="action"
                        value="add">


                    <input
                        type="hidden"
                        name="product_id"
                        value="<?php
                                echo $product["id"];
                                ?>">


                    <button type="submit">

                        🛒 Thêm vào giỏ hàng

                    </button>

                </form>


            </div>

        </div>

    </main>


</body>

</html>