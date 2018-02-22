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

            $return = $ops_helper->searchItem(8, 2, "Dual Berettas | Cartel (Minimal Wear)", "730_2");

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
}
