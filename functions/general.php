<?php
if(!isset($_GET['p']) || !is_numeric($_GET['p'])){
    if(!is_int($_GET['p']) || (int)$_GET<=0){
        $_GET['p'] = 1;
    }
}


function plDate($date){
    $polishDateFormatter = new IntlDateFormatter(
        'pl_PL',
        IntlDateFormatter::LONG,
        IntlDateFormatter::NONE
    );

    $dateFormatted = new DateTime($date);
    $newDate = $polishDateFormatter->format($dateFormatted);
    $dateArray = explode(' ',$newDate);
    return $dateArray;
}

function sortArrayByPromoted($array){

    foreach($array as $item){
        if($item['pinned']){
            $pinnedArray[] =  $item;
        }elseif($item['promoted']){
            $promotedArray[] =  $item;
        }else{
            $normalArray[] = $item;
        }
    }

    $finalArray = array();
    if(!empty($normalArray)){
        $finalArray = array_merge($normalArray,$finalArray);
    }

    if(!empty($promotedArray)){
        $finalArray = array_merge($promotedArray,$finalArray);
    }

    if(!empty($pinnedArray)){
        $finalArray = array_merge($pinnedArray,$finalArray);
    }


    return $finalArray;
}

function sortArrayByVerificatedAndPromoted($array){
    foreach($array as $item){
        if($item['verificated'] && $item['promoted']){
            $verificatedPromotedArray[] = $item;
        }elseif($item['verificated']){
            $verificatedArray[] =  $item;
        }elseif($item['promoted']){
            $promotedArray[] =  $item;
        }else{
            $normalArray[] = $item;
        }

    }
    $finalArray = array();

        if(!empty($normalArray)){
            $finalArray = array_merge($normalArray,$finalArray);
        }

    if(!empty($promotedArray)){
        $finalArray = array_merge($promotedArray,$finalArray);
    }

    if(!empty($verificatedArray)){
        $finalArray = array_merge($verificatedArray,$finalArray);
    }

    if(!empty($verificatedPromotedArray)){
        $finalArray = array_merge($verificatedPromotedArray,$finalArray);
    }

    return $finalArray;
}


function getFavIco(){
    $firstLetter = strtolower(mb_substr(getSettings('site_adress'), 0, 1));
    if($firstLetter==='ą'){
        $firstLetter = 'a';
    }
    if($firstLetter==='ę'){
        $firstLetter = 'e';
    }
    if($firstLetter==='ó'){
        $firstLetter = 'o';
    }
    if($firstLetter==='ł'){
        $firstLetter = 'l';
    }
    if($firstLetter==='ś'){
        $firstLetter = 's';
    }
    if($firstLetter==='ź'){
        $firstLetter = 'z';
    }
    if($firstLetter==='ż'){
        $firstLetter = 'z';
    }
    if($firstLetter==='ć'){
        $firstLetter = 'c';
    }

    echo '<link rel="icon" href="/assets/img/_favicon/'.$firstLetter.'/android-chrome-192x192.png">';
    echo '<link rel="icon" href="/assets/img/_favicon/'.$firstLetter.'/android-chrome-512x512.png">';
    echo '<link rel="icon" href="/assets/img/_favicon/'.$firstLetter.'/apple-touch-icon.png">';
    echo '<link rel="icon" href="/assets/img/_favicon/'.$firstLetter.'/favicon.ico">';
    echo '<link rel="icon" href="/assets/img/_favicon/'.$firstLetter.'/favicon-16x16.png">';
    echo '<link rel="icon" href="/assets/img/_favicon/'.$firstLetter.'/favicon-32x32.png">';

}

function clean($string,$place) {
    if($place != 'register'){
        $string = trim($string);
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
        $string = str_replace("ą", "a", $string);
        $string = str_replace("ć", "c", $string);
        $string = str_replace("ę", "e", $string);
        $string = str_replace("ó", "o", $string);
        $string = str_replace("ł", "l", $string);
        $string = str_replace("ś", "s", $string);
        $string = str_replace("ź", "z", $string);
        $string = str_replace("ż", "z", $string);
        $string = str_replace("ń", "n", $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        $string = trim($string);
        $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
    }
        $string = str_replace('/','', $string);
        $string = str_replace('\\', '', $string);
        $string = str_replace('"', '', $string);
        $string = str_replace('\'', '', $string);
    return trim($string); // Removes special chars.
}

//function removeSlashes($string){
//    $cleanString = str_replace('/','', $string);
//    $cleanString = str_replace('\\', '', $cleanString);
//    $cleanString = str_replace('"', '', $cleanString);
//    $cleanString = str_replace('\'', '', $cleanString);
//    return $cleanString;
//}

function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'rok',
        'm' => 'miesiąc',
        'w' => 'tydzień',
        'd' => 'dzień',
        'h' => 'godzinę',
        'i' => 'minutę',
        's' => 'sekundę',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            if($v==='rok'){
                $v = $diff->$k . ($diff->$k > 5 ? ' lat' : ' lata');
                if($diff->$k == 1){
                    $v = $diff->$k .' rok';
                }
            }
            if($v==='miesiąc'){
                $v = $diff->$k . ($diff->$k >= 5 ? ' miesięcy' : ' miesiące');
                if($diff->$k == 1){
                    $v = $diff->$k .' miesiąc';
                }

            }
            if($v==='tydzień'){
                $v = $diff->$k . ($diff->$k > 5 ? ' tygodni' : ' tygodnie');
                if($diff->$k == 1){
                    $v = $diff->$k .' tydzień';
                }

            }
            if($v==='dzień'){
                $v = $diff->$k . ($diff->$k > 1 ? ' dni' : ' dzień');
            }
            if($v==='godzine'){
                $v = $diff->$k . ($diff->$k > 5 ? ' godzin' : ' godziny');
                if($diff->$k == 1){
                    $v = $diff->$k .' godzinę';
                }
            }
            if($v==='minute'){
                $v = $diff->$k . ($diff->$k > 5 ? ' minut' : ' minuty');
                if($diff->$k == 1){
                    $v = $diff->$k .' minutę';
                }
            }
            if($v==='sekunde'){
                $v = $diff->$k . ($diff->$k > 5 ? ' sekund' : ' sekundy');
                if($diff->$k == 1){
                    $v = $diff->$k .' sekunde';
                }
            }
//            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' temu' : 'teraz';
}