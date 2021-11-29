<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/assets/css/style.css">

<?php
getFavIco();

    if(!empty($themeModeStatus)){
        echo '<link rel="stylesheet" href="/assets/css/dark-theme.css">';
    }else{
        echo '<link rel="stylesheet" href="/assets/css/light-theme.css">';
    }

?>
<meta name="theme-color" content="#006dff"/>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-4MD9YHPT3N"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-4MD9YHPT3N');
</script>