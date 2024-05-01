<?php
class ProductController
{
    // Hiển thị danh sách sản phẩm
    function index($category_id = null, $priceRange = null)
    {
        // Lấy tất cả các danh mục
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();

        //Lấy sản phẩm
        $productRepository = new ProductRepository();

        $conds = [];
        $sorts = [];
        $page = $_GET['page'] ?? 1; //trang đầu
        $item_per_page = 10; //10 sản phẩm trên 1 trang

        // Tìm sản phẩm theo danh mục

        $categoryName = 'Tất cả sản phẩm';
        if ($category_id) {
            $conds = [
                'category_id' => [
                    'type' => '=',
                    'val' => $category_id
                ]
            ];
            $category = $categoryRepository->find($category_id);
            $categoryName = $category->getName();
        }
        // Tìm sản phẩm theo khoảng giá

        if ($priceRange) {
            $temp = explode('-', $priceRange);
            $startPrice = $temp[0];
            $endPrice = $temp[1];
            $conds = [
                'sale_price' => [
                    'type' => 'BETWEEN',
                    'val' => "$startPrice AND $endPrice"
                ]
            ];
            if ($endPrice == 'greater') {
                $conds = [
                    'sale_price' => [
                        'type' => '>=',
                        'val' => $startPrice
                    ]
                ];
                // SELECT * FROM view_product WHERE sale_price >= 1000000
            }

            // SELECT * FROM view_product WHERE sale_price BETWEEN 300000 AND 500000
        }

        // Sắp xếp sản phẩm theo các tiêu chí: giá tăng/giảm, sắp xếp theo aphabet, cũ -> mới hoặc mới -> cũ
        $sort = $_GET['sort'] ?? '';
        if ($sort) {
            $temp = explode('-', $sort);
            $col = $temp[0];
            $map = ['price' => 'sale_price', 'alpha' => 'name', 'created' => 'created_date'];
            $colName = $map[$col];
            $order = $temp[1];
            $sorts = [$colName => $order];
            // SELECT * FROM view_product ORDER BY name ASC
        }

        $search = $_GET['search'] ?? null;
        if ($search) {
            $conds = [
                'name' => [
                    'type' => 'LIKE',
                    'val' => "'%$search%'"
                ]
            ];
            //SELECT * FROM view_product WHERE name LIKE '%kem%'
        }

        $products = $productRepository->getBy($conds, $sorts, $page, $item_per_page);
        $totalProducts = $productRepository->getBy($conds, $sorts);
        $totalPage = ceil(count($totalProducts) / $item_per_page);

        require ABSPATH_SITE .  'view/product/index.php';
    }

    function detail($id)
    {
        // 
        // Lấy tất cả các danh mục
        $categoryRepository = new CategoryRepository();
        $categories = $categoryRepository->getAll();


        $productRepository = new ProductRepository();
        $product = $productRepository->find($id);
        $category_id = $product->getCategoryId();

        // lấy category name
        $category = $categoryRepository->find($category_id);
        $categoryName = $category->getName();

        $conds = [
            'category_id' => [
                'type' => '=',
                'val' => $category_id
            ],
            'id' => [
                'type' => '!=',
                'val' => $id
            ]
        ];

        // SELECT * FROM view_product WHERE category_id=2 AND id != 5
        $relatedProducts = $productRepository->getBy($conds);
        require ABSPATH_SITE .  'view/product/detail.php';
    }

    function ajaxSearch()
    {
        $search = $_GET['pattern'];
        $conds = [];
        if ($search) {
            $conds = [
                'name' => [
                    'type' => 'LIKE',
                    'val' => "'%$search%'"
                ]
            ];
            //SELECT * FROM view_product WHERE name LIKE '%kem%'
        }
        $productRepository = new ProductRepository();
        $products = $productRepository->getBy($conds);
        require ABSPATH_SITE .  'view/product/ajaxSearch.php';
    }

    function storeComment()
    {
        $product_id = $_POST['product_id'];
        $rating = $_POST['rating'];
        $fullname = $_POST['fullname'];
        $email = $_POST['email'];
        $description = $_POST['description'];
        $commentRepository = new CommentRepository();
        $data = [
            'product_id' => $product_id,
            'star' => $rating,
            'fullname' => $fullname,
            'email' => $email,
            'description' => $description,
            'created_date' => date('Y-m-d H:i:s')
        ];
        $productRepository = new ProductRepository();
        // $product cần cho file commentList.php
        $product = $productRepository->find($product_id);
        $commentRepository->save($data);
        require ABSPATH_SITE .  'view/product/commentList.php';
    }
}
