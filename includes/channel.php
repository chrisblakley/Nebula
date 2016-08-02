<?php
  $cache_expire = 31536000; //1 year
  header("Pragma: public");
  header("Cache-Control: maxage=" . $cache_expire);
  header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$cache_expire) . ' GMT');
?>
<script src="//connect.facebook.net/en_US/all.js" defer></script>