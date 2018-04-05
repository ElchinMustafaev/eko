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

    /**
     * @Route("test3")
     *
     * @return Response
     */
    public function test3()
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.opskins.com/IInventory/GetInventory/v1/?key=9705eea199113d9e7c005b6a27f0f1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Cache-Control: no-cache",
                "Postman-Token: 045029cc-d320-2774-4e2b-d296f73ba422"
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response,1);
        $response = $response["response"]["items"];
        $i = 0;
        foreach ($response as $value) {
            $i++;
            $new_array[$value["id"]] = $value["bot_id"];
        }
        asort($new_array);
        $chunk_array = array_chunk($new_array, 100, true);
        foreach ($chunk_array as $value) {
            $list = "";
            foreach ($value as $id => $bot) {
                $list .= $id . ",";
            }
                if (strlen($list) > 0) {
                    $list = substr($list, 0, -1);
                    $curl = curl_init();

                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://api.opskins.com/IInventory/Withdraw/v1/",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 30,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POSTFIELDS => "key=9705eea199113d9e7c005b6a27f0f1&items=" . $list,
                        CURLOPT_HTTPHEADER => array(
                            "Cache-Control: no-cache",
                            "Content-Type: application/x-www-form-urlencoded",
                            "Postman-Token: fbcdb080-6b19-ccdc-2200-b0fcb640256a"
                        ),
                    ));

                    $response = curl_exec($curl);
                    $err = curl_error($curl);

                    curl_close($curl);

                    print_r($response);
                } else {
                    print_r("NULL");
                }

        }
        return new Response();
    }
}
