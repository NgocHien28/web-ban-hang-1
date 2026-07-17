<?php
session_start();

// Xóa toàn bộ dữ liệu session
session_unset();

// Hủy session
session_destroy();

// Chuyển về trang đăng nhập
header("Location: index.php");
exit;
