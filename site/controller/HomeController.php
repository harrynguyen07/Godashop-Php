<?php
class HomeController
{
    // Hiển thị trang chủ
    function index()
    {

        $productRepository = new ProductRepository();
        $conds = [];
        $page = 1;
        $item_per_page = 4;

        // Lấy 4 sản phẩm nổi bật 
        $sorts = ['featured' => 'DESC'];
        $featuredProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);

        // Lấy 4 sản phẩm mới nhất 
        $sorts = ['created_date' => 'DESC'];
        $latestProducts = $productRepository->getBy($conds, $sorts, $page, $item_per_page);

        // Lấy 4 sản phẩm theo từng danh mục
        // Biến để chứa các sản phẩm theo từng danh mục
        $categoryProducts = [];

        // Lấy tất cả các danh mục
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();
        //Duyệt từng category và lấy sản phẩm theo category đó
        foreach ($categories as $category) {
            $conds = [
                'category_id' => [
                    'type' => '=',
                    'val' => $category->getId()
                ]
            ];
            // SELECT * FROM view_product WHERE category_id=6;
            $products = $productRepository->getBy($conds, $sorts, $page, $item_per_page);

            //Dấu [] thêm phần tử vào cuối danh sách
            $categoryProducts[] = [
                'category_name' => $category->getName(),
                'products' => $products
            ];
        }


        require ABSPATH_SITE .  'view/home/index.php';
    }
}
