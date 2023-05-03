<?php
exit();
$filter_unit = '';
$filter_report = '';
$filter_order = '';

$maf = [];
$units = [];
$pr = [];
$orders = [];
$allInstallers = [];

$allUnits = "SELECT `unit_from_name` FROM `bot_photo_maf` WHERE `del`=0 AND `order_id` IS NULL GROUP BY `unit_from_name`";
$searchUnits = new DB();
$searchAllUnits = $searchUnits->request($allUnits);


if(isset($_GET['filter_unit']))
{
    $filter_unit = trim($_GET['filter_unit']);
}
if(isset($_GET['filter_report']))
{
    $filter_report = trim($_GET['filter_report']);
}
if(isset($_GET['filter_order']))
{
    $filter_order = trim($_GET['filter_order']);
}

$requestOrders = "SELECT COUNT(*) cnt, `order_id`, `maf_id`,`unit_code`,`name`,`path` 
    FROM `bot_photo_maf` 
    WHERE `del`=0 AND `order_id` > 0";
if(!empty($filter_unit))
{
    $requestOrders .=  " AND `unit_from_name`='".$filter_unit."'";
}
if(!empty($filter_report))
{
    $requestOrders .=  " AND `name` LIKE '%".$filter_report."%'";
}
if(!empty($filter_order))
{
    $requestOrders .=  " AND `order_id`='".$filter_order."'";
}
$requestOrders .=  " GROUP BY `maf_id` LIMIT 0,500";
$searchInstallers = new DB();
$installers = $searchInstallers->request($requestOrders);
if($installers['count'])
{
    foreach ($installers['results'] as $i)
    {
        $orders[$i['order_id']][$i['maf_id']]['name'] = $i['name'];
        $orders[$i['order_id']][$i['maf_id']]['cnt'] = $i['cnt'];
    }
}



$requestUnit = "SELECT COUNT(*) cnt, `unit_from_name`, `maf_id`,`unit_code`,`name` ,`path` 
FROM `bot_photo_maf` WHERE `del`=0 AND `order_id` IS NULL";
if(!empty($filter_unit))
{
    $requestUnit .=  " AND `unit_from_name`='".$filter_unit."'";
}
if(!empty($filter_report))
{
    $requestUnit .=  " AND `name` LIKE '%".$filter_report."%'";
}
if(!empty($filter_order))
{
    $requestUnit .=  " AND `order_id`='".$filter_order."'";
}
$requestUnit .= " GROUP BY `maf_id` LIMIT 0,500";
$searchInstallers = new DB();
$installers = $searchInstallers->request($requestUnit);
if($installers['count'])
{
    foreach ($installers['results'] as $i)
    {
        $units[$i['unit_from_name']][$i['maf_id']]['name'] = $i['name'];
        $units[$i['unit_from_name']][$i['maf_id']]['cnt'] = $i['cnt'];
    }
}
?>
<div id="block_filter" class="fixed-top bg-white">
    <form class="d-flex" action="/s/gallery/" method="get">
        <div class="container-fluid-first border-0 pe-2">
            <div id="helpBlock" class="form-text">Номер заказа</div>
            <input value="<?=$filter_order?>" name="filter_order" class="form-control" type="search" style="width: 220px;" aria-describedby="helpBlock">

        </div>
        <div class="container-fluid-first border-0 pe-2 mb-1">
            <div id="helpBlock" class="form-text">Номер заявки</div>
            <input value="<?=$filter_report?>" name="filter_report" class="form-control" type="search" style="width: 220px;" aria-describedby="helpBlock">
        </div>
        <div class="container-fluid-first border-0 pe-2 mb-1">
            <div id="helpBlock" class="form-text">Подразделение</div>
            <select name="filter_unit" class="form-control form-select form-select-sm" style="width: 220px;" aria-describedby="helpBlock">
                <option></option>
                <?foreach ($searchAllUnits['results'] as $unit_name):?>
                    <option <?if($unit_name['unit_from_name'] == $filter_unit) echo "selected";?> value="<?=$unit_name['unit_from_name']?>"><?=$unit_name['unit_from_name']?></option>
                <?endforeach;?>
            </select>
        </div>
        <div class="ontainer-fluid-first border-0 pe-2 mb-1">
            <div id="searchHelpBlock" class="form-text">&nbsp;</div>
            <button class="btn btn-primary" type="submit" aria-describedby="searchHelpBlock"><i class="bi bi-search color-white"></i> Искать</button>
            <a class="text-decoration-none text-nowrap ps-2" role="button" href="/s/gallery/"><i class="bi bi-x-lg color-red"></i>&nbsp;<span class="color-red text-decoration-none fw-bold">Сбросить</span></a>
        </div>
    </form>
</div>

<br>

<div class="row">
    <?foreach ($orders as $o_id=>$o):?>
        <div class="col mb-3 me-3">
                <div class="fs-5 fw-bold text-nowrap">Заказ <?=$o_id?></div>
                <?foreach ($o as $id=>$name):
                    /*$mafInfo = file_get_contents("https://lebergroup.ru/ya/photobotmaf.php?token=dhfgajO99UjkklYUTbj9&id=".$id);
                        if(!empty($mafInfo)):
                            $mafInfo = json_decode($mafInfo, true);?>
                            <div>Заявка <a href="/s/gallery/?maf_id=<?=$id?>"><?=$mafInfo['NAME']?></a> от <?=setDateFormat($mafInfo['DATE_CREATE'])?></div>
                        <?endif;*/?>
                    <div class="ms-2 text-nowrap">Заявка <a href="/s/gallery/?maf_id=<?=$id?>"><?=$name['name']?></a><?/* [<?=$name['cnt']?>]*/?></div>
                <?endforeach;?>
        </div>
    <?endforeach;?>
    <div class="col mb-3 me-3"></div>
    <div class="col mb-3 me-3"></div>
    <div class="col mb-3 me-3"></div>
    <div class="col mb-3 me-3"></div>
    <div class="col mb-3 me-3"></div>
    <div class="col mb-3 me-3"></div>
    <div class="col mb-3 me-3"></div>
</div>
<br>
<div class="row">
<?foreach ($units as $unit_name=>$o):?>
        <div class="col">
            <div class="fs-5 fw-bold"><?=$unit_name?></div>
            <?foreach ($o as $id=>$name):?>
                <div class="ms-2">Заявка <a href="/s/gallery/?maf_id=<?=$id?>"><?=$name['name']?></a><?/* [<?=$name['cnt']?>]*/?></div>
            <?endforeach;?>
        </div>
    <?endforeach;?>

    <?/*
<div class="col">
<div class="fs-5 fw-bold">Монтаж МАФ</div>
<br>
<?foreach ($maf['maf'] as $maf_id=>$o):?>
    <div>Заявка <a href="/s/passportsGenerator/product/ingallery.php?maf_id=<?=$maf_id?>">МАФ-<?=$maf_id?></a> </div>
<?endforeach;?>
<br>
</div>
<div class="col">
<div class="fs-5 fw-bold">Производство</div>
<br>
<?foreach ($maf['pr'] as $pr_id=>$o):?>
    <div>Заявка <a href="/s/passportsGenerator/product/ingallery.php?maf_id=<?=$pr_id?>">ПР-<?=$pr_id?></a> </div>
<?endforeach;?>
<br>
</div>*/?>
    <?/*foreach ($allInstallers as $maf_id=>$images):
$mafInfo = file_get_contents("https://lebergroup.ru/ya/photobotmaf.php?token=dhfgajO99UjkklYUTbj9&id=".$maf_id);
if(!empty($mafInfo)):
    $mafInfo = json_decode($mafInfo, true);
    //pre($mafInfo);
    ?>
    <div class="col d-flex justify-content-center mb-5">

        <div class="shadow p-4 bg-body border rounded w-380">
            <div class="mb-3 overflow-hidden border" style="height: 250px;"><a href="/s/passportsGenerator/product/ingallery.php?maf_id=<?=$maf_id?>"><img src="/init/resize.php?img=<?=$images[0]['path']?>&w=380"></a></div>

            <a class="fw-bold pb-5 fs-5 color-gray text-decoration-none" href="/s/passportsGenerator/product/ingallery.php?maf_id=<?=$maf_id?>">Заявка <?=$mafInfo['NAME']?> от <?=setDateFormat($mafInfo['DATE_CREATE'])?></a>
            <br>
            <br>
            <div>Адрес: <?=$mafInfo['PROPERTY_ADDRESS_VALUE']?></div>
            <div>Заказ: <?=$mafInfo['PROPERTY_ORDER_VALUE']?></div>
            <div>Менеджер: <?=$mafInfo['MANAGER']?> </div>
        </div>
    </div>
<?endif;?>
<?endforeach;*/?>
</div>