<h2>Установленные точки</h2>
<?php foreach (Shop::listAll() as $shop):?>
    <?php /* @var $shop Shop */?>
        <?php foreach ($shop->listPoints() as $point):?>
            <h3><?php echo htmlspecialchars($shop->name) ?>/<?php echo htmlspecialchars($point->name) ?></h3>
            <?php /* @var $point Point */ ?>
            <p>
                <?php foreach ($point->listImages() as $image): ?>
                    <img height="80" src="<?= $image['thumb']?>"/>
                <?php endforeach; ?>
            </p>
            <p class="postmeta">
                <?php if ($devs = $point->listDevices()):?>
                    Устройства: <?php echo htmlspecialchars(implode(', ', $point->getDevicesName()))?>
                <?php else: ?>
                    <i>нет активности</i>
                <?php endif;?>
            </p>
        <?php endforeach;?>
<?php endforeach;?>
