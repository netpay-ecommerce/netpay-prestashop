<?php
/**
 * Example Application
 *
 * @package Example-application
 */
require 'libs/Smarty.class.php';
$smarty = new Smarty;

$smarty->debugging = false;
$smarty->caching = false;
$smarty->cache_lifetime = 120;

for($i=0 ; $i<10 ; $i++)
{
    $expiration_years[] = array(
        'description' => date('Y', strtotime('+'.$i.' year')),
        'year' => substr(date('Y', strtotime('+'.$i.' year')), 2, 4)
    );
}
$smarty->assign("expiration_years", $expiration_years, true);

$smarty->display('payment_form.tpl');
