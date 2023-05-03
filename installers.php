<?php
ob_start();
define("DIRECTORY", "/s/gallery/");
define('FILEROOT', __DIR__);
require_once dirname(dirname(__DIR__)).'/init/config.php';

$header['top_menu_active'] = "top_menu_gallery";
$header['top_menu_text'] = "Галерея по заявкам";
$header["title"] = "Список пользователей";

require_once dirname(dirname(__DIR__)).'/init/head.php';



//$header["title"] = "Список монтажников";
$header['installers'] = "menu-active";
//include dirname(__DIR__)."/product/header.php";

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

$add = false;
$del = false;
$id = 0;

$instName = '';
$instPhone = '+7';
$instActive = 1;
$instError = [];
if(isset($_GET['add']))
{
    $add = true;
    if (isset($_POST['submit']))
    {
        //pre($_POST);
        $instName = trim($_POST['name']);
        $instPhone = trim($_POST['phone']);
        //$instActive = trim($_POST['active']);

        if(empty($instName))
        {
            $instError[] = "Не заполнено поле Имя.";
        }
        if(empty($instPhone))
        {
            $instError[] = "Не заполнено поле Телефон.";
        }
        else
        {
            if(strlen($instPhone) != 12)
            {
                $instError[] = "Некорректный номер Телефона.";
            }
        }

        if(empty($instError))
        {
            $add = new DB();
            $res = $add->addDB('bot_photo_user', ['name'=>$instName, 'phone'=>$instPhone, 'active'=>$instActive, 'whoAdd'=>$USER['login']]);
            if($res["insert_id"])
            {
                $add = false;
            }
            else
            {
                $instError[] = "Не удалось добавить пользователя.";
            }
        }
    }
}
if(isset($_GET['edit']))
{

    $editUserId = intval($_GET['edit']);
    $editUserActive = intval($_GET['active']);
    $editUserAdmin = intval($_GET['admin']);
    $editSuperUser = intval($_GET['superuser']);
    $editUserName = trim($_GET['name']);
    $editUserLogin = trim($_GET['login']);
    $editUser = new DB();
    $res = $editUser->editDB('bot_photo_user', $editUserId, ['active'=>$editUserActive,'superuser'=>$editSuperUser,'login'=>$editUserLogin,'admin'=>$editUserAdmin,'name'=>$editUserName, 'whoEdit'=>$USER['login'], 'dateEdit'=>date("Y-m-d H:i:s")]);
    ob_end_clean();
    if($res === true)
    {

        exit("Y");
    }
    exit("N");
    /*?>
    <script>window.location.href='/s/passportsGenerator/product/installers.php';</script>
    <?php*/
}

$allInstallers = [];
$searchInstallers = new DB();
$installers = $searchInstallers->request("select * from bot_photo_user");
foreach ($installers['results'] as $i)
{
    $allInstallers[] = $i;
}
?>
    <script>
        (function( $ ) {
            $(function() {
                $("#phone").mask('+70000000000');
                $(".phoneInstallerMask").mask('+7 (000) 000-00-00');
            });
        })(jQuery);

        function editStr(id)
        {
            $(".str_"+id).hide();
            $(".edit_str_"+id).show();
        }
        function cancelStr(id)
        {
            $(".edit_str_"+id).hide();
            $(".str_"+id).show();
        }
        function saveStr(id)
        {
            var get = "/s/gallery/installers.php?edit="+id;
            $( ".save_str_"+id ).each(function( index ) {
                //console.log( $( this ).val() +  $( this ).attr("name")+  $( this ).prop("checked"));

                switch ($( this ).attr("name"))
                {
                    case "admin":
                    case "active":
                    case "superuser":
                        if($( this ).prop("checked") === true)
                            get += "&"+$( this ).attr("name")+"=1";
                        else
                            get += "&"+$( this ).attr("name")+"=0";
                        break;
                    case "name":
                    case "login":
                        get += "&"+$( this ).attr("name")+"="+$( this ).val();
                        break;
                }
            });
            //console.log(get);

            $.get(get, function (answer){
                if(answer == "Y")
                {
                    window.location.href='/s/gallery/installers.php';
                }
                else
                {
                    alert("Ошибка сохранения!");
                }
            });

            /*var n = $("").val();
            var get = "/s/passportsGenerator/product/installers.php?id="+id+"&admin="+;
            alert();
            $.get("/s/passportsGenerator/product/installers.php?id="+id+"&admin="+$("").val(), function (){

            });*/


        }
    </script>

<?if($add === true):?>
    <div class="container-fluid-first border position-absolute bg-white p-4 rounded" style="width: 400px; top: 80px; z-index: 1000;">
        <form action="/s/gallery/installers.php?add" method="post">
            <?if(!empty($instError)):?>
                <div class="text-danger"><?=implode("<br>", $instError);?></div><br>
            <?endif;?>
            <div><b>Новый пользователь</b></div>
            <div>Имя: <input type="text" name="name" class="form-control me-2 mb-1" value="<?=$instName?>"></div>
            <div>Номер телефона: <input id="phone" type="tel" name="phone" class="form-control me-2 mb-1" value="<?=$instPhone?>"></div>
            <?/*<div>Активен: <select name="active" class="form-control me-2 mb-1 form-select form-select-sm">
                    <option value="1" <?if($instActive == 1) echo "selected";?>>Да</option>
                    <option value="0" <?if($instActive == 0) echo "selected";?>>Нет</option>
                </select></div>*/?>
            <div><button class="btn btn-primary mb-1  me-3" name="submit" type="submit">Добавить</button> <button onclick="window.location.href='/s/gallery/installers.php'" class="btn btn-primary mb-1  me-3" type="button">Отменить</button></div>
        </form>
    </div>
<?endif;?>

<?/*if($del === true):?>
    <div class="container-fluid-first border position-absolute bg-white p-4 rounded" style="width: 400px; top: 80px">
        <div class="text-danger">Ошибка удаления пользователя</div>
    </div>
<?endif;*/?>


    <div class="nav-scroller bg-body">
        <div class="container-fluid">
            <div class="fs-5 fw-bold">
                Список пользователей
            </div>
            <br>

            <?if($fullAccess === true):?>
                <div>
                    <a role="button" class="btn btn-primary" href="/s/gallery/installers.php"><i class="bi bi-people color-white"></i> Список пользователей</a>

                    <a role="button" class="btn btn-primary" href="/s/gallery/"><i class="bi bi-images color-white"></i> Галерея</a>
                    <?if($access === true):?>
                        <a role="button" class="btn btn-primary" href="/s/gallery/inlogs.php"><i class="bi bi-list-ul color-white"></i> Логи</a>
                    <?endif;?>
                    <a role="button" class="btn btn-gray" href="/s/gallery/installers.php?add"><i class="bi bi-person-plus color-white"></i> Добавить пользователя</a>
                </div>
                <br>
            <?endif;?>

            <div class="d-table w-100">
                <div>
                    <div class="p-2 d-inline-block col-1"><b>Активен</b></div>
                    <div class="p-2 d-inline-block col-2"><b>Дата добавления</b></div>
                    <div class="p-2 d-inline-block col-1"><b>Телефон</b></div>
                    <div class="p-2 d-inline-block col-2"><b>Имя/Логин</b></div>
                    <div class="p-2 d-inline-block col-1"><b>Админ</b></div>
                    <div class="p-2 d-inline-block col-1"><b>Суперюзер</b></div>
                    <div class="p-2 d-inline-block col-1"><b>Telegram id</b></div>

                    <div class="p-2 d-inline-block col-2"><b>Дата авторизации</b></div>
                </div>

                <?foreach ($allInstallers as $i):
                    $uActive = $i['active'];
                    $editActive = ($uActive == 1) ? 0 : 1;
                    $editActiveText = ($uActive == 1) ? "Деактивировать" : "Активировать";
                    ?>
                    <div class="border-top <?if($i['active'] != 1) echo "bg-danger bg-opacity-10";?>">
                        <div class="p-2 d-inline-block col-1 button-delete position-relative text-nowrap">
                            <span class="str_<?=$i['id']?> pe-3" class="color-red text-decoration-none text-nowrap">
                                <span onclick="editStr(<?=$i['id']?>); return false;" title="Редактировать" role="button" class="badge border-1 btn-gray-white rounded-5"><i class="bi bi-pencil color-gray"></i></span>
                            </span>
                            <span class="hide edit_str_<?=$i['id']?> pe-3" class="color-red text-decoration-none text-nowrap">
                                <span onclick="saveStr(<?=$i['id']?>); return false;" title="Сохранить" role="button" class="badge border-1 btn-gray-white rounded-5"><i class="bi bi-save color-gray"></i></span>
                                <span onclick="cancelStr(<?=$i['id']?>); return false;" title="Отменить" role="button" class="badge border-1 btn-gray-white rounded-5"><i class="bi bi-x-lg color-gray"></i></span>
                            </span>
                            <span class="str_<?=$i['id']?>"><?=($i['active'] == 1) ? "Да" : "Нет"?></span>
                            <span class="hide edit_str_<?=$i['id']?>"><input value="" class="form-check-input save_str_<?=$i['id']?>" type="checkbox" name="active" <?if($uActive == 1) echo "checked";?>></span>
                        </div>
                        <div class="p-2 d-inline-block col-2"><?=setDateFormat($i['date'])?></div>
                        <div class="p-2 d-inline-block col-1 phoneInstallerMask"><?=$i['phone']?></div>
                        <div class="p-2 d-inline-block col-2">
                            <span class="str_<?=$i['id']?>">[<?=$i['id']?>] <?=$i['name']?></span>
                            <span class="str_<?=$i['id']?>"> / <?=$i['login']?></span>
                            <span class="hide edit_str_<?=$i['id']?>"><input class="form-control save_str_<?=$i['id']?>" type="text" name="name" value="<?=$i['name']?>"></span>
                            <span class="hide edit_str_<?=$i['id']?>"><input class="form-control save_str_<?=$i['id']?>" type="text" name="login" value="<?=$i['login']?>"></span>
                        </div>

                        <div class="p-2 d-inline-block col-1">
                            <span class="str_<?=$i['id']?>"><?=($i['admin'] == 1) ? "Да" : "Нет"?></span>
                            <span class="hide edit_str_<?=$i['id']?>"><input value="" class="form-check-input save_str_<?=$i['id']?>" type="checkbox" name="admin" <?if($i['admin'] == 1) echo "checked";?>></span>
                        </div>
                        <div class="p-2 d-inline-block col-1">
                            <span class="str_<?=$i['id']?>"><?=($i['superuser'] == 1) ? "Да" : "Нет"?></span>
                            <span class="hide edit_str_<?=$i['id']?>"><input value="" class="form-check-input save_str_<?=$i['id']?>" type="checkbox" name="superuser" <?if($i['superuser'] == 1) echo "checked";?>></span>
                        </div>
                        <div class="p-2 d-inline-block col-1"><?=$i['user_id']?></div>

                        <div class="p-2 d-inline-block col-2"><?if($i['auth']) echo setDateFormat($i['auth'])?></div>
                    </div>
                <?endforeach;?>

            </div>
        </div>

        <br />
        <br />


    </div>

<?// include FILEROOT."/product/footer.php";?>
<?php
require_once dirname(dirname(__DIR__)).'/init/foot.php';
?>
