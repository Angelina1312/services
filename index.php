<?php
ob_start();

define("DIRECTORY", "/s/gallery/");
define('FILEROOT', __DIR__);
require_once dirname(dirname(__DIR__)).'/init/config.php';



$header['top_menu_active'] = "top_menu_gallery";
$header['top_menu_text'] = "Галерея по заявкам";
$header["title"] = "Галерея по заявкам";

$scripts = '<script src="/scripts/jquery.lazyload.min.js" type="text/javascript"></script><script src="/scripts/Sortable.js" type="text/javascript"></script><script src="'.DIRECTORY.'js.js?'.rand().'" type="text/javascript"></script>';

require_once dirname(dirname(__DIR__)).'/init/head.php';

//$header["title"] = "Галерея по заявкам";
//$header['gallery'] = "menu-active";
//include __DIR__."/product/header.php";


//$header["title"] = "Галерея по заявкам";
//$header['ingallery'] = "menu-active";
//$header['top_menu_active'] = "top_menu_ingallery";
//$header['top_menu_text'] = "Галерея";


//include dirname(__DIR__)."/product/header.php";

global $access;
global $USER;

$fullAccess = false;
$userEditContent = [];
$searchSuperUser = new DB();
$superUser = $searchSuperUser->request("select * from bot_photo_user where superuser=1 and active=1");
if($superUser['count'] > 0)
{
    //$fullAccess = true;
    foreach ($superUser['results'] as $u)
    {
        $userEditContent[$u['login']] = $u['name'];
        if($u['login'] == $USER['login'])
        {
            $fullAccess = true;
        }
    }
}

$maf_id = 0;
if(isset($_GET['maf_id']))
{
    $maf_id = intval($_GET['maf_id']);
}

//$fullAccess = false;
/*if ($fullAccess === false)
{
    ?>
    Доступ в данный раздел запрещен.
    <?php
    include FILEROOT."/product/footer.php";
    exit();
}*/

//orders

//$requestMaf = "SELECT COUNT(*), `unit_name`, `maf_id` FROM `bot_photo_maf` WHERE `del`=0 AND `order_id` IS NULL AND `unit_code`='maf' GROUP BY `maf_id`";
//$requestPr = "SELECT COUNT(*), `unit_name`, `maf_id` FROM `bot_photo_maf` WHERE `del`=0 AND `order_id` IS NULL AND `unit_code`='production' GROUP BY `maf_id`";


/*$searchInstallers = new DB();
$installers = $searchInstallers->request($requestMaf);
//pre($installers['results']);
if($installers['count'])
{
    foreach ($installers['results'] as $i)
    {
        $maf['maf'][$i['maf_id']] = $i['maf_id'];
    }
}*/

/*$searchInstallers = new DB();
$installers = $searchInstallers->request($requestPr);
//pre($installers['results']);
if($installers['count'])
{
    foreach ($installers['results'] as $i)
    {
        $pr['pr'][$i['maf_id']] = $i['maf_id'];
    }
}*/

//pre($orders);
//pre($maf);
//pre($pr);





//$mafs = [];
/*foreach ($installers['results'] as $i)
{
    if($_SERVER['HTTP_HOST'] == 'service.local.leber')
    {
        $i['path'] = str_replace("/home/service/www/service.leber.group", "/var/www/html", $i['path']);
    }
    if(file_exists($i['path']))
    {
        $allInstallers[$i['maf_id']][] = $i;
    }
}*/
//pre($allInstallers);
?>
<div class="nav-scroller bg-body">
    <div class="container-fluid">
        <div class="fs-5 fw-bold">
            Галерея по заявкам
        </div>

        <br>

        <?if($fullAccess === true):?>
            <div>
                <a role="button" class="btn btn-primary" href="/s/gallery/installers.php"><i class="bi bi-people color-white"></i> Список пользователей</a>

                <a role="button" class="btn btn-primary" href="/s/gallery/"><i class="bi bi-images color-white"></i> Галерея</a>
                <?if($access === true):?>
                    <a role="button" class="btn btn-primary" href="/s/gallery/inlogs.php"><i class="bi bi-list-ul color-white"></i> Логи</a>
                <?endif;?>
            </div>
            <br>
        <?endif;?>

        <?if($maf_id > 0)
        {
            require_once 'report.php';
        }
        else
        {
            //
            if (isset($_GET['type']))
            {
                switch ($_GET['type'])
                {
                    case "order" :
                        require_once 'all_orders.php';
                    break;
                    default :
                        require_once 'all_other.php';
                    break;
                }

            }
            else
            {
                $requestUnit = "SELECT `unit_name`, `unit_code`
                    FROM `bot_photo_maf` WHERE `del`=0 GROUP BY `unit_code`";
                $searchInstallers = new DB();
                $installers = $searchInstallers->request($requestUnit);


                $requestOrders = "SELECT COUNT(*) cnt, `order_id`, `maf_id`,`unit_code`,`name`,`path` 
                    FROM `bot_photo_maf` 
                    WHERE `del`=0 AND `order_id` > 0 GROUP BY `maf_id` ORDER BY `order_id` DESC, `maf_id` DESC LIMIT 0,10";
                $searchInstallersOrders = new DB();
                $installersOrders = $searchInstallersOrders->request($requestOrders);
                $orders = [];
                foreach ($installersOrders['results'] as $i)
                {
                    $orders[$i['order_id']][$i['maf_id']]['name'] = $i['name'];
                    $orders[$i['order_id']][$i['maf_id']]['cnt'] = $i['cnt'];
                }
            ?>
            <div class="row">
            <div class="col-md-4 col-lg-3 col-sm-6 mb-3">
                <div class="fs-5 fw-bold">Заказы</div><br>
                <div>10 последних</div>

                <br>

                    <?foreach ($orders as $o_id=>$o):?>
                    <div class="d-table ms-2 p-1 w-100">
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
                <br><div><a href="/s/gallery/?type=order">смотреть все заявки</a></div>
            </div>



            <?foreach ($installers['results'] as $i):
                $requestUnitRequest = "SELECT COUNT(*) cnt, `unit_name`, `maf_id`,`unit_code`,`name`,`path` 
                    FROM `bot_photo_maf` WHERE `del`=0 AND `unit_code`='".$i['unit_code']."' GROUP BY `maf_id` ORDER BY `maf_id` DESC LIMIT 0,10";
                $searchInstallersRequest = new DB();
                $installersRequest = $searchInstallersRequest->request($requestUnitRequest);
                ?>
                <div class="col-md-4 col-lg-3 col-sm-6 mb-3">
                    <div class="fs-5 fw-bold"><?=$i['unit_name']?></div><br>
                    <div>10 последних</div>

                    <br>

                    <?foreach ($installersRequest['results'] as $item):
                        //pre($item);?>
                        <div class="ms-2 p-1"><a href="/s/gallery/?maf_id=<?=$item['maf_id']?>"><?=$item['name']?></a><?/* [<?=$name['cnt']?>]*/?></div>
                    <?endforeach;?>
                    <br><div><a href="/s/gallery/?type=<?=$i['unit_code']?>">смотреть все заявки</a></div>
                </div>

            <?endforeach;?>
            </div>

            <?
            }
        }
        ?>
    </div>
</div>

<br />
<br />
<div id="showContent" class="newWindow">
    <div id="showContentInside" class="shadow">
        <span class="close closeWindow"></span>
        <div id="showContentHtml"></div>
    </div>
</div>
<?php
require_once dirname(dirname(__DIR__)).'/init/foot.php';
?>
