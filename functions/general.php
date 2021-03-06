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
    if($firstLetter==='??'){
        $firstLetter = 'a';
    }
    if($firstLetter==='??'){
        $firstLetter = 'e';
    }
    if($firstLetter==='??'){
        $firstLetter = 'o';
    }
    if($firstLetter==='??'){
        $firstLetter = 'l';
    }
    if($firstLetter==='??'){
        $firstLetter = 's';
    }
    if($firstLetter==='??'){
        $firstLetter = 'z';
    }
    if($firstLetter==='??'){
        $firstLetter = 'z';
    }
    if($firstLetter==='??'){
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
        $string = str_replace("??", "a", $string);
        $string = str_replace("??", "c", $string);
        $string = str_replace("??", "e", $string);
        $string = str_replace("??", "o", $string);
        $string = str_replace("??", "l", $string);
        $string = str_replace("??", "s", $string);
        $string = str_replace("??", "z", $string);
        $string = str_replace("??", "z", $string);
        $string = str_replace("??", "n", $string);
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
        'm' => 'miesi??c',
        'w' => 'tydzie??',
        'd' => 'dzie??',
        'h' => 'godzin??',
        'i' => 'minut??',
        's' => 'sekund??',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            if($v==='rok'){
                $v = $diff->$k . ($diff->$k > 5 ? ' lat' : ' lata');
                if($diff->$k == 1){
                    $v = $diff->$k .' rok';
                }
            }
            if($v==='miesi??c'){
                $v = $diff->$k . ($diff->$k >= 5 ? ' miesi??cy' : ' miesi??ce');
                if($diff->$k == 1){
                    $v = $diff->$k .' miesi??c';
                }

            }
            if($v==='tydzie??'){
                $v = $diff->$k . ($diff->$k > 5 ? ' tygodni' : ' tygodnie');
                if($diff->$k == 1){
                    $v = $diff->$k .' tydzie??';
                }

            }
            if($v==='dzie??'){
                $v = $diff->$k . ($diff->$k > 1 ? ' dni' : ' dzie??');
            }
            if($v==='godzine'){
                $v = $diff->$k . ($diff->$k > 5 ? ' godzin' : ' godziny');
                if($diff->$k == 1){
                    $v = $diff->$k .' godzin??';
                }
            }
            if($v==='minute'){
                $v = $diff->$k . ($diff->$k > 5 ? ' minut' : ' minuty');
                if($diff->$k == 1){
                    $v = $diff->$k .' minut??';
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