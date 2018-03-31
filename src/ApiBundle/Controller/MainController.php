<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    /**
     * @Route("test")
     */
    public function test()
    {
        try {
            $ops_helper = $this->get("api.ops.helper");

            //$return = $ops_helper->searchItem(1000, 100, "StatTrak™ UMP-45 | Scaffold (Well-Worn)", "730_2");
            //$return = $ops_helper->downloadOpsLowCost(730);
            //$return = $ops_helper->writeInDb($return);

            //$i = 0;
            //$return_from_db = $ops_helper->getInfoFromDb(1000, 5000, 1, 500);
            //print_r($return_from_db);
            //$return = $ops_helper->getInfoFromCsGoBack($return_from_db[0]['name'], "");
            /**
            foreach ($return_from_db as $key => $value) {
                $return = $ops_helper->getInfoFromCsGoBack($value['name'], "");
                print_r($return);
                $return = $return['result'];
                print_r($i++ . "\n");
                print_r($ops_helper->EqualPrice($value, $return, 37));
            }
            **/
            //print_r($ops_helper->removeRecord($return_from_db));
            //echo "\n";

            $return = "";
            return new JsonResponse($return);
        } catch (\Exception $e) {
            $err = array(
                $e->getMessage(),
                $e->getLine(),
                $e->getFile(),
            );

            return new JsonResponse($err);
        }
    }

    /**
     * @Route("form")
     *
     * @return Response
     */
    public function form()
    {
        $html = "<!DOCTYPE HTML>
<html>
 <head>
  <meta charset=\"utf-8\">
  <title>Тег FORM, атрибут method</title>
 </head>
 <body>  
 <form action=\"buy\" method=\"post\">
 name
  <p><input type=\"text\" name=\"name\"></p>
  price
  <p><input type=\"text\" name=\"price\"></p>
  <p><input type=\"submit\" value=\"Отправить\"></p>
 </form>
 </body>
</html>";
        return new Response($html);
    }

    /**
     * @Route("buy")
     *
     * @param Request $request
     * @return Response
     */
    public function buy(Request $request)
    {
        $name = $request->get("name");
        $price = $request->get("price");
        $list = "";
        $ops_helper = $this->get("api.ops.helper");
        $i = 0;

        $id_array = $ops_helper->searchItem($price, 0, $name, "578080_2");
        foreach ($id_array["response"]["sales"] as $sale) {
            if ($sale["amount"] == $price) {
                $list .= $sale["id"] . ",";
                $i++;
            }
        }
        if (strlen($list) > 0) {
            $list = substr($list, 0, -1);
            $result = $ops_helper->opsByeItem_v3($list, $i * $price);
            print_r("Пытался купить " . $i . " вещей");
            print_r($result . "\n");
        } else {
            print_r("null");
        }
        return new Response();
    }
}
