<?php
if(isset($_SESSION['userInfo'])){

    echo '<div class="alert-group">'.$_SESSION['userInfo'].'</div>';

    unset($_SESSION['userInfo']);

}
?>
<?php
require_once ($_SERVER['DOCUMENT_ROOT'].'/template/cookies-alert.php');
?>
<script src="/assets/js/script.min.js"></script>
