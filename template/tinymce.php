<?php
if((int)$_SESSION['user']->group_id === 6 || (int)$_SESSION['user']->group_id === 4){
    echo '
        <script>
    function setPlainText() {
        var ed = tinyMCE.get(\'elm1\');

        ed.pasteAsPlainText = true;

        //adding handlers crossbrowser
        if (tinymce.isOpera || /Firefox\/2/.test(navigator.userAgent)) {
            ed.onKeyDown.add(function (ed, e) {
                if (((tinymce.isMac ? e.metaKey : e.ctrlKey) && e.keyCode == 86) || (e.shiftKey && e.keyCode == 45))
                    ed.pasteAsPlainText = true;
            });
        } else {
            ed.onPaste.addToTop(function (ed, e) {
                ed.pasteAsPlainText = true;
            });
        }
    }

    tinymce.init({';
    if(!empty($themeModeStatus)){
        echo '
            skin: "oxide-dark",
            content_css: "dark",
            ';
    }
    echo '
        oninit : \'setPlainText\',
        selector: \'textarea\',
        plugins: \'paste image print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template code codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern help noneditable \',
        toolbar: \'formatselect | bold italic strikethrough forecolor backcolor | link image | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat\',
        language: \'pl\',
        branding: false,
        paste_as_text: true,
        browser_spellcheck: true,
        language_url: \'\/assets/js/tiny-pl.js\',
        images_upload_url: \'/app/uploadImage.php\',
        images_upload_handler: function (blobInfo, success, failure) {
            var xhr, formData;
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open(\'POST\', \'/app/uploadImage.php\');
            xhr.onload = function() {
                var json;
                                if (xhr.status === 400) {
                  failure(\'Błąd! Niepoprawna nazwa pliku. Zmień nazwę przesyłanego zdjęcia pomijając znaki specjalne i polskie litery. \', { remove: true });
                  return;
                }

                if (xhr.status != 200) {
                    failure(\'HTTP Error: \' + xhr.status);
                    return;
                }
                json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != \'string\') {
                    failure(\'Invalid JSON: \' + xhr.responseText);
                    return;
                }
                success(json.location);
            };
            formData = new FormData();
            formData.append(\'file\', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        }
    });
</script>
    ';
}else{
    echo '
    <script>
    function setPlainText() {
        var ed = tinyMCE.get(\'elm1\');

        ed.pasteAsPlainText = true;

        //adding handlers crossbrowser
        if (tinymce.isOpera || /Firefox\/2/.test(navigator.userAgent)) {
            ed.onKeyDown.add(function (ed, e) {
                if (((tinymce.isMac ? e.metaKey : e.ctrlKey) && e.keyCode == 86) || (e.shiftKey && e.keyCode == 45))
                    ed.pasteAsPlainText = true;
            });
        } else {
            ed.onPaste.addToTop(function (ed, e) {
                ed.pasteAsPlainText = true;
            });
        }
    }


    tinymce.init({';

    if(!empty($themeModeStatus)){
        echo '
            skin: "oxide-dark",
            content_css: "dark",
            ';
    }
    echo '      
        oninit : "setPlainText",
        selector: "textarea",
        branding: false,
          plugins: "paste image print preview searchreplace autolink directionality visualblocks visualchars fullscreen image link media template code codesample table charmap hr pagebreak nonbreaking anchor insertdatetime advlist lists wordcount textpattern help noneditable ",
        toolbar: "formatselect | bold italic strikethrough forecolor backcolor | link image | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat",
        menu: "",
        toolbar: "bold italic underline image ",
        menubar:"",
        language: "pl",
        browser_spellcheck: true,
        language_url: "/assets/js/tiny-pl.js",
        statusbar : false,
        paste_as_text: true,
        images_upload_url: "/app/uploadImage.php",
    });

</script>
    ';
}
?>
