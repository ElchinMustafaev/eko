<?php
/**
 * Created by PhpStorm.
 * User: el
 * Date: 22.02.18
 * Time: 14:47
 */

namespace ApiBundle\Service;
use ApiBundle\Entity\Ops;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OpsApiHelper
{
    private $container;

    private $em;

    /**
     * OpsApiHelper constructor.
     *
     * @param ContainerInterface $container
     * @param \Doctrine\ORM\EntityManager $em
     */
    function __construct(ContainerInterface $container, \Doctrine\ORM\EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * @param $app_id
     *
     * @return mixed|string
     */
    public function downloadOpsLowCost($app_id)
    {
        try {
            $url = "https://api.opskins.com/IPricing/GetAllLowestListPrices/v1/?appid=" . $app_id;
            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $url);

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // $output contains the output string
            $output = curl_exec($ch);

            // close curl resource to free up system resources
            curl_close($ch);

            $output = json_decode($output, 1);
            $output = $output['response'];

            return $output;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array $input_array
     *
     * @return array|string
     */
    public function writeInDb(array $input_array)
    {
        try {
            set_time_limit(0);
            $new = 0;
            $old = 0;
            foreach ($input_array as $key => $value) {
                $db_old_record = $this
                    ->em
                    ->getRepository("ApiBundle:Ops")
                    ->findOneBy(
                        array(
                            "name" => $key,
                            "cost" => $value["price"],
                            "quantity" => $value['quantity']
                        )
                    );
                if (empty($db_old_record)) {
                    $new_record = new Ops();

                    $new_record->setName($key);
                    $new_record->setCost($value['price']);
                    $new_record->setQuantity($value['quantity']);
                    $new_record->setFlag(false);

                    $this->em->persist($new_record);
                    $this->em->flush();
                    $new++;
                    unset($new_record);
                } else {
                    $old++;
                }
                unset($db_old_record);
            }
            return array(
                "new" => $new,
                "old" => $old,
            );
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $min_cost
     * @param $max_cost
     * @param $min_quantity
     * @param $max_quantity
     *
     * @return array|string
     */
    public function getInfoFromDb($min_cost, $max_cost, $min_quantity, $max_quantity)
    {
        try {
            $qb = $this
                ->em
                ->createQueryBuilder('i');
            $qb->select('i.name, i.cost, i.quantity')
                ->from('ApiBundle:Ops', 'i')
                ->where('i.cost >= :min_cost')
                ->andWhere('i.cost <= :max_cost')
                ->andWhere('i.quantity <= :max_quantity')
                ->andWhere('i.quantity >= :min_quantity')
                ->setParameter('min_cost', $min_cost)
                ->setParameter('max_cost', $max_cost)
                ->setParameter('min_quantity', $min_quantity)
                ->setParameter('max_quantity', $max_quantity);
            $query = $qb->getQuery();
            $result = $query->getResult();
            return $result;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function searchItem($cost, $percent, $name, $app_id)
    {
        try {
            $min_cost = ($cost / 100) * ((100 - $percent) / 100);
            $max_cost = ($cost / 100) * ((100 + $percent) / 100);
            $url = "https://api.opskins.com/ISales/Search/v1/?app=" . $app_id . "&search_item=" .
                '"' . $name . '"' . "&min=" . $min_cost . "&max=" .
                $max_cost . "&key=" . $this->container->getParameter("ops_api_key");
            // create curl resource
            $ch = curl_init();

            // set url
            curl_setopt($ch, CURLOPT_URL, $url);

            //return the transfer as a string
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            // $output contains the output string
            $output = curl_exec($ch);

            // close curl resource to free up system resources
            curl_close($ch);

            $output = json_decode($output, 1);

            return $output;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}