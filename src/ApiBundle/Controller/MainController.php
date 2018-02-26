<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\BrowserKit\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

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

            $i = 0;
            $return_from_db = $ops_helper->getInfoFromDb(1000, 5000, 1, 500);
            //print_r($return_from_db);
            //$return = $ops_helper->getInfoFromCsGoBack($return_from_db[0]['name'], "");

            foreach ($return_from_db as $key => $value) {
                $return = $ops_helper->getInfoFromCsGoBack($value['name'], "");
                $return = $return['result'];
                print_r($i++ . "\n");
                print_r($ops_helper->EqualPrice($value, $return, 37));
            }


            return new JsonResponse();
        } catch (\Exception $e) {
            $err = array(
                $e->getMessage(),
                $e->getLine(),
                $e->getFile(),
            );

            return new JsonResponse($err);
        }
    }
}
