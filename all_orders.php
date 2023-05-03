<?php
$filter_report = '';
$filter_order = '';

$maf = [];
$pr = [];
$orders = [];
$allInstallers = [];

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

if(!empty($filter_report))
{
    $requestOrders .=  " AND `name` LIKE '%".$filter_report."%'";
}
if(!empty($filter_order))
{
    $requestOrders .=  " AND `order_id` LIKE '%".$filter_order."%'";
}
$requestOrders .=  " GROUP BY `maf_id` ORDER BY `order_id` DESC, `maf_id` DESC LIMIT 0,500";

//pre($requestOrders);
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
?>
<div id="block_filter" class="fixed-top bg-white">
    <form class="d-flex" action="/s/gallery/" method="get">
        <input type="hidden" name="type" value="order">
        <div class="container-fluid-first border-0 pe-2">
            <div id="helpBlock" class="form-text">Номер заказа</div>
            <input value="<?=$filter_order?>" name="filter_order" class="form-control" type="search" style="width: 220px;" aria-describedby="helpBlock">

        </div>
        <div class="container-fluid-first border-0 pe-2 mb-1">
            <div id="helpBlock" class="form-text">Номер заявки</div>
            <input value="<?=$filter_report?>" name="filter_report" class="form-control" type="search" style="width: 220px;" aria-describedby="helpBlock">
        </div>
        <div class="ontainer-fluid-first border-0 pe-2 mb-1">
            <div id="searchHelpBlock" class="form-text">&nbsp;</div>
            <button class="btn btn-primary" type="submit" aria-describedby="searchHelpBlock"><i class="bi bi-search color-white"></i> Искать</button>
            <a class="text-decoration-none text-nowrap ps-2" role="button" href="/s/gallery/?type=order"><i class="bi bi-x-lg color-red"></i>&nbsp;<span class="color-red text-decoration-none fw-bold">Сбросить</span></a>
        </div>
    </form>
</div>

<br>

<div>
    <div class="d-table w-100 pb-2">
        <div class="fs-5 fw-bold d-table-cell" style="width: 100px;">Заказы</div>
        <div class="fs-5 fw-bold d-table-cell">Заявки</div>
    </div>
    <?foreach ($orders as $o_id=>$o):?>
        <div class="d-table pt-2 pb-2 border-top w-100">
            <div class="fw-bold d-table-cell" style="width: 100px;"><?=$o_id?></div>
            <div class="d-table-cell">
            <?foreach ($o as $id=>$name):
                /*$mafInfo = file_get_contents("https://lebergroup.ru/ya/photobotmaf.php?token=dhfgajO99UjkklYUTbj9&id=".$id);
                    if(!empty($mafInfo)):
                        $mafInfo = json_decode($mafInfo, true);?>
                        <div>Заявка <a href="/s/gallery/?maf_id=<?=$id?>"><?=$mafInfo['NAME']?></a> от <?=setDateFormat($mafInfo['DATE_CREATE'])?></div>
                    <?endif;*/?>
                <a href="/s/gallery/?maf_id=<?=$id?>"><?=$name['name']?></a><?/* [<?=$name['cnt']?>]*/?> &nbsp;
            <?endforeach;?>
            </div>
        </div>
    <?endforeach;?>
</div>