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
                    $old_cost = $db_old_record->getCost();
                    if (100 - (($value['price'] * 100) / $old_cost) >= 40) {
                        file_put_contents( __DIR__ . '/skins/test.txt', $old_cost, FILE_APPEND);
                        file_put_contents( __DIR__ . '/skins/test.txt', json_encode($value), FILE_APPEND);
                    }
                    $db_old_record->setCost($value['price']);
                    $db_old_record->setQuantity($value['quantity']);
                    $db_old_record->setFlag(false);
                    $this->em->persist($db_old_record);
                    $this->em->flush();
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
                ->andWhere('i.flag = false')
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

    /**
     * @param $name
     * @param $cookie
     *
     * @return mixed
     */
    public function getInfoFromCsGoBack($name, $cookie)
    {
        try {
            $skin = "app=730_2&service=cs.money&search=" . urlencode($name);
            $url = "http://csgoback.net/ajax/pricebase";
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Cache-Control: no-store, no-cache, must-revalidate',
                    'Cf-Ray: 3f22b12601848e67-DME',
                    'Content-Type: application/x-www-form-urlencoded',
                    'Date: Sat, 24 Feb 2018 13:23:17 GMT',
                    'Expires: Thu, 19 Nov 1981 08:52:00 GMT',
                    'Pragma: no-cache',
                    'Server: cloudflar',
                    'Transfer-Encoding: chunked',
                    'Connection: keep-alive',
                    'Content-Encoding: gzip'
                )
            );

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $skin);
            curl_setopt($ch, CURLOPT_COOKIE, "__cfduid=d0a88be536b85d8a328a17db2d8c0c3f61519041450; path=/; domain=.csgoback.net; HttpOnly; Expires=Sun, 25 Feb 2019 13:26:19 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_ga=GA1.2.1093312345.1519041452; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_gat=1; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_gid=GA1.2.1003775563.1519583817; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_ym_isad=1; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_ym_uid=1519041452185677183; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=7d51fba524435af3de7bc9bf51c901f1; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "BACKSESSID=acd5f15ca402f0cd9d88b7c9677f852c; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "BACKSESSID=acd5f15ca402f0cd9d88b7c9677f852c; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            return json_decode($result, 1);
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * @param array $input_array_from_db
     * @param array $input_array_from_csGoBack
     * @param $percent
     *
     * @return array|string
     */
    public function EqualPrice(array $input_array_from_db, array $input_array_from_csGoBack, $percent)
    {
        try {
            if (!empty($input_array_from_csGoBack)) {
                foreach ($input_array_from_csGoBack as $key => $value) {
                    if ($input_array_from_db['name'] === $key) {
                        if (100 - (($input_array_from_db['cost'] / $value['price'])) >= $percent) {
                            return $input_array_from_db;
                        } else {
                            $db_old_record = $this
                                ->em
                                ->getRepository("ApiBundle:Ops")
                                ->findOneBy(
                                    array(
                                        "name" => $input_array_from_db['name'],
                                    )
                                );
                            $db_old_record->setFlag(true);
                            $this->em->persist($db_old_record);
                            $this->em->flush();
                            unset($db_old_record);
                        }
                    }
                }
            } else {
                $db_old_record = $this
                    ->em
                    ->getRepository("ApiBundle:Ops")
                    ->findOneBy(
                        array(
                            "name" => $input_array_from_db['name'],
                        )
                    );
                $db_old_record->setFlag(true);
                $this->em->persist($db_old_record);
                $this->em->flush();
                unset($db_old_record);
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}