<?php
define("DIRECTORY", "/s/gallery/");
define('FILEROOT', __DIR__);
require_once dirname(dirname(__DIR__)).'/init/config.php';

$header['top_menu_active'] = "top_menu_gallery";
$header['top_menu_text'] = "Галерея по заявкам";
$header["title"] = "Логи действий пользователей";

$scripts = '<script src="'.DIRECTORY.'js.js?'.rand().'" type="text/javascript"></script>';

require_once dirname(dirname(__DIR__)).'/init/head.php';


//$header["title"] = "Логи действий пользователей";
$header['inlogs'] = "menu-active";
//include dirname(__DIR__)."/product/header.php";
require_once dirname(dirname(__DIR__)).'/class/paginator.php';

global $access;
global $USER;

$fullAccess = false;

$searchSuperUser = new DB();
$superUser = $searchSuperUser->request("select id from bot_photo_user where login='".$USER['login']."' and superuser=1 and active=1");
if($superUser['count'] > 0)
{
    $fullAccess = true;
}

if ($fullAccess === false)
{
    ?>
    Доступ в данный раздел запрещен.
    <?php
    //include FILEROOT."/product/footer.php";
    require_once dirname(dirname(__DIR__)).'/init/foot.php';
    exit();
}

$offset = 0;
$page = 1;
$step = 100;
$pagination = false;
if(isset($_GET['page']) && intval($_GET['page']) > 1)
{
    $page = intval($_GET['page']);
    $offset = $page * $step - $step;
}
$dateFrom = date('Y-m-d');
$dateTo = date('Y-m-d');
//$date = '';
if(isset($_GET['dateFrom']))
{
    $dateFrom = trim($_GET['dateFrom']);
}
if(isset($_GET['dateTo']))
{
    $dateTo = trim($_GET['dateTo']);
}

$userLg = '';
$userBot = '';
if(isset($_GET['users']) && strlen($_GET['users']) > 1)
{
    $userLg = trim($_GET['users']);
}
if(isset($_GET['users_bot']) && intval($_GET['users_bot']) > 0)
{
    $userBot = intval($_GET['users_bot']);
}

$action = '';
if(isset($_GET['action']) && strlen($_GET['action']) > 1)
{
    $action = trim($_GET['action']);
}
//pre($_GET);
//pre($date);
//pre($user);
//pre($action);

$where = [];
if(!empty($dateFrom))
{
    $where[] = "l.date >= '".$dateFrom."'";
}
if(!empty($dateTo))
{
    $where[] = "l.date <= '".$dateTo." 23:59:59'";
}
if(!empty($userLg))
{
    $where[] = "l.users_login = '".$userLg."'";
}
if(!empty($userBot))
{
    $where[] = "l.bot_photo_user_id = '".$userBot."'";
}
if(!empty($action))
{
    $where[] = "l.action LIKE '%".$action."%'";
}

$requestpages = new DB();
$req = "SELECT COUNT(*) cnt FROM bot_photo_log l";
if(!empty($where))
{
    $req .= " WHERE ".implode(" AND ", $where);
}
$totalselect = $requestpages->request($req);

$countpage = 0;
$count = 0;

if($totalselect['count'] > 0)
{
    $count = $totalselect['results'][0]['cnt'];
    $countpage = ceil($count/$step);
    if($countpage > 1)
    {
        $pagination = true;
    }
}

//выборка
$select = new DB();
$request = "SELECT l.*, photo.path, photo.del, photo.name
    FROM bot_photo_log l LEFT JOIN bot_photo_maf photo ON photo.id=l.bot_photo_maf_id";
if(!empty($where))
{
    $request .= " WHERE ".implode(" AND ", $where);
}
$request .= " ORDER BY l.id DESC LIMIT ".$offset.",".$step;

//pre($where);
//pre($request);
$logs = $select->request($request);
//pre($logs);

global $url;

//постраничка
$peger = new DBPaginator($url, $page, $step);
$items = $peger->getItems($count);

$allUsersLg = [];
$allUsersBot = [];

//выбираем пользователя для фильтра
$selectuser = new DB();
$users = $selectuser->request("SELECT users_login login FROM `bot_photo_log` WHERE users_login IS NOT NULL GROUP BY users_login");
if($users['count'] > 0)
{
    foreach ($users['results'] as $u)
    {
        $allUsersLg[$u['login']] = $u['login'];
    }
}

$selectuser = new DB();
$users = $selectuser->request("SELECT u.user_id users_id, u.name login 
                                        FROM `bot_photo_log` l 
                                            LEFT JOIN bot_photo_user u 
                                                ON u.user_id=l.bot_photo_user_id    
                                            WHERE l.bot_photo_user_id  IS NOT NULL
                                            GROUP BY l.bot_photo_user_id");
if($users['count'] > 0)
{
    foreach ($users['results'] as $u)
    {
        $allUsersBot[$u['users_id']] = $u['login'];
    }
}
//pre($allUsersLg);
//pre($allUsersBot);


//выбираем действия для фильтра
$actions = [];
$selectaction = new DB();
$allactions = $selectaction->request("SELECT `action` FROM `bot_photo_log` GROUP BY `action` ORDER BY `action` ASC");
foreach ($allactions['results'] as $a)
{
    switch ($a['action'])
    {
        case "add" :
            $actions["add"] = "Добавил фото к заявке";
            break;
        case "addvideo" :
            $actions["addvideo"] = "Добавил видео к заявке";
            break;
        case "move" :
            $actions["move"] = "Перенес фото в другую заявку";
            break;
        case "movevideo" :
            $actions["movevideo"] = "Перенес видео в другую заявку";
            break;
        case "del" :
            $actions["del"] = "Удалил фото из заявки";
            break;
        case "delvideo" :
            $actions["delvideo"] = "Удалил видео из заявки";
            break;
        case "addtext" :
            $actions["addtext"] = "Добавил комментарий  к фото";
            break;
        case "edittext" :
            $actions["edittext"] = "Изменил комментарий";
            break;
        case "deltext" :
            $actions["deltext"] = "Удалил комментарий";
            break;
    }
}
asort($actions);
//pre($req);
//pre($request);
?>

<?
/* Инфо о текущей странице */
//echo '<p>Страница ' . $peger->page . ' из ' . $peger->amt . '</p>';

/*<nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark" aria-label="Main navigation" style="height: 54px">
        <div class="container-fluid text-white"><?=$header["title"]?></div>
    </nav>*/?>
    <div class="nav-scroller bg-white">
    <div>
        <div class="fs-5 fw-bold">
            Логи
        </div>
        <br>
        <div>
            <a role="button" class="btn btn-primary" href="/s/gallery/installers.php"><i class="bi bi-people color-white"></i> Список пользователей</a>

            <a role="button" class="btn btn-primary" href="/s/gallery/"><i class="bi bi-images color-white"></i> Галерея</a>

            <a role="button" class="btn btn-primary" href="/s/gallery/inlogs.php"><i class="bi bi-list-ul color-white"></i> Логи</a>
        </div>
        <br>

        <form action="" method="get">
            <div class="container-fluid-first border-0 pe-1 ps-1">
                <div id="helpBlockDateFrom" class="form-text">Дата от</div>
                <input name="dateFrom" class="form-control me-2 mb-1" type="date" id="setDateFrom" value="<?=$dateFrom?>">
            </div>
            <div class="container-fluid-first border-0 pe-1 ps-1">
                <div id="helpBlockDateTo" class="form-text">до</div>
                <input name="dateTo" class="form-control me-2 mb-1" type="date" id="setDateTo" value="<?=$dateTo?>">
            </div>
            <div class="container-fluid-first border-0 pe-1 ps-1">
                <div id="helpBlockUser" class="form-text">Сотрудник ЛГ</div>
                <select name="users" id="setUsers" class="form-select form-select-sm" aria-label=".form-select-sm example">
                    <option value="" selected>---</option>
                    <?foreach ($allUsersLg as $u):?>
                        <option value="<?=$u?>" <?if($userLg == $u) echo 'selected';?>><?=$u?></option>
                    <?endforeach;?>
                </select>
            </div>
            <div class="container-fluid-first border-0 pe-1 ps-1">
                <div id="helpBlockUser" class="form-text">Пользователь ТГ</div>
                <select name="users_bot" id="setUsersBot" class="form-select form-select-sm" aria-label=".form-select-sm example">
                    <option value="" selected>---</option>
                    <?foreach ($allUsersBot as $id=>$u):?>
                        <option value="<?=$id?>" <?if($userBot == $id) echo 'selected';?>><?=$u?></option>
                    <?endforeach;?>
                </select>
            </div>
            <div class="container-fluid-first border-0 pe-1 ps-1">
                <div id="helpBlockAction" class="form-text">Действие</div>
                <select name="action" id="setAction" class="form-select form-select-sm" aria-label=".form-select-sm example">
                    <option value="" selected>---</option>
                    <?foreach ($actions as $k=>$a):?>
                        <option value="<?=$k?>" <?if($action == $k) echo 'selected';?>><?=$a?></option>
                    <?endforeach;?>
                </select>


            </div>

            <div class="container-fluid-first border-0 pe-1 ps-1">
                <div id="helpBlockButton" class="form-text">&nbsp;</div>
                <button class="btn btn-primary mb-1" type="submit" aria-describedby="searchHelpBlock"><i class="bi bi-search color-white"></i> Искать</button>
                <a class="text-decoration-none text-nowrap ps-2" role="button" href="/s/gallery/inlogs.php"><i class="bi bi-x-lg color-red"></i>&nbsp;<span class="color-red text-decoration-none fw-bold">Сбросить</span></a>
            </div>
        </form>

        <br>
        <div>Записей найдено: <?=$count?></div>
        <br>
        <div class="d-table w-100">
            <?
            if($logs['count'] > 0)
            {
                ?>
                <div class="">
                    <?/*<div class="d-inline p-2 bd-highlight"><?=($n+1)?></div>*/?>
                    <div class="py-2 d-inline-block col-1">Дата</div>
                    <div class="py-2 d-inline-block col-1">Сотрудник ЛГ</div>
                    <div class="py-2 d-inline-block col-2">Пользователь ТГ</div>
                    <div class="py-2 d-inline-block col-4">Действие</div>
                    <div class="py-2 d-inline-block col-2">Заявка</div>
                    <?/*<div class="py-2 d-inline-block col-1">Фото</div>*/?>
                </div>
                <?
                foreach($logs['results'] as $n=>$log)
                {
                    if($_SERVER['HTTP_HOST'] == 'service.local.leber')
                    {
                        $log['path'] = str_replace("/home/service/www/service.leber.group", "/var/www/html", $log['path']);
                    }

                    $currentMaf = '<a href="/s/gallery/?maf_id='.$log['maf_id'].'">'.$log['name'].'</a>';
                    //$oldMaf = '';
                    if($log['new_maf_id'])
                    {
                        $currentMaf = '<small class="text-secondary"><a href="/s/gallery/?maf_id='.$log['maf_id'].'">'.$log['name'].'</a></small> ⟶ <a href="/s/gallery/?maf_id='.$log['new_maf_id'].'">'.$log['new_maf_id'].'</a>';
                        //$oldMaf = '<div class="color-gray small">Предыдущая: '.$log['maf_id'].'</div>';
                    }

                    $text = '';
                    if($log['action'] == "addtext")
                    {
                        $text = '<br><span class="small text-dark">'.$log['newtext'].'</span>';
                    }
                    if($log['action'] == "edittext")
                    {
                        $text = '<br><span class="small fst-italic">'.$log['oldtext'].'</span> ⟶ <span class="small text-dark">'.$log['newtext'].'</span>';
                    }
                    if($log['action'] == "deltext")
                    {
                        $text = '<br><span class="small text-dark">'.$log['oldtext'].'</span>';
                    }
                    ?>
                    <div class="border-top">
                        <?/*<div class="d-inline p-2 bd-highlight"><?=($n+1)?></div>*/?>
                        <div class="py-2 d-inline-block col-1 small"><?=setDateFormat($log['date'])?></div>
                        <div class="py-2 d-inline-block col-1"><?=$log['users_login']?></div>
                        <div class="py-2 d-inline-block col-2"><?=$allUsersBot[$log['bot_photo_user_id']]?></div>
                        <div class="py-2 d-inline-block col-4">
                            <?=$actions[$log['action']]?>
                            <?=$text?>
                        </div>
                        <div class="py-2 d-inline-block col-2">
                            <?=$currentMaf?>
                        </div>
                        <?/*<div class="py-2 d-inline-block col-1">
                            <?if(file_exists($log['path']) && $log['del'] == 0):?>
                                <img id="img_<?=$log['id']?>" style="cursor: pointer;" onclick="showImg(<?=$log['id']?>)" width="50" src="/init/resize.php?img=<?=$log['path']?>&w=450">
                            <?endif;?>
                        </div>*/?>
                    </div>
                    <?
                }
                if($pagination === true)
                {
                    echo '<br /><div class="">';
                    echo $peger->display;
                    echo '</div>';
                }
            }
            else
            {
                echo "Логи отсутствуют";
            }
            ?>
        </div>
    </div>
    <br>
    <br>
    <div id="showContent" class="newWindow">
        <div id="showContentInside" class="shadow">
            <span class="close closeWindow"></span>
            <div id="showContentHtml"></div>
        </div>
    </div>

<?// include FILEROOT."/product/footer.php";?>
<?php
require_once dirname(dirname(__DIR__)).'/init/foot.php';
?>
