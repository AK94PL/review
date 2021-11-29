<?php
$eventsArray = getCurrentEvents(7);
if(!empty($eventsArray)){
    echo '
                 <section>
        <h2 class="main__heading">Ostatnio dodane wydarzenia</h2>

                ';
    echo (!empty($eventsArray)?'<div class="panel-art__group">':'');
    if(!empty($eventsArray)){

        $eventsList .= '
                               <section class="panel-art">
                                      <div class="panel-art__wrapper">
                    ' . ($eventsArray[0]['promoted'] ? '<div class="bdg">Wyróżniono</div>' : '') . '   <a href="/wydarzenia/' . $eventsArray[0]['subjectPL'] . ',' . $eventsArray[0]['eventId'] . '/" class="panel-art-photo" title="Przejdź do wydarzenia">
                            <img class="panel-art-photo__img" src="' . (!empty($eventsArray[0]['image']) ? '/uploads/events/' . $eventsArray[0]['image'] : '/assets/img/panel--art.png') . '" alt="' . $eventsArray[0]['subject'] . '">

                        </a>
                        <div class="panel-art-content">
                            <h3 class="heading">
                                <a href="/wydarzenia/' . $eventsArray[0]['subjectPL'] . ',' . $eventsArray[0]['eventId'] . '/" class="heading__link">
                                    ' . $eventsArray[0]['subject'] . '
                                </a>
                            </h3>
                            
                            <div class="author">
                            ';
        if ($eventsArray[0]['user_type'] != 'deleted' && $eventsArray[0]['user_type'] != 'deleted-company') {
            $eventsList .= ' <a href="' . ($eventsArray[0]['user_type'] === 'user' ? '/profil/' . getAuthorLoginPL($eventsArray[0]['user_id'], $eventsArray[0]['user_type']) . ',' . $eventsArray[0]['user_id'] . '/' : '/firmy/' . getAuthorLogin($eventsArray[0]['user_id'], $eventsArray[0]['user_type']) . ',' . $eventsArray[0]['user_id'] . '/') . '" class="link" title="Profil autora">' . getAuthorLogin($eventsArray[0]['user_id'], $eventsArray[0]['user_type']) . '</a>';
        } else {
            $eventsList .= ' <span class="bolder">Konto usunięte</span>';
        }
        $eventsList .= '  <span class="author__date">(' . time_elapsed_string($eventsArray[0]['created_date']) . ')</span>
                                 w:
                                <a href="/wydarzenia/miejscowosc/' . $eventsArray[0]['cityPL'] . '/" class="link" title="Wydarzenia z ' . $eventsArray[0]['city'] . '">' . $eventsArray[0]['city'] . '</a>.
                                Kategoria:
                                <a href="/wydarzenia/' . $eventsArray[0]['categoryNamePL'] . '/' . $eventsArray[0]['cityPL'] . '/" class="link" title="' . $eventsArray[0]['categoryName'] . ' w ' . $eventsArray[0]['city'] . '">' . $eventsArray[0]['categoryName'] . '</a>.
                            </div>
                            <div class="panel-art-date">
                                <div class="panel-art-date__elm">
                                    <span class="panel-art-date__title">Rozpoczęcie:</span>
                                    <span class="panel-art-date__time">' . substr($eventsArray[0]['date_start'], 0, 16) . '</span>
                                </div> 
                                <div class="panel-art-date__elm">
                                    <span class="panel-art-date__title">Zakończenie:</span>
                                    <span class="panel-art-date__time">' . (!empty($eventsArray[0]['date_end']) ? substr($eventsArray[0]['date_end'], 0, 16) : '---') . '</span>
                                </div>
                            </div>
                            <div class="actions">
                                <div class="actions__el">
                                    <svg class="actions__svg" xmlns="http://www.w3.org/2000/svg"
                                         xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1"
                                         viewBox="0 0 24 24">
                                        <path
                                                d="M13.5,8H12V13L16.28,15.54L17,14.33L13.5,12.25V8M13,3A9,9 0 0,0 4,12H1L4.96,16.03L9,12H6A7,7 0 0,1 13,5A7,7 0 0,1 20,12A7,7 0 0,1 13,19C11.07,19 9.32,18.21 8.06,16.94L6.64,18.36C8.27,20 10.5,21 13,21A9,9 0 0,0 22,12A9,9 0 0,0 13,3">
                                        </path>
                                    </svg>
                                    <span class="actions__content">
                                            Wyświetlenia:
                                        </span>
                                    <span class="actions__counter">
                                            ' . $eventsArray[0]['views'] . '
                                        </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
               ';
    }

    $eventsList.='
                <div class="panel-art-aside">
    <ul class="panel-art-aside-list list">
                ';
    $i = 0;
    foreach($eventsArray as $event){
        if($i!=0){

            $dateArray = plDate(substr($event['date_start'], 0, 11));
            $dateArray[1] = mb_substr($dateArray[1],0,3,'UTF-8');

            $eventsList.='
        <li class="panel-art-aside-list__item">
            <span class="panel-art-aside-list-date">
                <span class="panel-art-aside-list-date__day">
                    '.$dateArray[0].'
                </span>
                <span class="panel-art-aside-list-date__month">
                    '.$dateArray[1].'
                </span>
            </span>
            <a href="/wydarzenia/' . $event['subjectPL'] . ',' . $event['eventId'] . '/" class="panel-art-aside-list__link">
                ' . $event['subject'] . '
            </a>
        </li>
            ';
        }
        $i++;
    }
    $eventsList.='</ul></div>';
    echo $eventsList;

    echo '
                </div>
        <div class="right">
            <a href="/wydarzenia/" class="button button--secondary" title="Pokaż wszystkie">
                Pokaż więcej...
            </a>
        ';
    echo (!empty($eventsArray)?'</div>':'');
    echo '</section>
                ';
}
?>


