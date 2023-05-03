// адаптация картинки
function adaptingImg() {
    $(".fotorama__img").removeAttr("style");
    $(".fotorama__img").css("margin", "auto");
    $(".fotorama__img").css("max-height", "100%");
    $(".fotorama__img").css("max-width", "100%");
    $(".fotorama__img").css("height", "92%");
    $(".fotorama__img").css("width", "auto");

// надо получать именно текущую картинку
    if(($(".fotorama__img").width() > 1000) && ($(".fotorama__img").width() < 1400)) {
        $(".fotorama__img").removeAttr("style");
        $(".fotorama__img").css("margin", "auto");
        $(".fotorama__img").css("max-height", "100%");
        $(".fotorama__img").css("max-width", "100%");
        $(".fotorama__img").css("height", "50%");
        $(".fotorama__img").css("width", "auto");
    }
}

// текущая картинка
var currentImg = 0;
var newImgIndex;

// переключение картинок клавишами
$(document).keydown(function (event) {
    if ((event.which === 39) || (event.which === 37)) {
        let imgElements = $(".imgClickList");
        var newImgIndex = currentImg;
        // курсор вправо
        if (event.which === 39) {
            newImgIndex += 1;
            if(newImgIndex > imgElements.length - 1) newImgIndex = 0;
            $(imgElements[newImgIndex]).trigger('click');
        }
        // курсор влево
        if (event.which === 37) {
            newImgIndex -= 1;
            if(newImgIndex < 0) newImgIndex = imgElements.length - 1;
            $(imgElements[newImgIndex]).trigger("click");
        }
        currentImg = newImgIndex
    }
});

function modify_image(image_src){
    $(".fotorama__img").on('load', function() {
        var width = this.width;
        adaptingImg();
    })
        .attr("src", image_src)
        .each(function() {
            if (this.complete) $(this).trigger('load');
        });
}

// загрузка документа
$(document).ready(function() {

    if ($(".imgClickList").length < 2) {
        $('#laptopGallery').fotorama({
            arrows: false
        });
    }
    $(function () {
        // 1. Initialize fotorama manually.
        var $fotoramaDiv = $('#laptopGallery').fotorama();

        // 2. Get the API object.
        var fotorama = $fotoramaDiv.data('fotorama');
    });

    $(function () {
        $('#laptopGallery')
            // Listen to the events
            .on('fotorama:fullscreenenter', // Вход в полноэкранный режим Fotorama
                function () {
                    $('#laptopGallery').fotorama({
                        arrows: true,
                        click: false,
                        keyboard: false
                    });
                }
            )
            // Initialize fotorama manually
            .fotorama();
    });

    $(function () {
        $('#laptopGallery')
            // Listen to the events
            .on('fotorama:fullscreenexit', // Выход из полноэкранного режима Fotorama
                function () {
                    $('#laptopGallery').fotorama({
                        arrows: true,
                        click: false
                    });
                }
            )
            // Initialize fotorama manually
            .fotorama();
    });
    $(".fotorama__arr--next").attr("id", "next");
    $(".fotorama__arr--prev").attr("id", "prev");
    $('.fotorama-mobile-view').on('fotorama:show', function (e, fotorama, extra) {
            var index = fotorama.activeIndex;

            var addEditComment = '';
            var whoAdd = $("#iconHaveComment"+index).attr("data-addcomment");
            var whoEdit = $("#iconHaveComment"+index).attr("data-editcomment");

            if (whoAdd.length > 0) {
                addEditComment = ' (';
                addEditComment += whoAdd;

                if (whoEdit.length > 0 && whoAdd != whoEdit) {
                    addEditComment += ', ред. ' + whoEdit;
                }

                addEditComment += ')';
            }

            $("#nameUserEditComment").text(addEditComment);
            $("#commentFotorama").text($("#iconHaveComment"+index).val());
        }
    );

    let imgElements = $(".imgClickList");
    newImgIndex = currentImg;
    $(".imgClickList").on("click", function () {
        newImgIndex = currentImg;
    });

    $("#next").on("click", function () {
        newImgIndex += 1;
        if(newImgIndex > imgElements.length - 1) newImgIndex = 0;
        $(imgElements[newImgIndex]).trigger('click');
        $(function () {
            // 1. Initialize fotorama manually.
            var $fotoramaDiv = $('#laptopGallery').fotorama();

            // 2. Get the API object.
            var fotorama = $fotoramaDiv.data('fotorama');

            let newFotoramaIndex = newImgIndex;

            $(fotorama.data[newImgIndex]).trigger('click');

            console.log(fotorama.data[newFotoramaIndex])

            // надо получать при каждом клике и переключении и приравнять к текущему номеру картинки в массиве слева
            //  console.log(fotorama)
            console.log($(".imgClickList")[newImgIndex])
        });
    });

    $("#prev").on("click", function () {
        newImgIndex -= 1;
        if(newImgIndex < 0) newImgIndex = imgElements.length - 1;
        $(imgElements[newImgIndex]).trigger("click");
        $(function () {
            // 1. Initialize fotorama manually.
            var $fotoramaDiv = $('#laptopGallery').fotorama();

            // 2. Get the API object.
            var fotorama = $fotoramaDiv.data('fotorama');

            let newFotoramaIndex = newImgIndex;

            $(fotorama.data[newImgIndex]).trigger('click');

            console.log(fotorama.data[newFotoramaIndex])

            // надо получать при каждом клике и переключении и приравнять к текущему номеру картинки в массиве слева
            //  console.log(fotorama)
            console.log($(".imgClickList")[newImgIndex])
        });
    });

    currentImg = newImgIndex;

});


/**
 * Смена изображзения с параметрами
 * @param id
 */
function setImageGalleryPhotoBot(id) {

    cancelCommentPhotoBot();

     $(".fotorama__video-play").remove();

    if ($("#getImageGalleryPhotoBot" + id).attr("data-type") == 'video') {
       $(".fotorama__img").css("display", "none");
       modify_image($("video").find("source").attr("src", $("#getImageGalleryPhotoBot" + id).attr("data-path-video")));
        $("#setFileType").text("Видео");
        $(function () {
            $('#laptopGallery')
                // Listen to the events
                .on('fotorama:fullscreenenter ', // Вход в полноэкранный режим Fotorama
                    function () {
                        $(".fotorama__img").css("display", "none");
                    }
                )
                // Initialize fotorama manually
                .fotorama();
        });

        $(function () {
            $('#laptopGallery')
                // Listen to the events
                .on('fotorama:fullscreenexit ', // Выход из полноэкранного режима Fotorama
                    function () {
                        $(".fotorama__img").css("display", "none");
                    }
                )
                // Initialize fotorama manually
                .fotorama();
        });
       $(".fotorama__img").attr("src", $("#getImageGalleryPhotoBot" + id).attr("data-path"));
        $(".fotorama__stage__frame").append("<div class='fotorama__video'> <iframe frameborder=\"0\" allowfullscreen=\"\">\n" +
            "        <html>\n" +
            "          <head>\n" +
            "          <meta name=\"viewport\" content=\"width=device-width\">\n" +
            "          <link id=\"psono-css\" rel=\"stylesheet\" type=\"text/css\" href=\"chrome-extension://eljmjmgjkbmpmfljlmklcfineebidmlo/data/css/contentscript.css\" media=\"all\">\n" +
            "          </head>\n" +
            "          <body>\n" +
            "            <video controls=\"\" autoplay=\"\" name=\"media\">\n" +
            "              <source type=\"video/mp4\">\n" +
            "            </video>\n" +
            "          </body>\n" +
            "        </html>\n" +
            "      </iframe></div>")
      $(".fotorama__video iframe").attr("src", $("#getImageGalleryPhotoBot" + id).attr("data-path-video"));
    } else {
        modify_image($(".fotorama__img").attr("src", $("#getImageGalleryPhotoBot" + id).attr("data-path")));
        $(function () {
            $('#laptopGallery')
                // Listen to the events
                .on('fotorama:fullscreenenter ', // Вход в полноэкранный режим Fotorama
                    function () {
                        $('#laptopGallery').fotorama({
                            arrows: true,
                            click: false,
                            keyboard: false
                        });
                        $(".fotorama__img").removeAttr("style");
                        $(".fotorama__img").css("margin", "auto");
                        $(".fotorama__img").css("max-height", "100%");
                        $(".fotorama__img").css("max-width", "100%");
                        $(".fotorama__img").css("height", "98%");
                        $(".fotorama__img").css("width", "auto");
                    }
                )
                // Initialize fotorama manually
                .fotorama();
        });

        $(function () {
            $('#laptopGallery')
                // Listen to the events
                .on('fotorama:fullscreenexit ', // Выход из полноэкранного режима Fotorama
                    function () {
                        $('#laptopGallery').fotorama({
                            arrows: true,
                            click: false
                        });
                        adaptingImg();
                    }
                )
                // Initialize fotorama manually
                .fotorama();
        });
        $(".fotorama__video").remove();
        $("#setFileType").text("Фотография");
        $("#setImageGalleryPhotoBotSrc").attr("src", $("#getImageGalleryPhotoBot" + id).attr("data-path"));
        $(".fotorama__img").attr("src", $("#getImageGalleryPhotoBot" + id).attr("data-path"));
        $("#setImageGalleryPhotoBot").attr("href", $("#getImageGalleryPhotoBot" + id).attr("data-path"));
        $("#setImageGalleryVideoBot").addClass("hide");
        $("#setImageGalleryPhotoBot").removeClass("hide");
    }

//alert($("#getImageGalleryPhotoBot"+id).attr("data-path"));

//$("#setPhoto").text($("#getImageGalleryPhotoBot"+id).attr("data-number"));
    $("#setPhoto").text($("#getImageGalleryPhotoBot" + id).attr("data-date"));
    $("#setImageGalleryPhotoBotSrc").text($("#getImageGalleryPhotoBot" + id).attr("data-date"));
    $(".fotorama__img").text($("#getImageGalleryPhotoBot" + id).attr("data-date"));
    $("#setPhotoNumber").text($("#getImageGalleryPhotoBot" + id).attr("data-number"));
    $("#setImageGalleryPhotoBotSrc").text($("#getImageGalleryPhotoBot" + id).attr("data-number"));
    $(".fotorama__img").text($("#getImageGalleryPhotoBot" + id).attr("data-number"));


    $("#uploader").text($("#getImageGalleryPhotoBot" + id).attr("data-uploader"));
    $("#idCommentGalleryPhotoBot").val(id);
    $("#setCommentGalleryPhotoBot").text($("#getImageGalleryPhotoBot" + id).attr("data-comment"));
    $("#editCommentGalleryPhotoBot").find("textarea").val($("#getImageGalleryPhotoBot" + id).attr("data-comment"));

    var addEditComment = '';
    var whoAdd = $("#getImageGalleryPhotoBot" + id).attr("data-addcomment");
    var whoEdit = $("#getImageGalleryPhotoBot" + id).attr("data-editcomment");

    if (whoAdd.length > 0) {
        addEditComment = ' (';
        addEditComment += whoAdd;

        if (whoEdit.length > 0 && whoAdd != whoEdit) {
            addEditComment += ', ред. ' + whoEdit;
        }

        addEditComment += ')';
    }

    $("#nameUserEditComment").text(addEditComment);

    var blockWithComment = $("#getImageGalleryPhotoBot" + id).hasClass("comment-block-hide");
    if (blockWithComment === false) {
        $("#blockWithComment").show();
    } else {
        $("#blockWithComment").hide();
    }

    var boxImg = parseInt($("#getImageGalleryPhotoBot" + id).attr("data-number"));
// var boxImg = parseInt($(".fotorama__img")[0].textContent);
    var currentBoxImg = boxImg - 1;

    currentImg = currentBoxImg;
    newImgIndex = currentImg;

}



/**
 * Показ картинки в большом формате
 * @param id
 */
function showImg(id) {
    $("#showContent").show();

    var img = $("#img_" + id).attr("src");

    $("#showContentHtml").html('<img src="' + img + '" width="450">');
}

/**
 * Выделить все фото
 */
function allPhotoChecked() {
    var checked = $("#allPhotoChecked").prop("checked");
    if (checked === true) {
        $(".imageGalleryId").prop("checked", true);
    } else {
        $(".imageGalleryId").prop("checked", false);
    }
    /*alert(checked);
    $(".imageGalleryId").each(function (){
        if($(this).is(':checked'))
        {
            if(checked === true)
            {
                $(this).prop("", true;)
            }
            //formData.append('selectFile[]', $(this).val());
        }
    });*/
}

/**
 * Помечаем файлы из заявок наудаление
 * @returns {boolean}
 */
function delFilePhotoBot(type = 'all', idImage = 0) {
    var selectFile = 0;

    var formData = new FormData();

    switch (type) {
        case 'cursor' :
            formData.append('selectFile[]', idImage);
            formData.append('typeFile[]', $("#getImageGalleryPhotoBot" + idImage).attr("data-type"));
            selectFile = 1;
            break;
        case 'select' :
            var idCommentGalleryPhotoBot = $("#idCommentGalleryPhotoBot").val();
            formData.append('selectFile[]', idCommentGalleryPhotoBot);
            ormData.append('typeFile[]', $("#getImageGalleryPhotoBot" + idCommentGalleryPhotoBot).attr("data-type"));
            selectFile = 1;
            break;
        case 'all' :
            $("input.imageGalleryId").each(function () {
                if ($(this).is(':checked')) {
                    formData.append('selectFile[]', $(this).val());
                    formData.append('typeFile[]', $("#getImageGalleryPhotoBot" + $(this).val()).attr("data-type"));
                    selectFile++;
                }
            });
            break;
    }

    if (selectFile == 0) {
        alert("Отметьте фото для переноса!");
        return;
    }

    var isRequest = confirm("Удалить?");
    if (isRequest === false) {
        return;
    }


    $.ajax({
        type: "POST",
        method: "POST",
        url: window.location.href + '&delete=y',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        success: function (data, status, xhr) {
            //$.get("/?query=<?=$url?>&delete=y&file="+data);
            window.location.href = window.location.href;
        },
        error: function (data, status, xhr) {
            alert(data.responseText);
        }
    });

    return false;
}

/**
 * Переносим фото из заявок в другие заявки: отправка и получение результата
 */
function moveFilePhotoBotSend() {
//console.log('start3');
    var type = $("#typeMovePhoto").val();
    var idImage = $("#typeMovePhotoId").val();
    var new_maf_id = $('#moveFilePhotoBotMafId').val();
    var maf_id = $('#currentMafId').val();
    var maf_name = $('#currentMafFullMane').val();

    var formData = new FormData();

    var selectFile = 0;

    switch (type) {
        case 'cursor' :
            formData.append('selectFile[]', idImage);
            formData.append('typeFile[]', $("#getImageGalleryPhotoBot" + idImage).attr("data-type"));
            selectFile = 1;
            break;
        case 'select' :
            var idCommentGalleryPhotoBot = $("#idCommentGalleryPhotoBot").val();
            formData.append('selectFile[]', idCommentGalleryPhotoBot);
            formData.append('typeFile[]', $("#getImageGalleryPhotoBot" + idCommentGalleryPhotoBot).attr("data-type"));
            selectFile = 1;
            break;
        case 'all' :
            $("input.imageGalleryId").each(function () {
                if ($(this).is(':checked')) {
                    formData.append('selectFile[]', $(this).val());
                    formData.append('typeFile[]', $("#getImageGalleryPhotoBot" + $(this).val()).attr("data-type"));
                    selectFile++;
                }
            });
            break;
    }

    if (selectFile == 0) {
        alert("Отметьте фото для переноса!");
        return;
    }

    formData.append('new_maf_id', new_maf_id);

    $.ajax({
        type: "POST",
        method: "POST",
        url: '/s/gallery/?maf_id=' + maf_id + '&move=y',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        success: function (data, status, xhr) {
            $("#showContentHtml").css('opacity', 0);
            $(".closeWindow").hide();

            var answer = jQuery.parseJSON(data);

            var refresh = "window.location.href='/s/gallery/?maf_id=" + maf_id + "'";

            var showContentHtml = '<div style="width: 255px;">';
            showContentHtml += '<div class="fs-22 text-center text-dark">Перемещено ';
            showContentHtml += selectFile;
            showContentHtml += ' фото';
            showContentHtml += '</div>';
            showContentHtml += '<br>';
            showContentHtml += '<br>';
            showContentHtml += '<div class="fs-16 text-dark">Вы переместили ' + selectFile + ' фото из заявки ' + maf_name + ' в заявку <a href="/s/gallery/?maf_id=' + answer['newId'] + '">' + answer['newName'] + '</a>.</div>';
            showContentHtml += '<br>';
            showContentHtml += '<div class="text-end">';
            showContentHtml += '<button style="padding-bottom: 2rem;" onclick="' + refresh + '" class="btn btn-success btn-lg me-2" type="button">Закрыть окно</button>';
            showContentHtml += '</div>';
            showContentHtml += '</div>';

            $("#showContentHtml").html(showContentHtml);
            $("#showContentHtml").animate({'opacity': 1}, 1000);

        },
        error: function (data, status, xhr) {
            //alert(data.responseText);
            $("#moveFilePhotoBotError").text(data.responseText);
        }
    });
}

/**
 * Переносим фото из заявок в другие заявки: подготовка
 * @returns {boolean}
 */
function moveFilePhotoBot(type = 'all', idImage = 0) {
    $("#typeMovePhoto").val(type);
    $("#typeMovePhotoId").val(idImage);

    var selectFile = 0;

    switch (type) {
        case 'cursor' :
        case 'select' :
            selectFile = 1;
            break;
        case 'all' :
            $("input.imageGalleryId").each(function () {
                if ($(this).is(':checked')) {
                    selectFile++;
                }
            });
            break;
    }

//console.log(selectFile);
    if (selectFile == 0) {
        alert("Отметьте фото для переноса!");
        return;
    }

    var showContentHtml = '<div style="width: 255px;">';
    showContentHtml += '<div class="fs-22 text-center text-dark">Переместить ';
    showContentHtml += selectFile;
    showContentHtml += ' фото';
    showContentHtml += '</div>';
    showContentHtml += '<br>';
    showContentHtml += '<br>';
    showContentHtml += '<div class="fs-16 text-dark">Введите номер заявки, в которую хотите переместить фотографии:</div>';
    showContentHtml += '<br>';
    showContentHtml += '<div><input value="" id="moveFilePhotoBotMafId" class="form-control me-2" type="search" placeholder="Номер заявки"></div>';
    showContentHtml += '<div id="moveFilePhotoBotError" class="color-red mt-1 fs-12"></div>';
    showContentHtml += '<br>';
    showContentHtml += '<div class="text-end">';
    showContentHtml += '<button style="padding-bottom: 2rem;" onclick="closeWindow(); return false;" class="btn btn-gray btn-lg me-2" type="button">Отмена</button><button onclick="moveFilePhotoBotSend();" style="padding-bottom: 2rem;" class="btn btn-primary btn-lg" type="button">Переместить</button>';
    showContentHtml += '</div>';
    showContentHtml += '</div>';


    $("#showContentHtml").html(showContentHtml);
    $("#showContent").show();
}

/**
 * Генерация фотоотчета
 * @param maf_id
 * @param maf_name
 * @param type full | compact
 */
function createPdfFilePhotoBot(maf_id, maf_name, type = 'full') {
    var pageurl = $('#pageurl').val();

    $("#buttonDownload").addClass("disabled");
    $("#showContentHtml").html("Идет генерация отчета...");
    $("#showContent").show();
    var fName = 'Лебер Фото-отчет о монтаже ' + maf_name + '.pdf';
    if (type != 'full') {
        fName = 'Лебер Фото-отчет о монтаже ' + maf_name + ' (компакт).pdf';
    }
    $.ajax({
        type: "GET",
        method: "GET",
        url: '/s/gallery/?maf_id=' + maf_id + '&report=y&type=' + type + '&url=' + pageurl,
        cache: false,
        contentType: false,
        processData: false,
        dataType: 'binary',
        xhrFields: {
            'responseType': 'blob'
        },
        success: function (data, status, xhr) {

            if (data.type != 'application/octet-stream') {
                //Если вместо файла получили страницу с ошибкой
                var reader = new FileReader();
                reader.readAsText(data);
                reader.onload = function () {
                    alert(reader.result);
                };
                $("#buttonDownload").removeClass("disabled");
                $("#showContent").hide();
                $("#showContentHtml").html("");


                return false;
            }

            var link = document.createElement('a'), filename = fName;
            link.href = URL.createObjectURL(data);
            link.download = filename;
            link.click();
            $("#buttonDownload").removeClass("disabled");
            $("#showContent").hide();
            $("#showContentHtml").html("");


            //$("#showContentHtml").css('opacity', 0);
            //$(".closeWindow").hide();

            //var answer = jQuery.parseJSON( data );

            /*var showContentHtml = '<div style="width: 255px;">';
            showContentHtml += '<div class="fs-22 text-center text-dark">Перемещено ';
            showContentHtml += selectFile;
            showContentHtml += ' фото';
            showContentHtml += '</div>';
            showContentHtml += '<br>';
            showContentHtml += '<br>';
            showContentHtml += '<div class="fs-16 text-dark">Вы переместили '+selectFile+' фото из заявки '+maf_name+' в заявку <a href="/s/gallery/?maf_id='+answer['newId']+'">'+answer['newName']+'</a>.</div>';
            showContentHtml += '<br>';
            showContentHtml += '<div class="text-end">';
            showContentHtml += '<button style="padding-bottom: 2rem;" onclick="'+refresh+'" class="btn btn-success btn-lg me-2" type="button">Закрыть окно</button>';
            showContentHtml += '</div>';
            showContentHtml += '</div>';*/

            //$("#showContentHtml").html(showContentHtml);
            //$("#showContentHtml").animate({'opacity': 1}, 1000);


        },
        error: function (data, status, xhr) {
            alert(data.responseText);
            $("#buttonDownload").removeClass("disabled");
            $("#showContent").hide();
            $("#showContentHtml").html("");
            //$("#moveFilePhotoBotError").text(data.responseText);
        }
    });
}

/**
 * Переход к редактированию комментария
 * @param id
 */
function changeCommentPhotoBot(id) {
    setImageGalleryPhotoBot(id);
    editCommentPhotoBot();
    $("#editCommentGalleryPhotoBot").find("textarea").focus();
}

/**
 * Редактировать комментарий в галерее
 */
function editCommentPhotoBot() {
    $("#buttonEditComment").hide();
    $("#buttonDeleteComment").hide();
    $("#setCommentGalleryPhotoBot").hide();
    $("#iconEditComment").hide();
    $("#whoEditComment").hide();

    $("#buttonSaveComment").css("display", "block");
    $("#buttonDeleteCancel").css("display", "block");
    $("#editCommentGalleryPhotoBot").css("display", "block");
    $("#iconTextEditComment").css("display", "block");
    $("#whoTextEditComment").css("display", "block");
}

/**
 * Удаление комментария
 */
function delCommentPhotoBot() {
    var isRequest = confirm("Удалить комментарий?");
    if (isRequest === false) {
        return;
    }

    var photo_id = $("#idCommentGalleryPhotoBot").val();
    var commentOld = $("#setCommentGalleryPhotoBot").text();

    var formData = new FormData();
    formData.append('photo_id', photo_id);
    formData.append('oldtext', commentOld);

    $.ajax({
        type: "POST",
        method: "POST",
        url: window.location.href + '&deltext=y',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        success: function (data, status, xhr) {
            $("#iconHaveComment" + photo_id).hide();

            $("#getImageGalleryPhotoBot" + photo_id).attr("data-comment", "");
            $("#getImageGalleryPhotoBot" + photo_id).attr("data-editcomment", "");
            $("#getImageGalleryPhotoBot" + photo_id).attr("data-addcomment", "");

            setImageGalleryPhotoBot(photo_id);
            cancelCommentPhotoBot();

        },
        error: function (data, status, xhr) {
            alert(data.responseText);
        }
    });
}

/**
 * Отмена редактирования комментария в галерее
 */
function cancelCommentPhotoBot() {
    $("#buttonSaveComment").hide();
    $("#buttonDeleteCancel").hide();
    $("#editCommentGalleryPhotoBot").hide();
    $("#iconTextEditComment").hide();
    $("#whoTextEditComment").hide();

    $("#buttonEditComment").css("display", "block");
    $("#buttonDeleteComment").css("display", "block");
    $("#setCommentGalleryPhotoBot").css("display", "block");
    $("#iconEditComment").css("display", "block");
    $("#whoEditComment").css("display", "block");
}

/**
 * Сохранить комментарий в галерее
 */
function saveCommentPhotoBot() {
    var commentOld = $("#setCommentGalleryPhotoBot").text();
    var commentNew = $("#editCommentGalleryPhotoBot").find("textarea").val();
    var photo_id = $("#idCommentGalleryPhotoBot").val();
//var loginUser = $("#loginUser").val();
    var loginUserName = $("#loginUserName").val();

    var save = true;

    if (commentOld == commentNew && commentOld.length > 0) {
        save = false;
    }

    if (commentNew.length <= 0) {
        alert("Поле Комментарий не заполнено!");
        return;
    }

//alert(commentOld);
//alert(commentNew);
//alert(save);

    if (save === true) {
        var formData = new FormData();
        formData.append('oldtext', commentOld);
        formData.append('newtext', commentNew);
        formData.append('photo_id', photo_id);
        //formData.append('whoEditComment', loginUser);

        $.ajax({
            type: "POST",
            method: "POST",
            url: window.location.href + '&edittext=y',
            cache: false,
            contentType: false,
            processData: false,
            data: formData,
            success: function (data, status, xhr) {
                //$.get("/?query=<?=$url?>&delete=y&file="+data);
                //window.location.href=window.location.href;
                //$("#setCommentGalleryPhotoBot").text(commentNew);
                $("#iconHaveComment" + photo_id).css("display", "block");

                $("#getImageGalleryPhotoBot" + photo_id).attr("data-comment", commentNew);

                if (commentOld.length > 0) {
                    $("#getImageGalleryPhotoBot" + photo_id).attr("data-editcomment", loginUserName);
                } else {
                    $("#getImageGalleryPhotoBot" + photo_id).attr("data-addcomment", loginUserName);
                }

                setImageGalleryPhotoBot(photo_id);
                cancelCommentPhotoBot();

            },
            error: function (data, status, xhr) {
                alert(data.responseText);
            }
        });
    } else {
        cancelCommentPhotoBot();
    }
}

/**
 * Показать/скрыть меню действий с картинкой в галерее
 * @param id
 * @param view
 */
function showActions(id, view) {
    $(".showActions").hide();
    if (view === true) {
        $("#showActions" + id).show();
    }
}

/**
 * Сортировка фото
 * @param sort
 */
function sortPhotoGallery(sort) {
    var maf_id = $('#currentMafId').val();
    window.location.href = '/s/gallery/?maf_id=' + maf_id + '&handsort=' + sort;
}

/**
 * Сортировка фоток мышкой
 * @returns {boolean}
 */
function handSortGallery() {
//var numberPhoto = $("#setPhoto").text();
    var k = 1;
    var formData = new FormData();
//var pictures = [];
    var numPhotoBeforeSort = 0;
//var oldSort = 0;

    var photoNumber = $("#setPhotoNumber").text();

    $("div.gallery-data-sort").each(function () {
        numPhotoBeforeSort = $(this).attr("data-number");

        $("#photoNumber" + $(this).attr("data-id")).text(k);
        $(this).attr("data-number", k);
        $(this).attr("data-sort", k);
        //pictures[k] = k;
        //console.log(k);

        if (numPhotoBeforeSort != k) {

            if (photoNumber == numPhotoBeforeSort) {
                $("#setPhotoNumber").text(k);

                //console.log(photoNumber);
                //console.log(numPhotoBeforeSort+"->"+k);
            }

            formData.append('selectFile[' + $(this).attr("data-id") + ']', k);
        }
        k++;
    });

//console.log(formData);

//return ;

    $.ajax({
        type: "POST",
        method: "POST",
        url: window.location.href + '&sort=y',
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        success: function (data, status, xhr) {
            //$.get("/?query=<?=$url?>&delete=y&file="+data);
            //window.location.href=window.location.href;


            //setImageFalleryPhotoBot(id);
        },
        error: function (data, status, xhr) {
            alert(data.responseText);
        }
    });

    return false;
}

/**
 * Закрыть всплывающее окно
 */
function closeWindow() {
    $(".closeWindow").click();
}

/**
 * Выделение активного элемента
 * @param id
 */
function setActive(id) {
    if ($("#inputBox" + id).is(':checked')) {
        $("#box" + id).addClass("elementActive");
        $("#boxIndex" + id).addClass("elementIndexActive");
        $("#showActions" + id).addClass("boxActionsActive");
    } else {
        $("#box" + id).removeClass("elementActive");
        $("#boxIndex" + id).removeClass("elementIndexActive");
        $("#showActions" + id).removeClass("boxActionsActive");
    }
}


$(document).ready(function () {
    /**
     * Закрыть всплывающее окно closeWindow()
     */
    $(".closeWindow").click(function () {
        $(".newWindow").hide();
        $("#showContentHtml").html("");
    });

    /**
     * Сортировка мышкой
     */
    var currentPhotoSort = $("#currentPhotoSort").val();
    var accessForAction = $("#accessForAction").val();
    if (currentPhotoSort == 'hand' && accessForAction == 1) {
        Sortable.create(gallerySort, {
            onChange: function (evt) {
                return handSortGallery();
            }
        });
    }

    /**
     * Подгрузка картинок
     */
    $("img.lazy").lazyload({
        effect: "fadeIn"
    });

    var elem = $("#setElement").val();
    if (elem > 0) {
        setImageGalleryPhotoBot(elem);
    }

//$('.media').media( { width: 142, height: 142, autoplay: false } );
});


/*(async () => {
if ('loading' in HTMLImageElement.prototype) {
    const images = document.querySelectorAll("img.lazyload");
    images.forEach(img => {
        img.src = img.dataset.src;
    });
} else {
    // Динамически импортируем библиотеку LazySizes
    const lazySizesLib = await import('/scripts/lazysizes.min.js');
    // Инициализируем LazySizes (читаем data-src & class=lazyload)
    lazySizes.init(); // lazySizes применяется при обработке изображений, находящихся на странице.
}
})();*/