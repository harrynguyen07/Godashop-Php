<?php
session_start();

use Cocur\Slugify\Slugify;

require 'vendor/autoload.php';
// import config & database
require 'config.php';
require ABSPATH . 'connectDB.php';

// import model
require ABSPATH . 'bootstrap.php';

// import controler
require ABSPATH_SITE . 'load.php';


$router = new AltoRouter();

$slugify = new Slugify();




// map homepage
$router->map('GET', '/', ['HomeController', 'index'], 'home');

// Trang danh sách sản phẩm
$router->map('GET', '/san-pham', ['ProductController', 'index'], 'product');


// Trang chính sách đổi trả
$router->map('GET', '/chinh-sach-doi-tra.html', ['InformationController', 'returnPolicy'], 'returnPolicy');

// Trang chính sách giao hàng
$router->map('GET', '/chinh-sach-giao-hang.html', ['InformationController', 'deliveryPolicy'], 'deliveryPolicy');

$router->map('GET', '/chinh-sach-thanh-toan.html', ['InformationController', 'paymentPolicy'], 'paymentPolicy');

// Liên hệ
$router->map('GET', '/lien-he.html', ['ContactController', 'form'], 'contact');

// /san-pham/kem-danh-rang-congate-3.html
$router->map('GET', '/san-pham/[*:slug]-[i:id].html', function ($slug, $id) {
    call_user_func_array(['ProductController', 'detail'],  [$id]);
}, 'productDetail');



// /danh mục sản phẩm
// danh-muc/kem-trang-da-3
$router->map('GET', '/danh-muc/[*:slug]-[i:categoryId].', function ($slug, $categoryId) {
    call_user_func_array(['ProductController', 'index'],  [$categoryId]);
}, 'category');


// Tìm theo khoảng giá
// khoang-gia/0-100000
$router->map('GET', '/khoang-gia/[*:priceRange]', function ($priceRange) {
    call_user_func_array(['ProductController', 'index'],  [null, $priceRange]);
}, 'priceRange');



// Tìm theo khoảng giá
// seach/0-100000
$router->map('GET', '/seach', function () {
    call_user_func_array(['ProductController', 'index'],  []);
}, 'search');


// match current request url
$match = $router->match();

// route Name
$routeName = $match['name'];


// call closure or throw 404 status
if (is_array($match) && is_callable($match['target'])) {
    call_user_func_array($match['target'], $match['params']);
} else {
    // no route was matched
    // header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');

    // Duong dan khong dep
    //godashop.com/index.php?c=..&a=..
    $c = $_GET['c'] ?? 'home';
    $a = $_GET['a'] ?? 'index';

    $controller = ucfirst($c) . 'Controller'; //StudentController

    $controller = new $controller(); //new StudentController()
    $controller->$a(); //$controller->index();
}
