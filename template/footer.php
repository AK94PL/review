<footer class="footer">
    <div class="footer__el">
        <ul class="footer-list list">
            <li class="footer-list__item">
                <a href="mailto:redakcja@<?php echo strtolower($setting['domain']);?>" class="footer-list__link">Kontakt</a>
            </li>
            <li class="footer-list__item">
                <a href="/regulamin/" class="footer-list__link">Regulamin</a>
            </li>
            <li class="footer-list__item">
                <a href="/polityka/" class="footer-list__link">Polityka prywatności</a>
            </li>

            <?php
            foreach(getAds('footer-menu',null) as $ad){
                $footer.=html_entity_decode($ad['code']);
            }
            echo $footer;
            ?>

            <li class="footer-list__item">
                <a href="/sitemap.xml" class="footer-list__link">Mapa witryny</a>
            </li>
        </ul>
    </div>

    <div class="footer__el">
        <?php echo html_entity_decode($setting['footer_text']); ?>
    </div>

    <button type="button" class="scroll" title="Wróć na górę strony">
        <svg class="scroll__svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" viewBox="0 0 24 24">
            <path d="M7.41,15.41L12,10.83L16.59,15.41L18,14L12,8L6,14L7.41,15.41Z" /></svg>
        <span class="scroll__title">Powrót</span>
    </button>

</footer>