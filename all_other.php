<?php
$type = '';
if($_GET['type'])
{
    $type = trim($_GET['type']);
}
else
{
    exit();
}
$filter_report = '';

$maf = [];
$units = [];
$pr = [];
$allInstallers = [];

if(isset($_GET['filter_report']))
{
    $filter_report = trim($_GET['filter_report']);
}


$requestUnit = "SELECT COUNT(*) cnt, `unit_name`, `maf_id`,`unit_code`,`name` ,`path` 
FROM `bot_photo_maf` WHERE `del`=0 AND `unit_code`='".$type."'";

if(!empty($filter_report))
{
    $requestUnit .=  " AND `name` LIKE '%".$filter_report."%'";
}

$requestUnit .= " GROUP BY `maf_id` ORDER BY `maf_id` DESC LIMIT 0,500";
$searchInstallers = new DB();
$installers = $searchInstallers->request($requestUnit);
if($installers['count'])
{
    foreach ($installers['results'] as $i)
    {
        $units[$i['unit_name']][$i['maf_id']]['name'] = $i['name'];
        $units[$i['unit_name']][$i['maf_id']]['cnt'] = $i['cnt'];
    }
}
?>
<div id="block_filter" class="fixed-top bg-white">
    <form class="d-flex" action="/s/gallery/" method="get">
        <input type="hidden" name="type" value="<?=$type?>">
        <div class="container-fluid-first border-0 pe-2 mb-1">
            <div id="helpBlock" class="form-text">Номер заявки</div>
            <input value="<?=$filter_report?>" name="filter_report" class="form-control" type="search" style="width: 220px;" aria-describedby="helpBlock">
        </div>
        <div class="ontainer-fluid-first border-0 pe-2 mb-1">
            <div id="searchHelpBlock" class="form-text">&nbsp;</div>
            <button class="btn btn-primary" type="submit" aria-describedby="searchHelpBlock"><i class="bi bi-search color-white"></i> Искать</button>
            <a class="text-decoration-none text-nowrap ps-2" role="button" href="/s/gallery/?type=<?=$type?>"><i class="bi bi-x-lg color-red"></i>&nbsp;<span class="color-red text-decoration-none fw-bold">Сбросить</span></a>
        </div>
    </form>
</div>

<br>

<div>
    <?foreach ($units as $unit_name=>$o):?>
        <div>
            <div class="fs-5 fw-bold pb-2"><?=$unit_name?></div>
            <?foreach ($o as $id=>$name):?>
                <div class="pt-2 pb-2 border-top w-100"><a href="/s/gallery/?maf_id=<?=$id?>"><?=$name['name']?></a><?/* [<?=$name['cnt']?>]*/?></div>
            <?endforeach;?>
        </div>
    <?endforeach;?>
</div>
