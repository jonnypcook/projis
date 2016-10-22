<?php 
//phpinfo();
die();
$loc1 = '/tmp';
echo '<pre>',print_r(scandir($loc1), true), '</pre>';
echo '<hr>';
$loc2 = '/root/jonny.p.cook@8point3led.co.uk';
echo '<pre>',print_r(scandir($loc2), true), '</pre>';
echo '<hr>';
$loc2 = '/home/jonny.p.cook@8point3led.co.uk';
echo '<pre>',print_r(scandir($loc2), true), '</pre>';
echo '<hr>';
$loc2 = '/bob';
echo '<pre>',print_r(scandir($loc2), true), '</pre>';
echo '<hr>';
?>
