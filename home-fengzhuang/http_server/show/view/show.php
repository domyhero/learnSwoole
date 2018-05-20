<?php
     //include_once dirname(dirname(__DIR__)).'/tool/rpcClient.php';
     //$client=new rpcClient();
?>

<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="Generator" content="EditPlus®">
  <meta name="Author" content="">
  <meta name="Keywords" content="">
  <meta name="Description" content="">
  <title>六星商城</title>
  <link rel="stylesheet" type="text/css" href="/css/buy.css">
 </head>
 <body>
  <div class="banner"></div>
  <div class="center">
  <ul>
      <?php
         //$list=$client->service('GoodsService')->getList();
         //echo $list;
         /* if(!empty($list)){
              echo $list['data'];
          }*/
      ?>
  </ul>
  </div>
   <?php
       //$cart=$client->service('CartService')->cart();
       //echo $cart;
       /*if(!empty($cart)){
           echo $cart['data'];
       }*/
   ?>
  <script type="text/javascript" src="/js/jquery.js"></script>
  <script type="text/javascript" src="/js/buy.js"></script>
  <script type="text/javascript" src="/js/sx_fly.js"></script>
</body>

</html>
