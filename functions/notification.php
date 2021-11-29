<?php

function createNotification($type,$contentId,$sender_type,$sender){

    $content = null;
    if($type === 'article'){
        $content = 'przesyła artykuł';
    }elseif($type === 'article-edit'){
        $content = 'przesyła zedytowany artykuł';
    }elseif($type === 'event'){
        $content = 'przesyła wydarzenie';
    }elseif($type === 'event-edit'){
        $content = 'przesyła zedytowane wydarzenie';
    }elseif($type === 'company'){
        $content = 'przesyła prośbę o dodanie firmy';
    }elseif($type === 'advertisement'){
        $content = 'przesyła ogłoszenie';
    }elseif($type === 'advertisement-edit'){
        $content = 'przesyła zedytowane ogłoszenie';
    }elseif($type === 'theard'){
        $content = 'przesyła dyskusje';
    }elseif($type === 'theard-edit'){
        $content = 'przesyła zedytowaną dyskusje';
    }elseif($type === 'report'){
        $content = 'przesyła zgłoszenie. Sprawdź pocztę aby poznać szczegóły';
    }
    $ntfObj = new Notification();
    $ntfId = $ntfObj->createNotification($type,$contentId,$content,$sender_type,$sender);
    return $ntfId;
}