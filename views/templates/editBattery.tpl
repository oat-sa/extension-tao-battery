<?php
use oat\tao\helpers\Template;
?>

<header class="flex-container-full">
    <h3><?=get_data('formTitle')?></h3>
</header>

<div class="main-container flex-container-main-form">
    <div id="form-container">
        <?=get_data('form')?>
    </div>
</div>

<div class="data-container-wrapper flex-container-remainer">
    <?= get_data('deliveriesTree')?>
</div>

<div class="data-container-wrapper flex-container-remainer">
    <?= get_data('groupsTree')?>
</div>

<?php
Template::inc('footer.tpl', 'tao');
?>
