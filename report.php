<?php
//phpinfo();
require_once dirname(dirname(__DIR__)) . '/extravendor/vendor/autoload.php';
require_once dirname(dirname(__DIR__)).'/class/Mobile_Detect.php';

$detect = new Mobile_Detect;
// Мобильные и планшеты
$is_mobile = $detect->isMobile();
/*function imagefillroundedrect($im,$x,$y,$cx,$cy,$rad,$col)
{
    $im = imagecreatetruecolor($x, $y);
    $black = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefilledrectangle($im, 0, 0, 89, 89, $black);
// Draw the middle cross shape of the rectangle

    imagefilledrectangle($im,$x,$y+$rad,$cx,$cy-$rad,$col);
    imagefilledrectangle($im,$x+$rad,$y,$cx-$rad,$cy,$col);

    $dia = $rad*2;

// Now fill in the rounded corners

    imagefilledellipse($im, $x+$rad, $y+$rad, $rad*2, $dia, $col);
    imagefilledellipse($im, $x+$rad, $cy-$rad, $rad*2, $dia, $col);
    imagefilledellipse($im, $cx-$rad, $cy-$rad, $rad*2, $dia, $col);
    imagefilledellipse($im, $cx-$rad, $y+$rad, $rad*2, $dia, $col);
}*/
function roundRectangle($file, $endX, $endY)
{
    //imagefillroundedrect($im,$x,$y,$cx,$cy,$rad,$col);
    $draw = new \ImagickDraw();

    $draw->setStrokeColor('#DEE2E6');
    $draw->setFillColor('#FFFFFF');
    $draw->setStrokeOpacity(1);
    $draw->setStrokeWidth(2);
    //$draw->setBorderColor('#DEE2E6');
    $draw->setStrokeAntialias(false);

    $draw->roundRectangle(0, 0, $endX, $endY, 4, 4);

    $imagick = new \Imagick();
    $imagick->newImage($endX, $endY, '#FFFFFF');
    $imagick->setImageFormat("jpg");

    $imagick->drawImage($draw);
    $imagick->writeImage($file);

    //header("Content-Type: image/png");
    //echo $imagick->getImageBlob();
}

//require_once dirname(dirname(__DIR__)).'/class/HTMLtoOpenXML/HTMLtoOpenXML.php';
//require_once dirname(__DIR__)."/passportsGenerator/xml/HTMLtoOpenXML.php";

//use Char0n\FFMpegPHP\Movie;
use Char0n\FFMpegPHP\Adapters\FFMpegMovie as ffmpeg_movie;
use Char0n\FFMpegPHP\Adapters\FFMpegFrame as ffmpeg_frame;

$handsort = 'hand';
$allInstallers = [];

if (empty($maf_id)) {
    http_response_code(400);
    exit("Не указан номер заявки!");
}

$search = new DB();
$searchMaf = $search->request("select id from bot_photo_maf where maf_id=" . $maf_id);
if ($searchMaf['count'] == 0) {
    http_response_code(400);
    exit("Заявка не найдена!");
}
//проверяем, есть ли такая заявка
$mafInfo = file_get_contents("https://lebergroup.ru/ya/photobotmaf.php?token=dhfgajO99UjkklYUTbj9&id=" . $maf_id);
if (empty($mafInfo)) {
    http_response_code(400);
    exit("Заявка не найдена!");
}

$mafInfo = json_decode($mafInfo, true);

if ($_GET['sort'] && $fullAccess === true) {
    ob_end_clean();

    //pre($_POST);
    if (empty($_POST['selectFile'])) {
        http_response_code(400);
        exit("Отметьте файлы для удаления!");
    }

    foreach ($_POST['selectFile'] as $idPhoto => $sortPhoto) {
        $edit = new DB();
        $edit->editDB('bot_photo_maf', $idPhoto, ['sort' => $sortPhoto]);
    }


    exit();

}
if ($_GET['move'] && $fullAccess === true) {
    ob_end_clean();

    if (empty($_POST['selectFile'])) {
        http_response_code(400);
        exit("Отметьте фото для переноса");
    }
    if (empty($_POST['new_maf_id'])) {
        http_response_code(400);
        exit("Введите номер заявки");
    } else {
        $new_maf_id = str_replace(['МАФ', 'ПР', '-', 'РЕК'], '', $_POST['new_maf_id']); // добавил рекламации
        if ($new_maf_id == $maf_id) {
            http_response_code(400);
            exit("Новая заявка равна текущей");
        }
    }
    /*if(empty($maf_id))
    {
        http_response_code(400);
        exit("Ошибка перемещения");
    }*/

    //проверяем, есть ли такая заявка
    $mafInfo = file_get_contents("https://lebergroup.ru/ya/photobotmaf.php?token=dhfgajO99UjkklYUTbj9&id=" . $new_maf_id);
    if (empty($mafInfo)) {
        http_response_code(400);
        exit("Заявка не найдена");
    }

    $mafInfo = json_decode($mafInfo, true);

    foreach ($_POST['selectFile'] as $k => $photo_id) {
        $arr = [];
        $arr['maf_id'] = $new_maf_id;
        $arr['name'] = $mafInfo['NAME'];
        if (intval($mafInfo['PROPERTY_ORDER_VALUE']) > 0) $arr['order_id'] = intval($mafInfo['PROPERTY_ORDER_VALUE']);
        else $arr['order_id'] = 'NULL';

        if ($mafInfo['PROPERTY_UNIT_CODE']) $arr['unit_code'] = $mafInfo['PROPERTY_UNIT_CODE'];
        else $arr['unit_code'] = 'NULL';

        if ($mafInfo['PROPERTY_UNIT_NAME']) $arr['unit_name'] = $mafInfo['PROPERTY_UNIT_NAME'];
        else $arr['unit_name'] = 'NULL';

        if ($mafInfo['PROPERTY_UNIT_FROM_CODE']) $arr['unit_from_code'] = $mafInfo['PROPERTY_UNIT_FROM_CODE'];
        else $arr['unit_from_code'] = 'NULL';

        if ($mafInfo['PROPERTY_UNIT_FROM_NAME']) $arr['unit_from_name'] = $mafInfo['PROPERTY_UNIT_FROM_NAME'];
        else $arr['unit_from_name'] = 'NULL';

        if ($mafInfo['MANAGER_LOGIN']) $arr['manager_login'] = $mafInfo['MANAGER_LOGIN'];
        else $arr['manager_login'] = 'NULL';

        if ($mafInfo['MANAGER_EMAIL']) $arr['manager_email'] = $mafInfo['MANAGER_EMAIL'];
        else $arr['manager_email'] = 'NULL';

        $edit = new DB();
        $res = $edit->editDB('bot_photo_maf', $photo_id, $arr);
        if ($res === true) {
            $arrLog = [];
            $arrLog['maf_id'] = $maf_id;
            $arrLog['new_maf_id'] = $new_maf_id;
            $arrLog['users_login'] = $USER['login'];
            $arrLog['action'] = 'move';
            if ($_POST['typeFile'][$k] == 'video') {
                $arrLog['action'] = 'movevideo';
            }
            $arrLog['bot_photo_maf_id'] = $photo_id;

            $addLog = new DB();
            $addLog->addDB('bot_photo_log', $arrLog);
        }
    }

    //новая заявка
    $res = ['newName' => $mafInfo['NAME'], 'newId' => $new_maf_id];
    exit(json_encode($res, JSON_UNESCAPED_UNICODE));
}
if ($_GET['delete'] && $fullAccess === true) {
    ob_end_clean();

    if (empty($_POST['selectFile'])) {
        http_response_code(400);
        exit("Отметьте файлы для удаления!");
    }
    /*if(empty($maf_id))
    {
        http_response_code(400);
        exit("Ошибка удаления!");
    }*/

    foreach ($_POST['selectFile'] as $k => $photo_id) {
        $del = new DB();
        $res = $del->editDB('bot_photo_maf', $photo_id, ['del' => 1]);
        if ($res === true) {
            $arrLog = [];
            $arrLog['maf_id'] = $maf_id;
            $arrLog['users_login'] = $USER['login'];
            $arrLog['action'] = 'del';
            if ($_POST['typeFile'][$k] == 'video') {
                $arrLog['action'] = 'delvideo';
            }
            $arrLog['bot_photo_maf_id'] = $photo_id;

            $addLog = new DB();
            $addLog->addDB('bot_photo_log', $arrLog);
        }
    }

    exit();
}
if ($_GET['edittext'] && $fullAccess === true) {
    ob_end_clean();

    $textNew = trim($_POST['newtext']);
    $textOld = trim($_POST['oldtext']);
    $photo_id = trim($_POST['photo_id']);

    if (empty($photo_id)) {
        http_response_code(400);
        exit("ID комментария не определено!");
    }

    if (empty($textNew)) {
        http_response_code(400);
        exit("Поле Комментарий не заполнено!");
    }

    $arr = [];
    $arr['comment'] = $textNew;
    $arr['whoDelComment'] = 'NULL';
    if (empty($textOld)) {
        $arr['whoAddComment'] = $USER['login'];
    } else {
        $arr['whoEditComment'] = $USER['login'];
    }

    //pre($_POST);
    //pre($arr);


    $edit = new DB();
    $res = $edit->editDB('bot_photo_maf', $photo_id, $arr);
    if ($res === true) {
        $arrLog = [];
        $arrLog['maf_id'] = $maf_id;
        $arrLog['users_login'] = $USER['login'];
        $arrLog['action'] = 'addtext';
        if (!empty($textOld)) {
            $arrLog['action'] = 'edittext';
        }
        $arrLog['bot_photo_maf_id'] = $photo_id;
        $arrLog['oldtext'] = $textOld;
        $arrLog['newtext'] = $textNew;

        //pre($arrLog);
        //exit();

        $addLog = new DB();
        $addLog->addDB('bot_photo_log', $arrLog);
    } else {
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    exit();
}
if ($_GET['deltext'] && $fullAccess === true) {
    ob_end_clean();

    $textOld = trim($_POST['oldtext']);
    $photo_id = trim($_POST['photo_id']);

    if (empty($photo_id)) {
        http_response_code(400);
        exit("ID комментария не определено!");
    }

    $arr = [];
    $arr['comment'] = "NULL";
    $arr['whoAddComment'] = 'NULL';
    $arr['whoEditComment'] = 'NULL';
    $arr['whoDelComment'] = $USER['login'];

    $edit = new DB();
    $res = $edit->editDB('bot_photo_maf', $photo_id, $arr);
    if ($res === true) {
        $arrLog = [];
        $arrLog['maf_id'] = $maf_id;
        $arrLog['users_login'] = $USER['login'];
        $arrLog['action'] = 'deltext';
        $arrLog['bot_photo_maf_id'] = $photo_id;
        $arrLog['oldtext'] = $textOld;

        $addLog = new DB();
        $addLog->addDB('bot_photo_log', $arrLog);
    } else {
        exit(json_encode($res, JSON_UNESCAPED_UNICODE));
    }

    exit();
}
if ($_GET['handsort'] && $fullAccess === true) {
    $handsort = trim($_GET['handsort']);
}


$request = "select maf.*, u.`name` uploader 
        from `bot_photo_maf` maf 
            left join `bot_photo_user` u on u.`user_id`=maf.`user_id` ";
$request .= "where maf.`del`=0 and maf.`maf_id`=" . $maf_id;
switch ($handsort) {
    case "new" :
        $request .= " order by maf.`date` desc";
        break;
    case "old" :
        $request .= " order by maf.`date` asc";
        break;
    default :
        $request .= " order by maf.`sort` asc";
        break;
}

$searchInstallers = new DB();
$installers = $searchInstallers->request($request);
//pre($installers['results']);

/*$deletePath = '/home/service/www/service.leber.group';
if($_SERVER['HTTP_HOST'] == 'service.local.leber')
{
    $deletePath = '/var/www/html';
}*/


$deletePath = '';
//$fullPath = '/var/www/service.leber.group/data/www/service.leber.group/s/bot/file/';
$fullPath = dirname(__DIR__) . '/bot/file/';
$localPath = '/s/bot/file/';

$photos = [];
$videos = [];


if ($installers['count']) {
    foreach ($installers['results'] as $i) {
        /*if($_SERVER['HTTP_HOST'] == 'service.local.leber')
        {
            $i['path'] = "/var/www/html/s/bot/file/".$i['path'];
            $fullPath='';
        }*/
        //pre($fullPath.$i['path']);
        if (file_exists($fullPath . $i['path'])) {
            if ($i['type'] == 'video') {
                $infoFile = new SplFileInfo($fullPath . $i['path']);
                $moveVideo = $infoFile->getExtension();
                if(mb_strtolower($moveVideo) == "mov") {
                    $pathVideo = trim($fullPath . $i['path'], '.MOV');
                    $inputPath = $pathVideo . '.MOV';
                    $outputPath = $pathVideo . '.mp4';
                    shell_exec("/usr/bin/ffmpeg -i \"".$fullPath . $i['path'] . "\" \"$outputPath\" 2>&1");
                    $output = mb_substr($outputPath, 69);
                    $i['path'] = $output;
                    $i['filename'] = $output;
                }
                $file_out = __DIR__ . "/reports/file_out_" . $i['id'] . ".jpg";
                if (file_exists($file_out) === false) {
                    exec('ffmpeg -ss 00:00:01 -i "' . $fullPath . $i['path'] . '" -f image2 -vframes 1 "' . $file_out . '"');
                }
                if (file_exists($file_out)) {
                    $i['image'] = $file_out;
                    $videos[] = $i;
                    $allInstallers[] = $i;
                }

            } else {
                $photos[] = $i;
                $allInstallers[] = $i;
            }

        }
    }
}


$e = 0;
if (isset($_GET['e'])) {
    $e = intval($_GET['e']);
}


if ($_GET['report']) {
    ob_end_clean();
    require_once dirname(dirname(__DIR__)) . '/phpqrcode/qrlib.php';
    $totalPhoto = count($photos);
    $totalVideo = count($videos);
    $totalPhotoVideo = $totalPhoto + $totalVideo;

    if ($totalPhotoVideo == 0) {
        http_response_code(400);
        exit("Отсутствуют фото/видео для фотоотчета");
    }

    //pre($request);
    //pre($totalPhoto);
    //pre($totalVideo);
    //exit();

    $totalPageCompact = ceil($totalPhotoVideo / 4) + 1;
    //$extraPageWithPreview = [];
    $countPreview = 30;
    $extraPhoto = 0;
    if ($totalPhotoVideo > $countPreview) {
        $n = 1;
        $f = 0;
        for ($i = ($countPreview - 1); $i < $totalPhotoVideo; $i++) {
            //$extraPageWithPreview[$n][$i] = $i;
            //$f++;
            $extraPhoto++;
            /*if($f == $countPreview)
            {
                $n++;
                $f = 0;
            }*/
        }
    }

    $dir = dirname(dirname(__DIR__)) . "/s/passportsGenerator/passports/" . $USER['id'] . "/";
    if (file_exists($dir) === false) {
        mkdir($dir, 0777);
    }

    $templateFull = __DIR__ . '/reportFull.docx';
    //$templateFullTemp = __DIR__.'/reportFullTemp.docx';
    $templateCompact = __DIR__ . '/reportCompact.docx';
    //$templateCompactTemp = __DIR__.'/reportCompactTemp.docx';
    //$fileFull = $dir.'/'.$mafInfo['NAME'].' стандартный фотоотчет.docx';
    $fileFull = $dir . '/Лебер Фото-отчет о монтаже ' . $mafInfo['NAME'] . '.docx';
    //$reportFull = $dir.'/'.$mafInfo['NAME'].' стандартный фотоотчет.pdf';
    $reportFull = $dir . '/Лебер Фото-отчет о монтаже ' . $mafInfo['NAME'] . '.pdf';

    $fileCompact = $dir . '/Лебер Фото-отчет о монтаже ' . $mafInfo['NAME'] . ' (компакт).docx';
    $reportCompact = $dir . '/Лебер Фото-отчет о монтаже ' . $mafInfo['NAME'] . ' (компакт).pdf';

    $typeReport = 'full';
    if (isset($_GET['type']) && !empty($_GET['type'])) {
        $typeReport = trim($_GET['type']);
    }

    //$m = $searchMaf['results'][0];

    $icon_comment_path = dirname(dirname(__DIR__)) . '/scripts/images/comment.png';
    $icon_border_path = dirname(dirname(__DIR__)) . '/scripts/images/border-image.jpg';
    $icon_film_path = dirname(dirname(__DIR__)) . '/scripts/images/film.png';
    $icon_text_video_big_path = dirname(dirname(__DIR__)) . '/scripts/images/text_video_big.png';
    $icon_text_video_path = dirname(dirname(__DIR__)) . '/scripts/images/text_video.png';

    $zip = new \PhpOffice\PhpWord\Shared\ZipArchive();
    $fileToModify = 'word/_rels/document.xml.rels';

    if ($typeReport == 'full') {
        $template = $templateFull;
        $temp_file = $fileFull;
    } else {
        $template = $templateCompact;
        $temp_file = $fileCompact;
    }

    copy($template, $temp_file);
    if ($zip->open($temp_file) === TRUE) {
        //Read contents into memory
        $oldContents = $zip->getFromName($fileToModify);
        $oldContents = str_replace('ORDERID', $mafInfo['PROPERTY_ORDER_VALUE'], $oldContents);
        $oldContents = str_replace('MAFID', $mafInfo['ID'], $oldContents);
        //$zip->deleteName($fileToModify);
        $zip->addFromString($fileToModify, $oldContents);
        $return = $zip->close();
    }

    $style = [
        'size' => 20,
        'name' => 'Arial',
        'color' => '0D6EFD',
        'underline' => 'single'
    ];

    $document = new \PhpOffice\PhpWord\TemplateProcessor($temp_file);

    /*$inline = new \PhpOffice\PhpWord\Element\TextRun();
    $inline->addFontStyle('Link', array('color' => '0D6EFD', 'underline' => 'single', 'size'=>20, 'bold' => true));
    $inline->addTitleStyle('Link', array('color' => '0D6EFD', 'underline' => 'single', 'size'=>20, 'bold' => true));
    $link = $inline->addLink('https://github.com/', $mafInfo['NAME'], 'Link', $mafInfo['NAME']);*/
    /*$phpWord = new \PhpOffice\PhpWord\PhpWord();
    $phpWord->addTitleStyle(1, array('size' => 16, 'bold' => true));
    $phpWord->addFontStyle('Link', array('color' => '0D6EFD', 'underline' => 'single'));
    $section = $phpWord->addSection();
    $textrun = $section->addTextRun('Heading1');
    $link = $textrun->addLink('https://github.com/PHPOffice/PHPWord', $mafInfo['NAME'], 'Link');*/


    //$phpWord = new \PhpOffice\PhpWord\PhpWord();
    //$phpWord->addFontStyle('Link', array('color' => '0000FF', 'underline' => 'single'));
    //$section = $phpWord->addSection();
    //$textrun = $section->addTextRun('Heading1');
    //$textrun->addText('The ');
    //$textrun->addLink('https://github.com/PHPOffice/PHPWord', 'PHPWord', 'Link');// Link
    //$section->addLink('https://github.com/', 'GitHub', 'Link', 'Heading2');
    //https://service.leber.group/s/gallery/?maf_id=${maf_id}
    //https://service.leber.group/s/gallery/?maf_id=${maf_p_id}
    //https://lebergroup.ru/orderjump/${o_p}
    //$document->setValue('${maf}', $mafInfo['NAME']);


    //$document->setValue('${maf_id}', $mafInfo['ID']);
    //$document->setValue('$maf_id', $mafInfo['ID']);
    //$document->setComplexValue('maf', $link);

    $document->setValue('${maf}', $mafInfo['NAME']);
    $document->setValue('${date}', setDateFormat($mafInfo['DATE_CREATE'], 'd.m.Y, H:i'));
    if (empty($mafInfo['PROPERTY_ADDRESS_VALUE'])) {
        $document->setValue('${address}', "");
        $document->setValue('${address_no}', "не указан");
    } else {
        $document->setValue('${address}', $mafInfo['PROPERTY_ADDRESS_VALUE']);
        $document->setValue('${address_no}', "");
    }

    if (empty($mafInfo['PROPERTY_ORDER_VALUE'])) {
        $document->setValue('${order}', "");
        $document->setValue('${order_no}', "не указан");
    } else {
        $document->setValue('${order}', $mafInfo['PROPERTY_ORDER_VALUE']);
        $document->setValue('${order_no}', "");
    }

    if ($mafInfo['MANAGER']) {
        $document->setValue('${manager}', trim($mafInfo['MANAGER']));
        $document->setValue('${manager_no}', "");
    } else {
        $document->setValue('${manager}', "");
        $document->setValue('${manager_no}', "не указан");
    }

    $document->setValue('${total_photo}', $totalPhoto);
    if ($totalVideo > 0) {
        $document->setValue('${total_video}', "Видео: " . $totalVideo);
        $document->setValue('${space}', "</w:t></w:r><w:r><w:br/></w:r><w:r><w:t>");
    } else {
        $document->setValue('${total_video}', "");
        $document->setValue('${space}', "");
    }

    if ($typeReport == 'full') {
        $document->cloneBlock('block_name', $totalPhotoVideo, true, true);

        //$phpWord = new \PhpOffice\PhpWord\PhpWord();
        //$phpWord->addTitleStyle(1, array('size' => 16, 'bold' => true));
        //$phpWord->addTitleStyle(2, array('size' => 14, 'bold' => true));
        //$phpWord->addFontStyle('Link', array('color' => '0000FF', 'underline' => 'single'));
        //$section = $phpWord->addSection();
        //$textrun = $section->addTextRun('Heading1');// Link
        //$t = $section->addLink('https://github.com/', 'GitHub', 'Link');

        //$comment= new \PhpOffice\PhpWord\Element\Comment('my_initials', new \DateTime(), 'my_initials');
        //$comment->addText('Test', array('bold' => true));

        //$phpWord = new \PhpOffice\PhpWord\PhpWord();
// add it to the document
        //$text = $phpWord->addComment($comment);
        //$section = $phpWord->addSection();
        //$textrun = $section->addTextRun();
        // $textrun->addText('This ');
        //$text = $textrun->addText('is');
        //$document->setValue('${Heading1}', $t);

// link the comment to the text you just created
        //$text->setCommentStart($comment);
    } else {
        $document->cloneBlock('block_compact', ($totalPageCompact - 1), true, true);
    }


    $allInstallersPageFour = [];
    $start = 1;
    $startPage = 1;
    //pre($photos);
    //exit();
    foreach ($allInstallers as $key => $p) {
        $number = $key + 1;

        if ($number == $countPreview && $extraPhoto > 0)
        {
            $document->setValue('${image' . $countPreview . '}', '+' . $extraPhoto);
            //break;
        }
        elseif ($number <= $countPreview)
        {
            if ($p['type'] == 'video')
            {
                $p['path'] = $p['image'];
            }
            $mimeType = explode(".", $p['path']);
            $end = strtolower(end($mimeType));
            if ($end == "jpeg") {
                $end = "jpg";
            }

            $filename = __DIR__ . '/reports/' . $USER['id'] . '_' . $maf_id . '_' . $key . '.' . $end;

            /*if(file_exists($filename) === false)
            {
                pre($filename);
            }*/

            //pre($fullPath . $p['path']);

            $pathForImage = $fullPath . $p['path'];
            if ($p['type'] == 'video')
            {
                $pathForImage = $p['path'];
            }

            $thumb = new Thumbs($pathForImage);
            $thumb->thumb(89, 89);

            $thumb->save($filename);


            $image = imagecreatefromjpeg($icon_border_path);
            if ($end == 'png') {
                $watermark = imagecreatefrompng($filename);
            } else {
                $watermark = imagecreatefromjpeg($filename);
            }


            if ($p['type'] == 'video') {
                //затемнение
                $black = imagecolorallocatealpha($watermark, 0, 0, 0, 65);
                imagefilledrectangle($watermark, 0, 0, 89, 89, $black);
            }

            imagealphablending($watermark, false);
            imagesavealpha($watermark, true);
            $width = imagesx($watermark);
            $height = imagesy($watermark);
            imagecopymerge($image, $watermark, 4, 4, 0, 0, $width, $height, 100);


            if ($p['type'] == 'video') {
                $icon_film = imagecreatefrompng($icon_film_path);
                imagesavealpha($icon_film, true);
                $transparent = imagecolorallocatealpha($icon_film, 0, 0, 0, 127);
                imagefill($icon_film, 0, 0, $transparent);
                imagecopy($image, $icon_film, 36, 36, 0, 0, 25, 25);
            }

            imagepng($image, $filename);
            imagedestroy($image);

            /*$ix=4;  // x координата вставки текста
            $iy=4;  // y координата вставки текста
            imageString($image, 3, $ix, $iy, "watermark", 0xFF00FF);
            //вывод получившейся картинки
            header('Content-type: image/png');
            imagepng($image);
            imagedestroy($image);*/

            /*if ($p['type'] == 'video')
            {
                $image = imagecreatefrompng($filename);

                $icon_film = imagecreatefrompng($icon_film_path);
                imagesavealpha($icon_film, true);
                $transparent = imagecolorallocatealpha($icon_film, 0, 0, 0, 127);
                imagefill($icon_film, 0, 0, $transparent);

                //$width = imagesx($icon_film);
                //$height = imagesy($icon_film);
                //$dest_x = 36;
                //$dest_y = 36;

                imagecopy($image, $icon_film, 36, 36, 0, 0, 25, 25);
                imagepng($image, $filename);
                imagedestroy($image);
            }*/

            $document->setImageValue('image' . $number, array('path' => $filename, 'width' => 97, 'height' => 97));
            unlink($filename);
        }

        $elementIds = [];

        if ($typeReport == 'full') {

            $document->setValue('${maf_p#' . $number . '}', $mafInfo['NAME']);
            $document->setValue('${maf_p_id#' . $number . '}', $mafInfo['ID']);
            if (empty($mafInfo['PROPERTY_ORDER_VALUE'])) {
                $document->setValue('${o_p#' . $number . '}', "");
                $document->setValue('${o_no_p#' . $number . '}', "не указан");
            } else {
                $document->setValue('${o_p#' . $number . '}', $mafInfo['PROPERTY_ORDER_VALUE']);
                $document->setValue('${o_no_p#' . $number . '}', "");
            }
            //$templateProcessor->setValue('link', "<a href='google.com'>click here</a>");

            $link = "https://service.leber.group/s/gallery/?maf_id=" . $mafInfo['ID'] . "&e=" . $p['id'];
            //$link = "https://e".$p['id'];//."</w:t></w:r><w:r><w:br/></w:r><w:r><w:t>";

            //$inline = new \PhpOffice\PhpWord\Element\TextRun();
            //$inline->addLink($link, $number.' из '.$totalPhotoVideo);
            //$inline->addLink($link);
            //$document->setComplexValue('photo#'.$number, $inline);
            //$document->setValue('${photo#'.$number.'}', 1);
            $document->setValue('${photo#' . $number . '}', $number . ' из ' . $totalPhotoVideo);


            //$inline->addText($link, $style);
            //$inline->addTextBreak(1);
            //$document->setComplexValue('photo#'.$number, $inline);


            /*$pw = new \PhpOffice\PhpWord\PhpWord();
            $section = $pw->addSection();
            $textrun = $section->addTextRun();
            $textrun->addLink($link, $number.' из '.$totalPhotoVideo, $style);*/

            //$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($pw);
            //$fullXml = $objWriter->getWriterPart();
            //$document->setComplexValue('photo#'.$number, $textrun);

            //$document->setValue('${photo#'.$number.'}', $link);
            //
            //$document->setValue('${photo#'.$number.'}', "<a href='/google.com'>".$number." из ".$totalPhotoVideo."</a>");


            //$toOpenXML = HTMLtoOpenXML::getInstance()->fromHTML('<a href="">'.$number.' из '.$totalPhotoVideo.'</a>');
            //$toOpenXML = HTMLtoOpenXML::getInstance()->fromHTML("<a href=\"https://service.local.leber/s/gallery/?maf_id=217694\">".$number." из ".$totalPhotoVideo."</a>");
            //pre($toOpenXML);
            //exit($toOpenXML);

            //$toOpenXML = '</w:t></w:r><w:r><ulink url="https://service.local.leber/s/gallery/?maf_id=217694&e=123">'.$number.' из '.$totalPhotoVideo.'</ulink></w:r><w:r><w:t>';
            //$document->setValue('${photo#'.$number.'}', $toOpenXML);
            $elementIds[$number] = $p['id'];

            $document->setValue('${uploader#' . $number . '}', $p['uploader']);
            $document->setValue('${photo_date#' . $number . '}', setDateFormat($p['date'], 'd.m.Y, H:i'));

            $filenameList = __DIR__ . '/reports/' . $USER['id'] . '_' . $maf_id . '_' . $key . '_list.' . $end;


            $pathForImage = $fullPath . $p['path'];
            if ($p['type'] == 'video')
            {
                $pathForImage = $p['path'];
            }

            $thumb = new Thumbs($pathForImage);
            //$thumb = new Thumbs($fullPath . $p['path']);
            $thumb->reduce(705, 600);

            $thumb->save($filenameList);
            if ($end == 'png') {
                $image = imagecreatefrompng($filenameList);
            } else {
                $image = imagecreatefromjpeg($filenameList);
            }

            //обводка картинки
            $border_image = __DIR__ . '/reports/' . $USER['id'] . '_' . $maf_id . '_' . $key . '_border.jpg';

            //блок с рамкой
            /*roundRectangle($border_image, (imagesx($image)+8), (imagesy($image)+8));
            $imageBorder = imagecreatefromjpeg($border_image);*/

            if ($p['type'] == 'video') {
                $black = imagecolorallocatealpha($image, 0, 0, 0, 65);
                imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $black);
                imagesavealpha($image, true);
                $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
                imagefill($image, 0, 0, $transparent);

                $icon_film = imagecreatefrompng($icon_text_video_big_path);
                imagesavealpha($icon_film, true);
                $transparent = imagecolorallocatealpha($icon_film, 0, 0, 0, 127);
                imagefill($icon_film, 0, 0, $transparent);
                imagecopy($image, $icon_film, (imagesx($image) - 246) / 2, (imagesy($image) - 135) / 2, 0, 0, 246, 132);
                imagedestroy($icon_film);

            }

            //блок с рамкой
            /*imagecopymerge($imageBorder, $image, 4, 4, 0, 0, imagesx($image), imagesy($image), 100);
            imagepng($imageBorder, $filenameList);
            imagedestroy($imageBorder);
            imagedestroy($image);
            unlink($border_image);*/

            //блок без рамки
            imagepng($image, $filenameList);
            imagedestroy($image);

            $document->setImageValue('image_full#' . $number, array('path' => $filenameList, 'width' => 705, 'height' => 600));
            unlink($filenameList);


            if (empty($p['comment'])) {
                $document->setValue('${commentator#' . $number . '}', "");
                $document->setValue('${comment#' . $number . '}', "");
                $document->setValue('${icon_comment#' . $number . '}', "");
            } else {
                $commentator = $userEditContent[$p['whoAddComment']];
                if (!empty($p['whoEditComment']) && $p['whoEditComment'] != $p['whoAddComment']) {
                    $commentator .= ', ред. ' . $userEditContent[$p['whoEditComment']];
                }

                $commentText = trim($p['comment']);
                $strlenComment = strlen($commentText);

                if ($strlenComment > 1250) {
                    list($widthImage, $heightImage, $typeImage, $attrImage) = getimagesize($fullPath . $p['path']);
                    if ($widthImage > $heightImage) {
                        if ($strlenComment > 2150) {
                            $commentText = substr($commentText, 0, 2150);
                            $slice = explode(" ", $commentText);
                            unset($slice[count($slice) - 1]);

                            $slice[count($slice) - 1] = preg_replace("#[[:punct:]]#", "", $slice[count($slice) - 1]);
                            $commentText = implode(" ", $slice) . "...";
                        }

                    } else {
                        $commentText = substr($commentText, 0, 1250);
                        $slice = explode(" ", $commentText);
                        unset($slice[count($slice) - 1]);
                        $slice[count($slice) - 1] = preg_replace("#[[:punct:]]#", "", $slice[count($slice) - 1]);
                        $commentText = implode(" ", $slice) . "...";
                    }

                }

                $document->setImageValue('icon_comment#' . $number, array('path' => $icon_comment_path, 'width' => 24, 'height' => 23));
                $document->setValue('${commentator#' . $number . '}', ' Комментарий (' . $commentator . '):');
                $document->setValue('${comment#' . $number . '}', $commentText);
            }
        } else {
            $allInstallersPageFour[$startPage][] = $p;
            $start++;
            if ($start == 5) {
                $start = 1;
                $startPage++;
            }
        }
    }

    if ($countPreview > $totalPhotoVideo) {
        for ($i = ($totalPhotoVideo + 1); $i <= $countPreview; $i++) {
            $document->setValue('${image' . $i . '}', '');
        }
    }
    $qrFilename = __DIR__ . '/reports/' . $USER['id'] . '_' . $maf_id . '_qr.png';
    QRcode::png('https://service.leber.group/s/gallery/?maf_id=' . $maf_id, $qrFilename, 'L', 3, 0);

    //$document->setImageValue2('image1.png', $qrFilename);
    $document->setImageValue('qr', array('path' => $qrFilename, 'width' => 97, 'height' => 97));
    unlink($qrFilename);


    if ($typeReport != 'full') {
        $numberPhoto = 1;
        foreach ($allInstallersPageFour as $pageNum => $pageItem) {
            //$left = 4-$countPhotoForPage;
            $document->setValue('${maf_p#' . $pageNum . '}', $mafInfo['NAME']);
            $document->setValue('${maf_p_id#' . $pageNum . '}', $mafInfo['ID']);
            $document->setValue('${num_page#' . $pageNum . '}', ($pageNum + 1));
            $document->setValue('${total_page#' . $pageNum . '}', $totalPageCompact);
            if (empty($mafInfo['PROPERTY_ORDER_VALUE'])) {
                $document->setValue('${o_p#' . $pageNum . '}', "");
                $document->setValue('${o_no_p#' . $pageNum . '}', "не указан");
            } else {
                $document->setValue('${o_p#' . $pageNum . '}', $mafInfo['PROPERTY_ORDER_VALUE']);
                $document->setValue('${o_no_p#' . $pageNum . '}', "");
            }
            $kNum = 1;
            foreach ($pageItem as $p) {
                $commentText = trim($p['comment']);
                $strlenComment = strlen($commentText);

                list($widthImage, $heightImage, $typeImage, $attrImage) = getimagesize($fullPath . $p['path']);

                if (empty($commentText)) {
                    $document->setValue('${commentator' . $kNum . '#' . $pageNum . '}', "");
                    $document->setValue('${comment' . $kNum . '#' . $pageNum . '}', "");
                } else {
                    $fullComment = '';

                    $totalStrComment = 8;

                    if ($widthImage > $heightImage) {
                        $totalStrComment = 12;
                    }


                    $commentator = $userEditContent[$p['whoAddComment']];
                    if (!empty($p['whoEditComment']) && $p['whoEditComment'] != $p['whoAddComment']) {
                        $commentator .= ', ред. ' . $userEditContent[$p['whoEditComment']];
                    }
                    $commentator .= ': ';

                    $fullComment = $commentator . $commentText;

                    //высчитываем, сколько строчек комментария получается
                    $realStrComment = 1;
                    $strlenStrComment = 0;
                    $viewComment = [];
                    $fullComment = explode(" ", $fullComment);
                    for ($i = 0; $i < count($fullComment); $i++) {
                        if ($realStrComment > $totalStrComment) {
                            break;
                        }

                        $strlenStrComment += strlen($fullComment[$i]) + 1;
                        if ($strlenStrComment < 93) {
                            $viewComment[] = $fullComment[$i];
                        } else {
                            $strlenStrComment = 0;
                            $realStrComment++;
                            $i = $i - 1;
                        }
                    }


                    if ($realStrComment > $totalStrComment) {
                        $viewComment[count($viewComment) - 1] = preg_replace("#[[:punct:]]#", "", $viewComment[count($viewComment) - 1]);
                        //$fullComment .= "...";
                    }

                    $commentText = implode(" ", $viewComment);
                    $commentText = trim(str_replace($commentator, "", $commentText));

                    if ($realStrComment > $totalStrComment) {
                        $commentText .= "...";
                    }

                    /*if($strlenComment > 650)
                    {

                        if($widthImage > $heightImage)
                        {
                            if($strlenComment > 1000)
                            {
                                $commentText = substr($commentText, 0, 1000);
                                $slice = explode(" ", $commentText);
                                unset($slice[count($slice)-1]);

                                $slice[count($slice)-1] = preg_replace("#[[:punct:]]#", "", $slice[count($slice)-1]);
                                $commentText = implode(" ", $slice)."...";

                                //$inline = new \PhpOffice\PhpWord\Element\TextRun();
                                //$inline->addText($commentText);
                                //$inline->addLink($link, "...");
                                //$document->setComplexValue('comment'.$kNum.'#'.$pageNum, $inline);/
                                //$document->setValue('${comment'.$kNum.'#'.$pageNum.'}', $commentText);
                                //$inline->addText($link, $style);
                            }

                        }
                        else
                        {
                            $commentText = substr($commentText, 0, 650);
                            $slice = explode(" ", $commentText);
                            unset($slice[count($slice)-1]);
                            $slice[count($slice)-1] = preg_replace("#[[:punct:]]#", "", $slice[count($slice)-1]);
                            $commentText = implode(" ", $slice)."...";
                            //$document->setValue('${comment'.$kNum.'#'.$pageNum.'}', $commentText);
                        }

                    }
                    else
                    {
                        //$document->setValue('${comment'.$kNum.'#'.$pageNum.'}', $commentText);
                    }*/
                    $document->setValue('${comment' . $kNum . '#' . $pageNum . '}', $commentText);
                    $document->setValue('${commentator' . $kNum . '#' . $pageNum . '}', $commentator);

                }

                //$document->setValue('${p'.$kNum.'#'.$pageNum.'}', 'Элемент '.$numberPhoto.'/'.$totalPhotoVideo);
                $document->setValue('${p' . $kNum . '#' . $pageNum . '}', $numberPhoto . ' / ' . $totalPhotoVideo);
                $document->setValue('${pDate' . $kNum . '#' . $pageNum . '}', setDateFormat($p['date'], 'd.m.Y, H:i'));
                //$document->setValue('${pDate'.$kNum.'#'.$pageNum.'}', 'от '.setDateFormat($p['date'], 'd.m.Y, H:i'));
                $document->setValue('${pUploader' . $kNum . '#' . $pageNum . '}', 'Загрузил: ' . $p['uploader']);
                //$document->setImageValue('pImage'.$kNum.'#'.$pageNum, array('path' => $p['path'], 'width' => 320, 'height'=> 260));

                $mimeType = explode(".", $p['path']);
                $end = strtolower(end($mimeType));
                if ($end == "jpeg") {
                    $end = "jpg";
                }

                $filenameList4 = __DIR__ . '/reports/' . $USER['id'] . '_' . $maf_id . '_' . $pageNum . '_' . $kNum . '_list4.' . $end;

                $tW = 270;
                $tH = 270;
                if ($widthImage > $heightImage) {
                    $tW = 305;
                    $tH = 305;
                }
                if ($widthImage < $heightImage) {
                    if (empty($commentText)) {
                        $tW = 400;
                        $tH = 400;
                    } else {
                        switch ($realStrComment) {
                            case "1" :
                                $tW = $tH = 384;
                                break;
                            case "2" :
                                $tW = $tH = 368;
                                break;
                            case "3" :
                                $tW = $tH = 352;
                                break;
                            case "4" :
                                $tW = $tH = 336;
                                break;
                            case "5" :
                                $tW = $tH = 320;
                                break;
                            case "6" :
                                $tW = $tH = 304;
                                break;
                            case "7" :
                                $tW = $tH = 288;
                                break;
                        }
                    }
                }

                $pathForImage = $fullPath . $p['path'];
                if ($p['type'] == 'video')
                {
                    $pathForImage = $p['path'];
                }

                $thumb = new Thumbs($pathForImage);
                //$thumb = new Thumbs($fullPath . $p['path']);
                $thumb->reduce($tW, $tH);
                $thumb->save($filenameList4);

                if ($end == 'png') {
                    $image = imagecreatefrompng($filenameList4);
                } else {
                    $image = imagecreatefromjpeg($filenameList4);
                }

                //обводка картинки
                $border_image4 = __DIR__ . '/reports/' . $USER['id'] . '_' . $maf_id . '_' . $key . '_border4.jpg';

                //блок с рамкой
                /*roundRectangle($border_image4, (imagesx($image)+8), (imagesy($image)+8));
                $imageBorder4 = imagecreatefromjpeg($border_image4);*/

                if ($p['type'] == 'video') {
                    $black = imagecolorallocatealpha($image, 0, 0, 0, 65);
                    imagefilledrectangle($image, 0, 0, imagesx($image), imagesy($image), $black);
                    imagesavealpha($image, true);
                    $transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
                    imagefill($image, 0, 0, $transparent);

                    $icon_film = imagecreatefrompng($icon_text_video_path);
                    imagesavealpha($icon_film, true);
                    $transparent = imagecolorallocatealpha($icon_film, 0, 0, 0, 127);
                    imagefill($icon_film, 0, 0, $transparent);
                    imagecopy($image, $icon_film, (imagesx($image) - 157) / 2, (imagesy($image) - 119) / 2, 0, 0, 157, 119);
                    imagedestroy($icon_film);
                }


                //блок с рамкой
                /*imagecopymerge($imageBorder4, $image, 4, 4, 0, 0, imagesx($image), imagesy($image), 100);
                imagepng($imageBorder4, $filenameList4);
                imagedestroy($imageBorder4);
                imagedestroy($image);
                unlink($border_image4);
                //$tW += 8;
                //$tH += 8;
                */

                //блок без рамки
                imagepng($image, $filenameList4);
                imagedestroy($image);

                $document->setImageValue('pImage' . $kNum . '#' . $pageNum, array('path' => $filenameList4, 'width' => $tW, 'height' => $tH));
                unlink($filenameList4);

                $kNum++;
                $numberPhoto++;
            }
        }

        $countPhotoForLastPage = count(end($allInstallersPageFour));
        if ($countPhotoForLastPage < 4) {
            $pageNum = count($allInstallersPageFour);
            for ($i = ($countPhotoForLastPage + 1); $i <= 4; $i++) {
                $document->setValue('${p' . $i . '#' . $pageNum . '}', "");
                //$document->setValue('${pA'.$i.'#'.$pageNum.'}', "");
                $document->setValue('${pDate' . $i . '#' . $pageNum . '}', "");
                $document->setValue('${pUploader' . $i . '#' . $pageNum . '}', "");
                $document->setValue('${pImage' . $i . '#' . $pageNum . '}', "");
                $document->setValue('${commentator' . $i . '#' . $pageNum . '}', "");
                $document->setValue('${comment' . $i . '#' . $pageNum . '}', "");
            }


        }
    }

    $fileForSave = $fileFull;
    $fileForReport = $reportFull;
    if ($typeReport != 'full') {
        $fileForSave = $fileCompact;
        $fileForReport = $reportCompact;
    }

    $document->saveAs($fileForSave);


    /*$zip = new \PhpOffice\PhpWord\Shared\ZipArchive();
    $fileToModify = 'word/document.xml';
    $var = 1;
    if ($zip->open($fileForSave) === TRUE)
    {
        $oldContents = $zip->getFromName($fileToModify);
        //pre($oldContents);

        $countLink = preg_match_all('/<w:hyperlink r:id="rId10" w:history="1">/', $oldContents, $matches);
        for ($i = 1; $i <= $countLink; $i++)
        {
            $oldContents = str_replace('rId10', 'rId10'.$i.'00', $oldContents, $var);
        }
        $zip->addFromString($fileToModify, $oldContents);
        $zip->close();
    }*/

    /*$zip = new \PhpOffice\PhpWord\Shared\ZipArchive();
    $fileToModify = 'word/_rels/document.xml.rels';

    if ($zip->open($fileForSave) === TRUE)
    {
        $oldContents = $zip->getFromName($fileToModify);
        //pre($oldContents);
        //exit();

        $Relationship = '';
        for ($i = 1; $i <= $countLink; $i++)
        {
            $Relationship .= '<Relationship Id="rId10'.$i.'00" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="https://service.leber.group/s/gallery/?maf_id='.$mafInfo['ID'].'&e='.$elementIds[$i].'" TargetMode="External"/>';
        }

        $oldContents = str_replace('<Relationship Id="rId10" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="https://service.leber.group/s/gallery/?maf_id=ELID" TargetMode="External"/>', $Relationship, $oldContents);

        $zip->addFromString($fileToModify, $oldContents);
        $zip->close();
    }*/

    /*$zip = new \PhpOffice\PhpWord\Shared\ZipArchive();
    $fileToModify = 'word/_rels/document.xml.rels';

    if ($zip->open($fileForSave) === TRUE)
    {
        //Read contents into memory
        $oldContents = $zip->getFromName($fileToModify);
        exit($oldContents);
        //$oldContents = str_replace('ORDERID', $mafInfo['PROPERTY_ORDER_VALUE'], $oldContents);
        //$oldContents = str_replace('MAFID', $mafInfo['ID'], $oldContents);
        //$zip->deleteName($fileToModify);
        //$zip->addFromString($fileToModify, $oldContents);
        $return =$zip->close();
    }*/

    //$convertfile = 'export HOME=/tmp/ && /usr/bin/libreoffice --headless --writer --convert-to docx "'.$fContentHtml.'" --outdir "'.$dir.'"';
    $convertfile = 'export HOME=/tmp/ && /usr/bin/libreoffice --headless --writer --convert-to pdf "' . $fileForSave . '" --outdir "' . $dir . '"';
    //pre($convertfile);
    $res = shell_exec($convertfile);
    unlink($fileForSave);

    $realurl = '';
    if (isset($_GET['url']) && !empty(trim($_GET['url']))) {
        $realurl = trim($_GET['url']);
    }
    $arrlog = [];
    $arrlog['url'] = $realurl;
    if ($typeReport == 'full') {
        $arrlog['log'] = '[report full] Сгенерирован стандартный фотоотчет для ' . $mafInfo['NAME'];
        $arrlog['format'] = 'report full';

    } else {
        $arrlog['log'] = '[report compact] Сгенерирован компактный фотоотчет для ' . $mafInfo['NAME'];
        $arrlog['format'] = 'report compact';
    }
    if (file_exists($fileForReport)) {
        addlogdb($arrlog);

        //header("Content-Type: application/pdf");
        //echo file_get_contents($fileForReport);

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileForReport) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fileForReport));

        readfile($fileForReport);
    } else {
        $arrlog['type'] = 'error';
        addlogdb($arrlog);

        http_response_code(400);
        exit("Ошибка генерации фотоотчета");
    }
    //pre($res);
    //unlink($qrFilename);
    ?>

    <?
    exit();
}


$order = "";
$manager = "";
$address = "";
$unit = "";
if ($mafInfo['PROPERTY_ADDRESS_VALUE']) {
    $address = $mafInfo['PROPERTY_ADDRESS_VALUE'];
}
if ($mafInfo['PROPERTY_ORDER_VALUE']) {
    $order = $mafInfo['PROPERTY_ORDER_VALUE'];
}
if ($mafInfo['MANAGER']) {
    $manager = $mafInfo['MANAGER'];
}
if ($mafInfo['PROPERTY_UNIT_FROM_NAME']) {
    $unit = $mafInfo['PROPERTY_UNIT_FROM_NAME'];
}
?>
<style>
    .elementInActive {
        margin: 0 auto;
        border-radius: 4px;
        border: 1px solid #DEE2E6;
        width: 150px !important;
        height: 150px !important;
        padding: 4px;
        position: relative;
    }

    .elementActive {
        border: 4px solid #0D6EFD;
        padding: 0px;
    }

    .elementIndexInActive {
        background-color: #FFFFFF;
        color: #343A40;
        width: 35px;
        height: 35px;
        border-top-right-radius: 35px;
    }

    .elementIndexActive {
        background-color: #0D6EFD;

    }

    .elementIndexActive span {
        color: #FFFFFF !important;
    }

    .boxActionsInActive {
        bottom: 0;
        right: 0;
        width: 100%;
        height: 35px;
        padding-top: 10px;
        background-color: #FFFFFF;
    }

    .boxActionsActive, .boxActionsActive i {
        background-color: #0D6EFD;
        color: #FFFFFF !important;
    }
</style>

<input type="hidden" id="setElement" value="<?= $e ?>">
<input type="hidden" id="accessForAction" value="<?= $fullAccess ?>">
<input type="hidden" id="currentPhotoSort" value="<?= $handsort ?>">
<input type="hidden" id="currentMafId" value="<?= $maf_id ?>">
<input type="hidden" id="currentMafFullMane" value="<?= $mafInfo['NAME'] ?>">
<input type="hidden" id="typeMovePhoto" value="all">
<input type="hidden" id="typeMovePhotoId" value="0">

<div class="row">

    <div class="col colFlex">
        <div>
            <div class="d-table-cell pe-2" style="width: 1%">
                <div style="position: relative; top: -8px;">
                    <a class="btn btn-blue-white" href="/s/gallery/" role="button"><i title="Назад к заявкам"
                                                                                      class="bi bi-arrow-left  color-blue ms-1 me-1 fs-22"></i></a>
                </div>
            </div>

            <div class="d-table-cell pe-2" style="width: 1%">
                <div class="dropdown" style="position: relative; top: -8px;">
                    <a id="buttonDownload" class="btn btn-primary dropdown-toggle fs-16" href="#" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false" style="padding-top: 8px; padding-bottom: 7px;">
                        Скачать
                    </a>

                    <ul class="dropdown-menu">
                        <li>
                            <a onclick="createPdfFilePhotoBot(<?= $maf_id ?>, '<?= $mafInfo['NAME'] ?>', 'full'); return false;"
                               class="dropdown-item" href="#">Стандартный отчет</a></li>
                        <li>
                            <a onclick="createPdfFilePhotoBot(<?= $maf_id ?>, '<?= $mafInfo['NAME'] ?>', 'compact'); return false;"
                               class="dropdown-item" href="#">Компактный отчет</a></li>
                    </ul>
                </div>
                <? /*<div style="position: relative; top: -8px;">
                            <a onclick="createPdfFilePhotoBot(<?=$maf_id?>, '<?=$mafInfo['NAME']?>'); return false;" class="btn btn-blue-white" href="#" role="button"><i title="Скачать презентацию" class="bi bi-journal-arrow-down color-blue ms-1 me-1 fs-22"></i></a>
                        </div>*/ ?>
            </div>
            <? /*
                    <div class="d-table-cell pe-2" style="width: 1%">
                        <div style="position: relative; top: -8px;">
                            <a class="btn btn-blue-white" href="#" role="button"><i title="Скачать презентацию" class="bi bi-journal-richtext color-blue ms-1 me-1 fs-22"></i></a>
                        </div>
                    </div>
                    */ ?>

            <div class="d-table-cell galleryH1 text-nowrap text-start">
                <?= $mafInfo['NAME'] ?> <span
                        class="galleryInfoText fs-16">от <?= setDateFormat($mafInfo['DATE_CREATE'], 'd.m.Y, H:i') ?></span>
            </div>
        </div>
        <br>
        <div class="row">
            <? if ($order): ?>
                <div class="galleryInfoText fs-16">Заказ: <a class="fs-16" target="_blank"
                                                             href="https://lebergroup.ru/orderjump/<?= $order ?>"><?= $order ?></a>
                </div><? endif; ?>
            <? if ($address): ?>
                <div class="galleryInfoText fs-16">Адрес: <?= $address ?></div><? endif; ?>
            <? if ($manager): ?>
                <div class="galleryInfoText fs-16">Менеджер: <?= $manager ?></div><? endif; ?>
            <? if ($unit): ?>
                <div class="galleryInfoText fs-16">Подразделение: <?= $unit ?></div><? endif; ?>
        </div>
        <br>
        <? if ($fullAccess === true && $is_mobile === false): ?>

            <div style="width: 100%">
                <div class="d-table-cell">
                    <div class="galleryInfoText text-nowrap">
                        <input onclick="allPhotoChecked()" id="allPhotoChecked"
                               style="border-radius: 2px !important; border: 1px solid #CED4DA;" type="checkbox"
                               class="form-check-input">&nbsp;&nbsp;
                        <label for="allPhotoChecked">
                            <span class="fs-16 galleryInfoText text-nowrap">Выделить все (<?= count($allInstallers) ?>)</span>
                        </label>
                    </div>
                </div>
                <div class="d-table-cell" style="width: 1%">
                    <div class="col text-end me-2" style="position: relative; top: -8px;">
                        <a onclick="moveFilePhotoBot(); return false;" class="btn btn-blue-white" href="" role="button"><i
                                    title="Переместить в другую заявку"
                                    class="bi bi-arrow-left-right fs-22 color-blue ms-1 me-1"></i></a>
                    </div>
                </div>
                <div class="d-table-cell" style="width: 1%">
                    <div class="col text-end me-2" style="position: relative; top: -8px;">
                        <a onclick="delFilePhotoBot(); return false;" class="btn btn-red-white" href="" role="button"><i
                                    title="Удалить выделенные фото"
                                    class="bi bi-trash fs-22 color-red ms-1 me-1"></i></a>
                    </div>
                </div>
                <div class="d-table-cell" style="width: 1%">
                    <div class="text-nowrap" style="position: relative; top: -8px;">
                        <select title="Сортировка" onchange="sortPhotoGallery(this.value)"
                                class="fs-16 form-select gallery-select-sort">
                            <option value="hand"
                                    class="fs-16 text-nowrap" <? if (empty($handsort) || $handsort == 'hand') echo "selected"; ?>>
                                Вручную
                            </option>
                            <option value="new"
                                    class="fs-16 text-nowrap" <? if ($handsort == 'new') echo "selected"; ?>>Сначала
                                новые
                            </option>
                            <option value="old"
                                    class="fs-16 text-nowrap" <? if ($handsort == 'old') echo "selected"; ?>>Сначала
                                старые
                            </option>
                        </select>
                    </div>
                </div>
            </div>


            <br>
        <? endif; ?>

        <? if ($is_mobile === false): ?>
            <div class="row text-center" id="gallerySort">

                <? foreach ($allInstallers as $k => $images):
                    //вычисляем ориентацию картинки
                    $size = 142;
                    $sizePadding = 142;
                    $shortWidth = $size;
                    $shortHeigth = '';
                    $mimeType = explode(".", $images['path']);
                    $end = strtolower(end($mimeType));
                    $videoPath = '';

                    if ($images['type'] != 'video') {
                        $getimagesize = getimagesize($fullPath . $images['path']);

                        $w = $getimagesize[0];
                        $h = $getimagesize[1];

                        $param = "w";
                        $shortSize = ($sizePadding * 100 / $h) / 2;
                        $css = "top: -" . ceil(($h * $shortSize / 100) / 2) . "px;";
                        if ($w > $h) {
                            $param = "h";
                            $shortSize = ($sizePadding * 100 / $w) / 2;
                            $css = "left: -" . ceil(($w * $shortSize / 100) / 2) . "px;";
                        }
                    } else {
                        //$videoPath = $localPath.str_replace($deletePath, "", $images['path']);
                        $videoPath = $localPath . $images['path'];
                        $movie = new ffmpeg_movie($fullPath . $images['path']);
                        $w = $movie->getFrameWidth();
                        $h = $movie->getFrameHeight();
                        $css = '';

                        /*$frame = $movie->getFrame(1);
                        if($frame !== false)
                        {
                            //pre($frame->getWidth());
                            //pre();
                            //$frameImage = new ffmpeg_frame($frame->toGDImage());
                            //pre($frameImage);
                            //exit();
                            //$im = $frame->getR();
                            //header('Content-Type: image/png');
                            //imagepng($im);
                            //imagedestroy($im);
                            //exit();
                            //echo $frame->toGDImage();
                            //$frameImage = new ffmpeg_frame($frame);
                            //$frameImage = $frame->toGDImage();
                        }*/
                        //pre($frame);
                        //file_put_contents($deletePath."/s/gallery/".$images['id'].".png", $frame);

                        $param = "w";
                        //$shortSize = ($sizePadding*100/$h)/2;
                        //$css = "top: -".ceil(($h*$shortSize/100)/2)."px;";
                        if ($w > $h) {
                            $param = "h";
                            $shortWidth = '';
                            $shortHeigth = $size;
                            //$shortSize = ($sizePadding*100/$w)/2;
                            //$css = "left: -".ceil(($w*$shortSize/100)/2)."px;";
                        }
                    }

                    $k++;
                    /*if (isset($_GET['exif']))
                    {
                        //$exif_read_data = exif_read_data($images['path']);
                        //pre($exif_read_data);
                        //pre("-----------");
                    }*/
                    $classHideCommentIcon = 'hide';
                    if (!empty($images['comment'])) {
                        $classHideCommentIcon = '';
                    }
                    $whoAddComment = '';
                    if (!empty($images['whoAddComment'])) {
                        $whoAddComment = $userEditContent[$images['whoAddComment']];
                    }
                    $whoEditComment = '';
                    if (!empty($images['whoEditComment'])) {
                        $whoEditComment = $userEditContent[$images['whoEditComment']];
                    }

                    ?>

                    <div data-path-video="<?= $videoPath ?>" data-type="<?= $images['type'] ?>"
                         data-uploader="<?= $images['uploader'] ?>" data-addcomment="<?= $whoAddComment ?>"
                         data-editcomment="<?= $whoEditComment ?>" data-comment="<?= $images['comment'] ?>"
                         data-date="<?= setDateFormat($images['date'], 'd.m.Y, H:i') ?>" data-number="<?= $k ?>"
                         data-path="/init/resize.php?img=<?= encrypt($fullPath . $images['path']) ?>&w=full"
                         id="getImageGalleryPhotoBot<?= $images['id'] ?>"
                         class="<? if ($fullAccess === false && empty($images['comment'])): ?>comment-block-hide<? endif; ?> col text-center gallery-data-sort"
                         style="margin-bottom: 20px;" data-id="<?= $images['id'] ?>" data-sort="<?= $images['sort'] ?>">

                        <div class="text-center elementInActive" id="box<?= $images['id'] ?>">
                            <? if ($images['type'] == 'video'): ?>
                                <div style="top: 61px; left: 58px; z-index: 2;" class="position-absolute"><i
                                            title="Видео" style="font-size: 28px;"
                                            class="color-white bi bi-play-fill"></i></div>
                            <? endif; ?>
                            <div <? if ($fullAccess === true): ?>onmouseover="showActions(<?= $images['id'] ?>, true)"
                                 onmouseleave="showActions(<?= $images['id'] ?>, false)"<? endif;
                            ?> style="overflow: hidden; position: relative; width: 100%; height: 100%;">

                                <? if ($fullAccess === true): ?>
                                    <div style="top: 2px; left: 6px; z-index: 2;" class="position-absolute">
                                        <input id="inputBox<?= $images['id'] ?>"
                                               onclick="setActive(<?= $images['id'] ?>)"
                                               title="Отметить фото"
                                               style="width:20px; height: 20px; border-radius: 2px !important; border: 1px solid #CED4DA;"
                                               name="selectFile[]" type="checkbox"
                                               class="imageGalleryId form-check-input"
                                               value="<?= $images['id'] ?>">
                                    </div>
                                <? endif; ?>

                                <div id="iconHaveComment<?= $images['id'] ?>" style="top: 5px; right: 5px; z-index: 2;"
                                     class="<?= $classHideCommentIcon ?> position-absolute"><i title="Комментарий"
                                                                                               style="color: #FFC107; font-size: 18px;"
                                                                                               class="bi bi-chat-fill"></i>
                                </div>

                                <div style="bottom: 0px; width: 100%; z-index: 2; <? if ($fullAccess === false): ?>width: 35px;<? endif; ?>"
                                     class="position-absolute">
                                    <div class="position-relative">
                                        <? if ($fullAccess === true): ?>
                                            <div style=""
                                                 class="showActions hide text-end position-absolute boxActionsInActive"
                                                 id="showActions<?= $images['id'] ?>">
                                                <i onclick="moveFilePhotoBot('cursor', <?= $images['id'] ?>); return false;"
                                                   title="Переместить в другую заявку"
                                                   class="bi bi-arrow-left-right fs-16 text-dark cursor-pointer color-blue-hover"></i>
                                                <i onclick="changeCommentPhotoBot(<?= $images['id'] ?>); return false;"
                                                   title="Написать/изменить комментарий"
                                                   class="bi bi-chat-dots fs-16 text-dark cursor-pointer color-blue-hover"></i>
                                                <i onclick="delFilePhotoBot('cursor', <?= $images['id'] ?>); return false;"
                                                   title="Удалить"
                                                   class="bi bi-trash fs-16 text-dark cursor-pointer color-red-hover"></i>
                                            </div>
                                        <? endif; ?>
                                        <div id="boxIndex<?= $images['id'] ?>" class="text-start elementIndexInActive"
                                             style="">
                                        <span class="position-relative text-dark" style="top: 14px; left: 6px;"
                                              id="photoNumber<?= $images['id'] ?>"><?= $k ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div style="<?= $css ?> position: relative; max-width: 100%; max-height: 100%;">
                                    <? if ($images['type'] == 'video'): ?>
                                        <?/*<video class="cursor-pointer" onclick="setImageGalleryPhotoBot(<?=$images['id']?>)" width="<?=$shortWidth?>" height="<?=$shortHeigth?>">
                                    <source src="<?=$videoPath?>">
                                    Видео не поддерживается вашим браузером.
                                </video>*/?>

                                        <!--<a href="/init/resize.php?img=<?/*= encrypt($fullPath . $images['image']) */?>&<?/*= $param */?>=<?/*= $size */?>">-->
                                        <img onclick="setImageGalleryPhotoBot(<?= $images['id'] ?>)"
                                             class="lazy cursor-pointer imgClickList"
                                             src="/init/resize.php?img=<?= encrypt($images['image']) ?>&<?= $param ?>=<?= $size ?>">
                                        <!-- </a>-->
                                    <? else: ?>
                                        <img onclick="setImageGalleryPhotoBot(<?= $images['id'] ?>)"
                                             class="lazy cursor-pointer imgClickList"
                                             src="/init/resize.php?img=<?= encrypt($fullPath . $images['path']) ?>&<?= $param ?>=<?= $size ?>">
                                    <? endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <? endforeach; ?>

                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
                <div class="col">
                    <div style="margin: 0 auto; border: 1px solid transparent; width: 150px !important; height: 0px !important; padding: 4px; position: relative;"></div>
                </div>
            </div>
        <? else: ?>
            <div style="position: absolute; width: 90%">
                <? foreach ($allInstallers as $k => $images):
                    $whoAddComment = '';
                    if (!empty($images['whoAddComment'])) {
                        $whoAddComment = $userEditContent[$images['whoAddComment']];
                    }
                    $whoEditComment = '';
                    if (!empty($images['whoEditComment'])) {
                        $whoEditComment = $userEditContent[$images['whoEditComment']];
                    }
                    ?>
                    <input type="hidden" id="iconHaveComment<?= $k ?>" value="<?= $images['comment'] ?>"
                           data-addcomment="<?= $whoAddComment ?>" data-editcomment="<?= $whoEditComment ?>">
                <? endforeach; ?>
                <div id="gallerySort"></div>
                <div style="width: 100%" class="fotorama-mobile-view fotorama" data-nav="thumbs"
                     data-allowfullscreen="true"
                     data-arrows="true"
                     data-click="true"
                     data-trackpad="true"
                     data-swipe="true"
                     data-autoplay="false"
                     data-loop="true"
                     data-keyboard="true"
                     data-transition="crossfade">
                    <? foreach ($allInstallers as $k => $images):
                        $size = 142;
                        $sizePadding = 142;
                        $shortWidth = $size;
                        $shortHeigth = '';
                        $mimeType = explode(".", $images['path']);
                        $end = strtolower(end($mimeType));
                        $videoPath = '';

                        if ($images['type'] != 'video') {
                            $getimagesize = getimagesize($fullPath . $images['path']);

                            $w = $getimagesize[0];
                            $h = $getimagesize[1];

                            $param = "w";
                            $shortSize = ($sizePadding * 100 / $h) / 2;
                            $css = "top: -" . ceil(($h * $shortSize / 100) / 2) . "px;";
                            if ($w > $h) {
                                $param = "h";
                                $shortSize = ($sizePadding * 100 / $w) / 2;
                                $css = "left: -" . ceil(($w * $shortSize / 100) / 2) . "px;";
                            }
                        } else {
                            //$videoPath = $localPath.str_replace($deletePath, "", $images['path']);
                            $videoPath = $localPath . $images['path'];
                            $movie = new ffmpeg_movie($fullPath . $images['path']);
                            $w = $movie->getFrameWidth();
                            $h = $movie->getFrameHeight();
                            $css = '';

                            /*$frame = $movie->getFrame(1);
                            if($frame !== false)
                            {
                                //pre($frame->getWidth());
                                //pre();
                                //$frameImage = new ffmpeg_frame($frame->toGDImage());
                                //pre($frameImage);
                                //exit();
                                //$im = $frame->getR();
                                //header('Content-Type: image/png');
                                //imagepng($im);
                                //imagedestroy($im);
                                //exit();
                                //echo $frame->toGDImage();
                                //$frameImage = new ffmpeg_frame($frame);
                                //$frameImage = $frame->toGDImage();
                            }*/
                            //pre($frame);
                            //file_put_contents($deletePath."/s/gallery/".$images['id'].".png", $frame);

                            $param = "w";
                            if ($w > $h) {
                                $param = "h";
                                $shortWidth = '';
                                $shortHeigth = $size;
                            }
                        }

                        $thumbURL = "/init/resize.php?img=" . encrypt($fullPath . $images['path']) . "&" . $param . "=" . $size;
                        $previewURL = "/init/resize.php?img=" . encrypt($fullPath . $images['path']) . "&w=full";
                        $detailURL = $fullPath . $images['path'];
                        if ($images['type'] == 'video') {
                            $thumbURL = "/init/resize.php?img=" . encrypt($images['image']) . "&" . $param . "=" . $size;
                            $detailURL = $videoPath;
                            $previewURL = "/init/resize.php?img=" . encrypt($images['image']) . "&w=full";
                        }
                        //pre($detailURL);
                        $commentPicture = '';
                        if (!empty($images['comment'])) {
                            $commentPicture = '<div id="iconHaveComment' . $images['id'] . '" style="top: 5px; right: 5px; z-index: 2;" class=" position-absolute"><i title="Комментарий" style="color: #FFC107; font-size: 18px;" class="bi bi-chat-fill"></i></div>';
                        }
                        ?>


                        <a href="<?= $detailURL ?>" data-img="<?= $previewURL ?>"
                           <? if ($images['type'] == 'video'): ?>data-video="true"<? endif;
                        ?>>
                            <? //=$commentPicture
                            ?>
                            <img src="<?= $thumbURL ?>">
                        </a>
                    <? endforeach; ?>
                </div>
                <br>
                <div id="whoEditComment" class="fs-16"><b>Комментарий<span id="nameUserEditComment"
                                                                           class="fs-16"><? if ($allInstallers[0]['whoAddComment']): ?> (<?= $userEditContent[$allInstallers[0]['whoAddComment']] ?><? if ($allInstallers[0]['whoEditComment'] && $allInstallers[0]['whoAddComment'] != $allInstallers[0]['whoEditComment']): ?>, ред. <?= $userEditContent[$allInstallers[0]['whoEditComment']] ?><? endif; ?>)<? endif; ?></span>:</b>
                </div>
                <div id="commentFotorama"><?= $allInstallers[0]['comment'] ?></div>
            </div>
        <? endif; ?>
    </div>
    <? if ($is_mobile === false): ?>
    <div class="container-fluid-second border-0 col  colFlex">

        <div class="row" style="position: sticky; top: 100px;">
            <div class="col galleryInfoText fs-24 text-nowrap">
                <span class="galleryInfoText fs-24"
                      id="setFileType"><? if ($allInstallers[0]['type'] == 'video'): ?>Видео<? else: ?>Фотография<? endif; ?></span>
                <span class="galleryInfoText fs-24" id="setPhotoNumber">1</span>
                <span class="galleryInfoText fs-16">от</span> <span id="setPhoto" class="galleryInfoText fs-16"><?= setDateFormat($allInstallers[0]['date'], 'd.m.Y, H:i') ?></span>
            </div>
            <div class="col text-end text-nowrap">
                <? if ($fullAccess === true): ?>
                    <div style="position: relative; top: -8px;">
                        <a onclick="moveFilePhotoBot('select'); return false;" class="btn btn-blue-white" href=""
                           role="button"><i title="Переместить в другую заявку"
                                            class="bi bi-arrow-left-right fs-22 color-blue ms-1 me-1"></i></a>
                        <a onclick="delFilePhotoBot('select'); return false;" class="btn btn-red-white" href=""
                           role="button"><i title="Удалить" class="bi bi-trash fs-22 color-red ms-1 me-1"></i></a>
                    </div>
                <? endif; ?>
            </div>
        </div>

        <div class="mt-1 fs-16 text-dark" style="position: sticky; top: 130px;">
            Загрузил: <span id="uploader" class="fs-16 text-dark fw-bold"><?= $allInstallers[0]['uploader'] ?></span>
        </div>
        <br>

        <!-- <div class="container h-50 mw-100" style="position: sticky; top: 50px; border-radius: 4px; border: 1px solid #DEE2E6; padding: 4px;"> -->
        <div style="position: sticky; top: 170px;" id="setAttributeImg">
            <div style="position: sticky; top: 170px; border-radius: 4px; border: 1px solid #DEE2E6; padding: 4px;" class="setAttributeBox">
                <!-- fotorama -->
                <? foreach ($allInstallers as $k => $images):
                    $whoAddComment = '';
                    if (!empty($images['whoAddComment'])) {
                        $whoAddComment = $userEditContent[$images['whoAddComment']];
                    }
                    $whoEditComment = '';
                    if (!empty($images['whoEditComment'])) {
                        $whoEditComment = $userEditContent[$images['whoEditComment']];
                    }
                    ?>
                    <input type="hidden" id="iconHaveComment<?= $k ?>" value="<?= $images['comment'] ?>"
                           data-addcomment="<?= $whoAddComment ?>" data-editcomment="<?= $whoEditComment ?>">
                <? endforeach; ?>
                <div id="laptopGallery" class="fotorama"
                     data-nav="false"
                     data-allowfullscreen="true"
                     data-arrows="true"
                     data-click="false"
                     data-trackpad="false"
                     data-swipe="false"
                     data-autoplay="false"
                     data-loop="true"
                     data-keyboard="false"
                     data-transition="crossfade"
                     data-height="50%",
                     data-maxwidth="720",
                     data-width="auto"
                >
                    <? foreach ($allInstallers as $k => $images):
                        $size = 142;
                        $sizePadding = 142;
                        $shortWidth = $size;
                        $shortHeigth = '';
                        $mimeType = explode(".", $images['path']);
                        $end = strtolower(end($mimeType));
                        $videoPath = '';

                        if ($images['type'] != 'video') {
                            $getimagesize = getimagesize($fullPath . $images['path']);

                            $w = $getimagesize[0];
                            $h = $getimagesize[1];

                            $param = "w";
                            $shortSize = ($sizePadding * 100 / $h) / 2;
                            $css = "top: -" . ceil(($h * $shortSize / 100) / 2) . "px;";
                            if ($w > $h) {
                                $param = "h";
                                $shortSize = ($sizePadding * 100 / $w) / 2;
                                $css = "left: -" . ceil(($w * $shortSize / 100) / 2) . "px;";
                            }
                        } else {
                            //$videoPath = $localPath.str_replace($deletePath, "", $images['path']);
                            $videoPath = $localPath . $images['path'];
                            $movie = new ffmpeg_movie($fullPath . $images['path']);
                            $w = $movie->getFrameWidth();
                            $h = $movie->getFrameHeight();
                            $css = '';
                            /*$frame = $movie->getFrame(1);
                            if($frame !== false)
                            {
                                //pre($frame->getWidth());
                                //pre();
                                //$frameImage = new ffmpeg_frame($frame->toGDImage());
                                //pre($frameImage);
                                //exit();
                                //$im = $frame->getR();
                                //header('Content-Type: image/png');
                                //imagepng($im);
                                //imagedestroy($im);
                                //exit();
                                //echo $frame->toGDImage();
                                //$frameImage = new ffmpeg_frame($frame);
                                //$frameImage = $frame->toGDImage();
                            }*/
                            //pre($frame);
                            //file_put_contents($deletePath."/s/gallery/".$images['id'].".png", $frame);

                            $param = "w";
                            if ($w > $h) {
                                $param = "h";
                                $shortWidth = '';
                                $shortHeigth = $size;
                            }
                        }

                        $thumbURL = "/init/resize.php?img=" . encrypt($fullPath . $images['path']) . "&" . $param . "=" . $size;
                        $previewURL = "/init/resize.php?img=" . encrypt($fullPath . $images['path']) . "&w=full";
                        $detailURL = $fullPath . $images['path'];
                        if ($images['type'] == 'video') {
                            $thumbURL = "/init/resize.php?img=" . encrypt($images['image']) . "&" . $param . "=" . $size;
                            $detailURL = $videoPath;
                            $previewURL = "/init/resize.php?img=" . encrypt($images['image']) . "&w=full";
                        }
                        //pre($detailURL);
                        $commentPicture = '';
                        if (!empty($images['comment'])) {
                            $commentPicture = '<div id="iconHaveComment' . $images['id'] . '" style="top: 5px; right: 5px; z-index: 2;" class=" position-absolute"><i title="Комментарий" style="color: #FFC107; font-size: 18px;" class="bi bi-chat-fill"></i></div>';
                        }
                        ?>

                        <a href="<?= $detailURL ?>" data-img="<?= $previewURL ?>"
                           <? if ($images['type'] == 'video'): ?>data-video="true"<? endif;
                        ?>>
                            <? //=$commentPicture
                            ?>
                            <img src="<?= $thumbURL ?>">
                        </a>
                    <? endforeach; ?>
                </div>
            </div>
        </div>
        <div class="mt-5 <? if ($fullAccess === false && empty($allInstallers[0]['comment'])): ?>hide<? endif; ?>"
             id="blockWithComment" style="position: sticky; top: 75%;">
            <div class="d-table-cell" style="width: 1%">
                <div class="col text-start me-2">
                    <i id="iconEditComment" class="bi bi-chat text-dark fs-22"></i>
                    <? if ($fullAccess === true): ?>
                        <i id="iconTextEditComment" class="hide bi bi-pencil text-dark fs-22"></i>
                    <? endif; ?>
                </div>
            </div>
            <div class="d-table-cell">
                <div id="whoEditComment" class="fs-16">Комментарий<span id="nameUserEditComment"
                                                                        class="fs-16"><? if ($allInstallers[0]['whoAddComment']): ?> (<?= $userEditContent[$allInstallers[0]['whoAddComment']] ?><? if ($allInstallers[0]['whoEditComment'] && $allInstallers[0]['whoAddComment'] != $allInstallers[0]['whoEditComment']): ?>, ред. <?= $userEditContent[$allInstallers[0]['whoEditComment']] ?><? endif; ?>)<? endif; ?></span>:
                </div>
                <? if ($fullAccess === true): ?>
                    <div id="whoTextEditComment" class="hide  fs-16">Изменить комментарий:</div>
                <? endif; ?>
            </div>
            <div class="d-table-cell" style="width: 1%">
                <div class="col text-end me-2" style="position: relative; top: -8px;">
                    <? if ($fullAccess === true): ?>
                        <a id="buttonEditComment" onclick="editCommentPhotoBot(); return false;"
                           class="btn btn-blue-white" href="" role="button"><i title="Редактировать комментарий"
                                                                               class="bi bi-pencil fs-22 color-blue ms-1 me-1"></i></a>
                        <a id="buttonSaveComment" onclick="saveCommentPhotoBot(); return false;"
                           class="hide btn btn-primary text-nowrap" href="" role="button"><i style="top: 3px;"
                                                                                             title="Сохранить"
                                                                                             class="position-relative bi bi-check2 fs-22 color-white ms-1 me-1"></i>Сохранить</a>
                    <? endif; ?>
                </div>
            </div>
            <div class="d-table-cell" style="width: 1%">
                <div class="col text-end me-2" style="position: relative; top: -8px;">
                    <? if ($fullAccess === true): ?>
                        <a id="buttonDeleteComment" onclick="delCommentPhotoBot(); return false;"
                           class="btn btn-red-white" href="" role="button"><i title="Удалить комментарий"
                                                                              class="bi bi-trash fs-22 color-red ms-1 me-1"></i></a>
                        <a id="buttonDeleteCancel" onclick="cancelCommentPhotoBot(); return false;"
                           class="hide btn btn-blue-white text-nowrap color-blue-hover" href="" role="button"><i
                                    style="top: 3px;" title="Отменить"
                                    class="position-relative bi bi-x-lg fs-22 color-blue"></i>Отменить</a>
                    <? endif; ?>
                </div>
            </div>

        <div class="mt-3">
            <div class="fs-16 text-dark" id="setCommentGalleryPhotoBot">
                <?= $allInstallers[0]['comment'] ?>
            </div>
            <? if ($fullAccess === true): ?>
                <input type="hidden" id="idCommentGalleryPhotoBot" value="<?= $allInstallers[0]['id'] ?>">
                <input type="hidden" id="loginUser" value="<?= $USER['login'] ?>">
                <input type="hidden" id="loginUserName" value="<?= $userEditContent[$USER['login']] ?>">
                <div class="hide" id="editCommentGalleryPhotoBot">
                    <textarea class="form-control"><?= $allInstallers[0]['comment'] ?></textarea>
                </div>
            <? endif; ?>
        </div>
    </div>
</div>
</div>
<? endif; ?>


