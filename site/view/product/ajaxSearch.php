<?php global $router, $slugify; ?>

<ul class="list-unstyled">
    <?php foreach ($products as $product) : ?>
        <li>
            <?php
            $slug = $slugify->slugify($product->getName());
            $params = ['slug' => $slugify->slugify($product->getName()), 'id' => $product->getId()];
            $productDetailLink =  $router->generate('productDetail', $params);
            ?>
            <a class="product-name" href="<?= $productDetailLink ?>" title="<?= $product->getName() ?>">
                <img style="width:50px" src="../upload/<?= $product->getFeaturedImage() ?>" alt="">
                <?= $product->getName() ?>
            </a>
        </li>
    <?php endforeach ?>

</ul>