<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @Route("test2")
     *
     * @return Response
     */
    public function test2()
    {
        //echo date(DATE_RFC822);
        //echo date('D, d M Y H:i:s', time() - 10800) . ' GMT';
        $ops_helper = $this->get("api.ops.helper");
        //$return = $ops_helper->searchItem(27, 50, "StatTrak™ MAC-10 | Carnivore (Minimal Wear)", "730_2");
        //$return = $ops_helper->downloadOpsLowCost("730");
        print_r($ops_helper->searchItem(61, 10, "M4A1-S | Nitro (Well-Worn)", "730_2"));


        return new Response();
    }
}
