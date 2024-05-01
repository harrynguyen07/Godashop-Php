<?php require ABSPATH_SITE .  'layout/header.php' ?>
<main id="maincontent" class="page-main">
    <div class="container">
        <div class="row">
            <div class="col-xs-9">
                <ol class="breadcrumb">
                    <li><a href="/" target="_self">Trang chủ</a></li>
                    <li><span>/</span></li>
                    <li class="active"><span>Tài khoản</span></li>
                </ol>
            </div>
            <div class="clearfix"></div>
            <?php require ABSPATH_SITE .  'view/customer/sidebar.php' ?>
            <div class="col-md-9 order">
                <div class="row">
                    <div class="col-xs-6">
                        <h4 class="home-title">Đơn hàng của tôi</h4>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12">
                        <!-- Mỗi đơn hàng -->
                        <?php foreach ($orders as $order) : ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5>Đơn hàng <a href="/index.php?c=customer&a=orderDetail&id=<?= $order->getId() ?> "><?= $order->getId() ?></a>
                                    </h5>
                                    <span class="date">
                                        Đặt hàng ngày <?= $order->getCreatedDate() ?> </span>
                                    <hr>
                                    <?php
                                    $orderItems = $order->getOrderItems();
                                    foreach ($orderItems as $orderItem) :
                                        $product = $orderItem->getProduct();
                                    ?>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <img src="../upload/<?= $product->getFeaturedImage() ?>" alt="" class="img-responsive">
                                            </div>
                                            <div class="col-md-3">
                                                <?php
                                                $slug = $slugify->slugify($product->getName());
                                                $params = ['slug' => $slugify->slugify($product->getName()), 'id' => $product->getId()];
                                                $productDetailLink =  $router->generate('productDetail', $params);
                                                ?>
                                                <a class="product-name" href="<?= $productDetailLink ?>"><?= $product->getName() ?></a>
                                            </div>
                                            <div class="col-md-2">
                                                Số lượng: <?= $orderItem->getQty() ?>
                                            </div>
                                            <div class="col-md-2">
                                                <?= $order->getStatus()->getDescription() ?>
                                            </div>
                                            <div class="col-md-3">
                                                Giao hàng ngày <?= $order->getDeliveredDate() ?>
                                            </div>
                                        </div>
                                    <?php endforeach ?>
                                </div>
                            </div>
                        <?php endforeach ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require ABSPATH_SITE .  'layout/footer.php' ?>