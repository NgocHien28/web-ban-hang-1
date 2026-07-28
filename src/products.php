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
// Nhận dữ liệu filter
// =========================

$search =
    trim(
        $_GET["search"]
            ?? ""
    );


$category =
    trim(
        $_GET["category"]
            ?? ""
    );


$minPrice =
    isset($_GET["min_price"])
    && $_GET["min_price"] !== ""
    ? max(
        0,
        (int) $_GET["min_price"]
    )
    : 0;


$maxPrice =
    isset($_GET["max_price"])
    && $_GET["max_price"] !== ""
    ? max(
        0,
        (int) $_GET["max_price"]
    )
    : 0;


// Nếu giá từ lớn hơn giá đến,
// đổi vị trí hai giá trị

if (
    $minPrice > 0
    &&
    $maxPrice > 0
    &&
    $minPrice > $maxPrice
) {

    $temporaryPrice =
        $minPrice;

    $minPrice =
        $maxPrice;

    $maxPrice =
        $temporaryPrice;
}


// =========================
// Lấy danh sách category
// =========================

$categories = [];

$categoryCounts = [];


foreach ($allProducts as $product) {

    $productCategory =
        (string) (
            $product["category"]
            ?? ""
        );


    if ($productCategory === "") {
        continue;
    }


    if (
        !in_array(
            $productCategory,
            $categories,
            true
        )
    ) {

        $categories[] =
            $productCategory;
    }


    if (
        !isset(
            $categoryCounts[$productCategory]
        )
    ) {

        $categoryCounts[$productCategory] = 0;
    }


    $categoryCounts[$productCategory]++;
}


sort($categories);


// =========================
// Filter sản phẩm
// =========================

$filteredProducts =
    array_filter(

        $allProducts,

        function ($product)
        use (
            $search,
            $category,
            $minPrice,
            $maxPrice
        ) {

            $productName =
                (string) (
                    $product["name"]
                    ?? ""
                );


            $productDescription =
                (string) (
                    $product["description"]
                    ?? ""
                );


            $productCategory =
                (string) (
                    $product["category"]
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


            // Giới hạn phần trăm giảm từ 0 đến 100

            $discount =
                max(
                    0,
                    min(
                        100,
                        $discount
                    )
                );


            // Tính giá sau khuyến mãi

            $productPrice =
                $originalPrice;


            if ($discount > 0) {

                $productPrice =
                    (int) round(
                        $originalPrice
                            -
                            (
                                $originalPrice
                                *
                                $discount
                                /
                                100
                            )
                    );
            }


            // =========================
            // Search
            // =========================

            if ($search !== "") {

                $searchName =
                    stripos(
                        $productName,
                        $search
                    ) !== false;


                $searchDescription =
                    stripos(
                        $productDescription,
                        $search
                    ) !== false;


                if (
                    !$searchName
                    &&
                    !$searchDescription
                ) {

                    return false;
                }
            }


            // =========================
            // Category
            // =========================

            if (
                $category !== ""
                &&
                $productCategory
                !==
                $category
            ) {

                return false;
            }


            // =========================
            // Min Price
            // =========================

            if (
                $minPrice > 0
                &&
                $productPrice
                <
                $minPrice
            ) {

                return false;
            }


            // =========================
            // Max Price
            // =========================

            if (
                $maxPrice > 0
                &&
                $productPrice
                >
                $maxPrice
            ) {

                return false;
            }


            return true;
        }
    );


// Reset array index

$filteredProducts =
    array_values(
        $filteredProducts
    );


// =========================
// Pagination
// =========================

$productsPerPage = 12;


$totalProducts =
    count(
        $filteredProducts
    );


$totalPages =
    max(
        1,
        (int) ceil(
            $totalProducts
                /
                $productsPerPage
        )
    );


$currentPage =
    isset($_GET["page"])
    ? (int) $_GET["page"]
    : 1;


$currentPage =
    max(
        1,
        min(
            $currentPage,
            $totalPages
        )
    );


$offset =
    ($currentPage - 1)
    *
    $productsPerPage;


$products =
    array_slice(
        $filteredProducts,
        $offset,
        $productsPerPage
    );


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
// Query dùng cho pagination
// =========================

$baseQuery = [

    "search" =>
    $search,

    "category" =>
    $category,

    "min_price" =>
    $minPrice > 0
        ? $minPrice
        : "",

    "max_price" =>
    $maxPrice > 0
        ? $maxPrice
        : ""
];

?>

<!DOCTYPE html>

<html lang="vi">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Danh sách sản phẩm
    </title>

    <link
        rel="stylesheet"
        href="./css/products.css?v=11">

</head>


<body>


    <!-- =========================
     Success Message
========================= -->

    <div
        id="success-message"
        class="success-message">
    </div>


    <!-- =========================
     Header
========================= -->

    <header class="header">

        <div class="header-container">


            <!-- Logo -->

            <a
                href="products.php"
                class="logo">
                MyShop
            </a>


            <!-- Search -->

            <form
                class="header-search"
                action="products.php"
                method="get">

                <input
                    type="text"
                    name="search"
                    placeholder="Tìm kiếm sản phẩm..."
                    value="<?php
                            echo htmlspecialchars(
                                $search
                            );
                            ?>">


                <input
                    type="hidden"
                    name="category"
                    value="<?php
                            echo htmlspecialchars(
                                $category
                            );
                            ?>">


                <input
                    type="hidden"
                    name="min_price"
                    value="<?php
                            echo
                            $minPrice > 0
                                ? $minPrice
                                : "";
                            ?>">


                <input
                    type="hidden"
                    name="max_price"
                    value="<?php
                            echo
                            $maxPrice > 0
                                ? $maxPrice
                                : "";
                            ?>">


                <button
                    type="submit"
                    aria-label="Tìm kiếm">
                    🔍
                </button>

            </form>


            <!-- Navigation -->

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


    <!-- =========================
     Page Layout
========================= -->

    <div class="page-layout">


        <!-- =========================
         Sidebar
    ========================= -->

        <aside class="sidebar">

            <form
                action="products.php"
                method="get"
                class="sidebar-filter-form">


                <!-- Category -->

                <div class="sidebar-section">

                    <h3>
                        DANH MỤC
                    </h3>


                    <div class="category-list">


                        <!-- Tất cả sản phẩm -->

                        <label
                            class="<?php
                                    echo
                                    $category === ""
                                        ? "selected"
                                        : "";
                                    ?>">

                            <input
                                type="radio"
                                name="category"
                                value=""
                                <?php
                                echo
                                $category === ""
                                    ? "checked"
                                    : "";
                                ?>>


                            <span>
                                Tất cả sản phẩm
                            </span>


                            <span class="category-count">

                                <?php
                                echo count(
                                    $allProducts
                                );
                                ?>

                            </span>

                        </label>


                        <!-- Category List -->

                        <?php
                        foreach (
                            $categories
                            as $categoryItem
                        ):
                        ?>

                            <label
                                class="<?php
                                        echo
                                        $category
                                            ===
                                            $categoryItem
                                            ? "selected"
                                            : "";
                                        ?>">

                                <input
                                    type="radio"
                                    name="category"
                                    value="<?php
                                            echo htmlspecialchars(
                                                $categoryItem
                                            );
                                            ?>"
                                    <?php
                                    echo
                                    $category
                                        ===
                                        $categoryItem
                                        ? "checked"
                                        : "";
                                    ?>>


                                <span>

                                    <?php
                                    echo htmlspecialchars(
                                        $categoryItem
                                    );
                                    ?>

                                </span>


                                <span class="category-count">

                                    <?php
                                    echo
                                    $categoryCounts[$categoryItem]
                                        ?? 0;
                                    ?>

                                </span>

                            </label>

                        <?php endforeach; ?>

                    </div>

                </div>


                <!-- Price Filter -->

                <div class="sidebar-section">

                    <h3>
                        KHOẢNG GIÁ
                    </h3>


                    <div class="price-fields">

                        <div>

                            <label for="min-price">
                                Từ
                            </label>

                            <input
                                type="number"
                                id="min-price"
                                name="min_price"
                                min="0"
                                placeholder="0"
                                value="<?php
                                        echo
                                        $minPrice > 0
                                            ? $minPrice
                                            : "";
                                        ?>">

                        </div>


                        <div>

                            <label for="max-price">
                                Đến
                            </label>

                            <input
                                type="number"
                                id="max-price"
                                name="max_price"
                                min="0"
                                placeholder="50000000"
                                value="<?php
                                        echo
                                        $maxPrice > 0
                                            ? $maxPrice
                                            : "";
                                        ?>">

                        </div>

                    </div>

                </div>


                <!-- Giữ search khi filter -->

                <input
                    type="hidden"
                    name="search"
                    value="<?php
                            echo htmlspecialchars(
                                $search
                            );
                            ?>">


                <!-- Filter Actions -->

                <div class="sidebar-actions">

                    <button
                        type="submit"
                        class="apply-filter-button">
                        Áp dụng bộ lọc
                    </button>


                    <a
                        href="products.php"
                        class="clear-filter-button">
                        Xóa bộ lọc
                    </a>

                </div>

            </form>

        </aside>


        <!-- =========================
         Product Main
    ========================= -->

        <main class="product-main">


            <!-- Product Header -->

            <div class="product-list-header">

                <div>

                    <h1>

                        <?php

                        if ($category !== "") {

                            echo htmlspecialchars(
                                $category
                            );
                        } else {

                            echo "Tất cả sản phẩm";
                        }

                        ?>

                    </h1>


                    <p>

                        Hiển thị

                        <?php

                        if ($totalProducts > 0) {

                            echo ($offset + 1)
                                .
                                " - "
                                .
                                min(
                                    $offset
                                        +
                                        $productsPerPage,
                                    $totalProducts
                                );
                        } else {

                            echo "0";
                        }

                        ?>

                        của

                        <?php echo $totalProducts; ?>

                        sản phẩm

                    </p>

                </div>

            </div>


            <!-- Product Grid -->

            <section class="product-grid">


                <?php if (
                    count($products) > 0
                ): ?>


                    <?php
                    foreach (
                        $products
                        as $product
                    ):
                    ?>


                        <?php

                        $productId =
                            (int) (
                                $product["id"]
                                ?? 0
                            );


                        $productName =
                            (string) (
                                $product["name"]
                                ?? "Sản phẩm"
                            );


                        $productCategory =
                            (string) (
                                $product["category"]
                                ?? ""
                            );


                        $productDescription =
                            (string) (
                                $product["description"]
                                ?? ""
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
                            $originalPrice;


                        if ($discount > 0) {

                            $salePrice =
                                (int) round(
                                    $originalPrice
                                        -
                                        (
                                            $originalPrice
                                            *
                                            $discount
                                            /
                                            100
                                        )
                                );
                        }

                        ?>


                        <article
                            class="product-card"
                            tabindex="0"
                            role="link"
                            data-detail-url="product_detail.php?id=<?php
                                                                    echo $productId;
                                                                    ?>">


                            <!-- Product Image -->

                            <div class="product-image">


                                <!-- Sale Badge -->

                                <?php if ($discount > 0): ?>

                                    <span class="sale-badge">

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
                                    loading="lazy"
                                    onerror="
                                    this.onerror = null;
                                    this.src =
                                    'https://placehold.co/600x400/f3f4f6/64748b?text=Khong+co+hinh';
                                ">


                                <!-- Hover Overlay -->

                                <div class="product-hover-overlay">

                                    <span>
                                        Xem chi tiết
                                    </span>

                                </div>

                            </div>


                            <!-- Product Content -->

                            <div class="product-content">


                                <span class="product-category">

                                    <?php
                                    echo htmlspecialchars(
                                        $productCategory
                                    );
                                    ?>

                                </span>


                                <h2>

                                    <?php
                                    echo htmlspecialchars(
                                        $productName
                                    );
                                    ?>

                                </h2>


                                <p class="description">

                                    <?php
                                    echo htmlspecialchars(
                                        $productDescription
                                    );
                                    ?>

                                </p>


                                <!-- Product Price -->

                                <div class="product-price">


                                    <?php if ($discount > 0): ?>


                                        <p class="original-price">

                                            <?php
                                            echo number_format(
                                                $originalPrice,
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                            đ

                                        </p>


                                        <p class="sale-price">

                                            <?php
                                            echo number_format(
                                                $salePrice,
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                            đ

                                        </p>


                                    <?php else: ?>


                                        <p class="normal-price">

                                            <?php
                                            echo number_format(
                                                $originalPrice,
                                                0,
                                                ",",
                                                "."
                                            );
                                            ?>

                                            đ

                                        </p>


                                    <?php endif; ?>


                                </div>


                                <!-- Add To Cart -->

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
                                                echo $productId;
                                                ?>">


                                    <button type="submit">

                                        🛒 Thêm vào giỏ hàng

                                    </button>

                                </form>

                            </div>

                        </article>

                    <?php endforeach; ?>


                <?php else: ?>


                    <div class="no-products">

                        <div class="no-products-icon">
                            🔍
                        </div>

                        <h2>
                            Không tìm thấy sản phẩm
                        </h2>

                        <p>
                            Vui lòng thử thay đổi điều kiện tìm kiếm hoặc bộ lọc.
                        </p>

                    </div>


                <?php endif; ?>


            </section>


            <!-- Pagination -->

            <?php if ($totalPages > 1): ?>


                <nav
                    class="pagination"
                    aria-label="Phân trang">


                    <!-- Previous -->

                    <?php if ($currentPage > 1): ?>


                        <?php

                        $previousQuery =
                            $baseQuery;

                        $previousQuery["page"] =
                            $currentPage - 1;

                        ?>


                        <a
                            href="products.php?<?php
                                                echo http_build_query(
                                                    $previousQuery
                                                );
                                                ?>"
                            class="pagination-nav"
                            title="Trang trước">
                            ‹
                        </a>


                    <?php else: ?>


                        <span
                            class="pagination-nav disabled">
                            ‹
                        </span>


                    <?php endif; ?>


                    <?php

                    $startPage =
                        max(
                            1,
                            $currentPage - 2
                        );


                    $endPage =
                        min(
                            $totalPages,
                            $currentPage + 2
                        );

                    ?>


                    <!-- First Page -->

                    <?php if ($startPage > 1): ?>


                        <?php

                        $firstQuery =
                            $baseQuery;

                        $firstQuery["page"] = 1;

                        ?>


                        <a
                            href="products.php?<?php
                                                echo http_build_query(
                                                    $firstQuery
                                                );
                                                ?>">
                            1
                        </a>


                        <?php if ($startPage > 2): ?>

                            <span class="pagination-dots">
                                ...
                            </span>

                        <?php endif; ?>


                    <?php endif; ?>


                    <!-- Page Numbers -->

                    <?php

                    for (
                        $page = $startPage;
                        $page <= $endPage;
                        $page++
                    ):

                        $pageQuery =
                            $baseQuery;

                        $pageQuery["page"] =
                            $page;

                    ?>


                        <a
                            href="products.php?<?php
                                                echo http_build_query(
                                                    $pageQuery
                                                );
                                                ?>"
                            class="<?php
                                    echo
                                    $page
                                        ===
                                        $currentPage
                                        ? "active"
                                        : "";
                                    ?>">

                            <?php echo $page; ?>

                        </a>


                    <?php endfor; ?>


                    <!-- Last Page -->

                    <?php if (
                        $endPage
                        <
                        $totalPages
                    ): ?>


                        <?php if (
                            $endPage
                            <
                            $totalPages - 1
                        ): ?>

                            <span class="pagination-dots">
                                ...
                            </span>

                        <?php endif; ?>


                        <?php

                        $lastQuery =
                            $baseQuery;

                        $lastQuery["page"] =
                            $totalPages;

                        ?>


                        <a
                            href="products.php?<?php
                                                echo http_build_query(
                                                    $lastQuery
                                                );
                                                ?>">
                            <?php echo $totalPages; ?>
                        </a>


                    <?php endif; ?>


                    <!-- Next -->

                    <?php if (
                        $currentPage
                        <
                        $totalPages
                    ): ?>


                        <?php

                        $nextQuery =
                            $baseQuery;

                        $nextQuery["page"] =
                            $currentPage + 1;

                        ?>


                        <a
                            href="products.php?<?php
                                                echo http_build_query(
                                                    $nextQuery
                                                );
                                                ?>"
                            class="pagination-nav"
                            title="Trang sau">
                            ›
                        </a>


                    <?php else: ?>


                        <span
                            class="pagination-nav disabled">
                            ›
                        </span>


                    <?php endif; ?>


                </nav>


            <?php endif; ?>


        </main>

    </div>


    <!-- =========================
     JavaScript
========================= -->

    <script>
        // =====================================
        // CATEGORY AUTO FILTER
        // =====================================

        const categoryInputs =
            document.querySelectorAll(
                '.category-list input[name="category"]'
            );


        const sidebarFilterForm =
            document.querySelector(
                ".sidebar-filter-form"
            );


        categoryInputs.forEach(
            function(categoryInput) {

                categoryInput.addEventListener(
                    "change",
                    function() {

                        if (sidebarFilterForm) {

                            sidebarFilterForm.submit();
                        }

                    }
                );

            }
        );


        // =====================================
        // PRODUCT CARD DETAIL
        // =====================================

        const productCards =
            document.querySelectorAll(
                ".product-card"
            );


        productCards.forEach(
            function(card) {

                card.addEventListener(
                    "click",
                    function(event) {

                        const clickedForm =
                            event.target.closest(
                                ".add-to-cart-form"
                            );


                        if (clickedForm) {
                            return;
                        }


                        const detailUrl =
                            card.dataset.detailUrl;


                        if (detailUrl) {

                            window.location.href =
                                detailUrl;
                        }

                    }
                );


                card.addEventListener(
                    "keydown",
                    function(event) {

                        if (
                            event.key === "Enter" ||
                            event.key === " "
                        ) {

                            event.preventDefault();


                            const detailUrl =
                                card.dataset.detailUrl;


                            if (detailUrl) {

                                window.location.href =
                                    detailUrl;
                            }

                        }

                    }
                );

            }
        );


        // =====================================
        // ADD TO CART AJAX
        // =====================================

        const forms =
            document.querySelectorAll(
                ".add-to-cart-form"
            );


        const successMessage =
            document.getElementById(
                "success-message"
            );


        const cartCountElement =
            document.getElementById(
                "cart-count"
            );


        let hideMessageTimer;


        forms.forEach(
            function(form) {

                form.addEventListener(
                    "click",
                    function(event) {

                        event.stopPropagation();

                    }
                );


                form.addEventListener(
                    "submit",
                    function(event) {

                        event.preventDefault();

                        event.stopPropagation();


                        const submitButton =
                            form.querySelector(
                                'button[type="submit"]'
                            );


                        const formData =
                            new FormData(form);


                        formData.append(
                            "ajax",
                            "1"
                        );


                        if (submitButton) {

                            submitButton.disabled =
                                true;

                            submitButton.textContent =
                                "Đang thêm...";
                        }


                        fetch(
                                "cart.php", {
                                    method: "POST",
                                    body: formData,
                                    headers: {
                                        "X-Requested-With": "XMLHttpRequest"
                                    }
                                }
                            )

                            .then(
                                function(response) {

                                    if (!response.ok) {

                                        throw new Error(
                                            "HTTP error: " +
                                            response.status
                                        );
                                    }


                                    return response.json();
                                }
                            )

                            .then(
                                function(data) {

                                    if (!data.success) {

                                        throw new Error(
                                            data.message ||
                                            "Không thể thêm sản phẩm."
                                        );
                                    }


                                    if (
                                        cartCountElement &&
                                        data.cartCount !==
                                        undefined
                                    ) {

                                        cartCountElement.textContent =
                                            data.cartCount;
                                    }


                                    if (successMessage) {

                                        successMessage.textContent =
                                            data.message ||
                                            "Đã thêm sản phẩm vào giỏ hàng.";


                                        successMessage.classList.add(
                                            "show"
                                        );


                                        clearTimeout(
                                            hideMessageTimer
                                        );


                                        hideMessageTimer =
                                            setTimeout(
                                                function() {

                                                    successMessage
                                                        .classList
                                                        .remove(
                                                            "show"
                                                        );

                                                },
                                                2500
                                            );
                                    }

                                }
                            )

                            .catch(
                                function(error) {

                                    console.error(
                                        "Add cart error:",
                                        error
                                    );


                                    if (successMessage) {

                                        successMessage.textContent =
                                            "Có lỗi xảy ra. Vui lòng thử lại.";


                                        successMessage.classList.add(
                                            "show"
                                        );


                                        clearTimeout(
                                            hideMessageTimer
                                        );


                                        hideMessageTimer =
                                            setTimeout(
                                                function() {

                                                    successMessage
                                                        .classList
                                                        .remove(
                                                            "show"
                                                        );

                                                },
                                                2500
                                            );
                                    }

                                }
                            )

                            .finally(
                                function() {

                                    if (submitButton) {

                                        submitButton.disabled =
                                            false;

                                        submitButton.textContent =
                                            "🛒 Thêm vào giỏ hàng";
                                    }

                                }
                            );

                    }
                );

            }
        );


        // =====================================
        // PRODUCT SCROLL ANIMATION
        // =====================================

        const productMain =
            document.querySelector(
                ".product-main"
            );


        let lastScrollTop =
            productMain ?
            productMain.scrollTop :
            0;


        let scrollDirection =
            "down";


        if (productMain) {

            productMain.addEventListener(
                "scroll",
                function() {

                    const currentScrollTop =
                        productMain.scrollTop;


                    if (
                        currentScrollTop >
                        lastScrollTop
                    ) {

                        scrollDirection =
                            "down";

                    } else if (
                        currentScrollTop <
                        lastScrollTop
                    ) {

                        scrollDirection =
                            "up";
                    }


                    lastScrollTop =
                        Math.max(
                            currentScrollTop,
                            0
                        );

                }, {
                    passive: true
                }
            );
        }


        if (
            productMain &&
            productCards.length > 0 &&
            "IntersectionObserver" in window
        ) {

            const productObserver =
                new IntersectionObserver(

                    function(entries) {

                        entries.forEach(
                            function(entry) {

                                const card =
                                    entry.target;


                                if (
                                    entry.isIntersecting
                                ) {

                                    card.classList.remove(
                                        "scroll-up",
                                        "scroll-down"
                                    );


                                    card.classList.add(
                                        scrollDirection ===
                                        "down" ?
                                        "scroll-down" :
                                        "scroll-up"
                                    );


                                    requestAnimationFrame(
                                        function() {

                                            card.classList.add(
                                                "show-animation"
                                            );

                                        }
                                    );

                                } else {

                                    card.classList.remove(
                                        "show-animation"
                                    );
                                }

                            }
                        );

                    },

                    {
                        root: productMain,
                        threshold: 0.12,
                        rootMargin: "0px 0px -30px 0px"
                    }
                );


            productCards.forEach(
                function(card) {

                    productObserver.observe(
                        card
                    );

                }
            );

        } else {

            productCards.forEach(
                function(card) {

                    card.classList.add(
                        "show-animation"
                    );

                }
            );
        }
    </script>


</body>

</html>