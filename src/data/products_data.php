<?php

$baseProducts = [

    [
        "name" => "Laptop",
        "category" => "Laptop",
        "base_price" => 15000000,
        "description" =>
        "Laptop hiện đại, phù hợp cho học tập và làm việc.",
        "image_keyword" =>
        "laptop,computer,technology,device"
    ],

    [
        "name" => "Smartphone",
        "category" => "Điện thoại",
        "base_price" => 8000000,
        "description" =>
        "Điện thoại thông minh với thiết kế hiện đại.",
        "image_keyword" =>
        "smartphone,mobilephone,technology,device"
    ],

    [
        "name" => "Tai nghe",
        "category" => "Âm thanh",
        "base_price" => 1200000,
        "description" =>
        "Tai nghe không dây với chất lượng âm thanh tốt.",
        "image_keyword" =>
        "headphones,electronics,technology"
    ],

    [
        "name" => "Bàn phím",
        "category" => "Phụ kiện",
        "base_price" => 900000,
        "description" =>
        "Bàn phím hiện đại, phù hợp cho công việc và giải trí.",
        "image_keyword" =>
        "computerkeyboard,keyboard,electronics"
    ],

    [
        "name" => "Chuột",
        "category" => "Phụ kiện",
        "base_price" => 500000,
        "description" =>
        "Chuột không dây nhỏ gọn và tiện lợi.",
        "image_keyword" =>
        "computermouse,mouse,electronics"
    ],

    [
        "name" => "Màn hình",
        "category" => "Màn hình",
        "base_price" => 4500000,
        "description" =>
        "Màn hình sắc nét, phù hợp cho công việc và giải trí.",
        "image_keyword" =>
        "computermonitor,monitor,display,technology"
    ],

    [
        "name" => "Loa Bluetooth",
        "category" => "Âm thanh",
        "base_price" => 1500000,
        "description" =>
        "Loa Bluetooth nhỏ gọn với âm thanh sống động.",
        "image_keyword" =>
        "bluetoothspeaker,speaker,electronics"
    ],

    [
        "name" => "Webcam",
        "category" => "Phụ kiện",
        "base_price" => 1300000,
        "description" =>
        "Webcam chất lượng cao dành cho họp trực tuyến.",
        "image_keyword" =>
        "webcam,camera,electronics"
    ],

    [
        "name" => "Tablet",
        "category" => "Máy tính bảng",
        "base_price" => 7000000,
        "description" =>
        "Máy tính bảng tiện lợi cho học tập và giải trí.",
        "image_keyword" =>
        "tabletcomputer,tablet,technology,device"
    ],

    [
        "name" => "Đồng hồ thông minh",
        "category" => "Thiết bị đeo",
        "base_price" => 3000000,
        "description" =>
        "Đồng hồ thông minh theo dõi hoạt động hàng ngày.",
        "image_keyword" =>
        "smartwatch,digitalwatch,technology,device"
    ]

];


$products = [];

$id = 1;


// =====================================
// Tạo 100 sản phẩm
// 10 loại sản phẩm x 10 phiên bản
// =====================================

for (
    $version = 1;
    $version <= 10;
    $version++
) {

    foreach (
        $baseProducts
        as $baseProduct
    ) {

        $products[] = [

            "id" => $id,


            "name" =>
            $baseProduct["name"]
                . " "
                . $version,


            "category" =>
            $baseProduct["category"],


            "price" =>
            $baseProduct["base_price"]
                +
                (
                    ($version - 1)
                    *
                    200000
                ),


            "description" =>
            $baseProduct["description"],


            // =============================
            // Online product image
            // =============================

            "image" =>
            "https://loremflickr.com/600/400/"
                . $baseProduct["image_keyword"]
                . "?lock="
                . $id

        ];


        $id++;
    }
}


return $products;
