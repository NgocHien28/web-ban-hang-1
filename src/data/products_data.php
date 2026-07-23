<?php

$baseProducts = [

    [
        "name" => "Laptop",
        "category" => "Laptop",
        "base_price" => 15000000,
        "description" =>
        "Laptop hiện đại, hiệu năng tốt, phù hợp cho học tập và làm việc.",

        "image" =>
        "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=800&q=80"
    ],

    [
        "name" => "Điện thoại",
        "category" => "Điện thoại",
        "base_price" => 8000000,
        "description" =>
        "Điện thoại thông minh với thiết kế hiện đại và nhiều tính năng tiện ích.",

        "image" =>
        "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80"
    ],

    [
        "name" => "Tablet",
        "category" => "Tablet",
        "base_price" => 7000000,
        "description" =>
        "Máy tính bảng tiện lợi, phù hợp cho học tập, làm việc và giải trí.",

        "image" =>
        "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=80"
    ],

    [
        "name" => "Tai nghe",
        "category" => "Tai nghe",
        "base_price" => 1200000,
        "description" =>
        "Tai nghe không dây với chất lượng âm thanh tốt và thiết kế hiện đại.",

        "image" =>
        "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80"
    ]

];


// =========================
// Khởi tạo mảng sản phẩm
// =========================

$products = [];

$id = 1;


// =========================
// Tạo 100 sản phẩm
//
// 4 loại sản phẩm
// x
// 25 phiên bản
//
// = 100 sản phẩm
// =========================

for (
    $version = 1;
    $version <= 25;
    $version++
) {

    foreach (
        $baseProducts
        as $baseProduct
    ) {

        $products[] = [

            // ID sản phẩm
            "id" =>
            $id,


            // Tên sản phẩm
            "name" =>
            $baseProduct["name"]
                . " "
                . $version,


            // Danh mục
            "category" =>
            $baseProduct["category"],


            // Giá sản phẩm
            "price" =>
            $baseProduct["base_price"]
                +
                (
                    ($version - 1)
                    *
                    200000
                ),


            // Mô tả
            "description" =>
            $baseProduct["description"],


            // Hình ảnh online
            "image" =>
            $baseProduct["image"]

        ];


        $id++;
    }
}


// =========================
// Trả về danh sách sản phẩm
// =========================

return $products;
