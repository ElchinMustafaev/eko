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

    /**
     * @param $cost
     * @param $percent
     * @param $name
     * @param $app_id
     *
     * @return mixed|string
     */
    public function searchItem($cost, $percent, $name, $app_id)
    {
        try {
            $min_cost = ($cost / 100) * ((100 - $percent) / 100);
            $max_cost = ($cost / 100) * ((100 + $percent) / 100);
            $url = "https://api.opskins.com/ISales/Search/v1/?app=" . $app_id . "&search_item=" .
                '"' . urlencode($name) . '"' . "&min=" . $min_cost . "&max=" .
                $max_cost . "&key=" . $this->container->getParameter("ops_api_key_search");
            // create curl resource

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
            print_r($err);
            $output = json_decode($response, 1);

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
     * @param $input_array_from_db
     * @param $input_array_from_csGoBack
     * @param $percent
     *
     * @return array|string
     */
    public function EqualPrice($input_array_from_db, $input_array_from_csGoBack, $percent)
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

    /**
     * @return mixed|string
     */
    public function getTableFromCsGoBack()
    {
        try {
            $skin = "app=730_2&leftService=opskins.com&rightService=cs.money&leftServiceMinCount=&rightServiceMinCount=&leftServiceMaxCount=&rightServiceMaxCount=&leftUpdateTime=1&rightUpdateTime=&opskinsSales=10";
            $url = "http://csgoback.net/ajax/comparison";
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Cache-Control: no-store, no-cache, must-revalidate',
                    'Cf-Ray: 3f22b12601848e67-DME',
                    'Content-Type: application/x-www-form-urlencoded',
                    'Date: ' . date('D, d M Y H:i:s', time() - 10800) . ' GMT',
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
            curl_setopt($ch, CURLOPT_COOKIE, "__cfduid=df40c2f5b5e053f3435d6a188031c6af91505581236; path=/; domain=.csgoback.net; HttpOnly; Expires=Sun, 25 Feb 2019 13:26:19 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_ga=GA1.2.337131466.1505581226; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_gat=1; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_gid=GA1.2.35540825.1519978685; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_ym_isad=2; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "_ym_uid=1510571535413303924; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=e867f9d214c84242b9a4767d0d1ab5fd; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            curl_setopt($ch, CURLOPT_COOKIE, "BACKSESSID=ae2894c9424eeacfba5e1fc979a3fc3e; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");
            //curl_setopt($ch, CURLOPT_COOKIE, "BACKSESSID=acd5f15ca402f0cd9d88b7c9677f852c; path=/; domain=.csgoback.net; Expires=Tue, 19 Jan 2038 03:14:07 GMT;");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $result = curl_exec($ch);
            curl_close($ch);

            return json_decode($result, 1);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $input_array
     * @param $max_cost
     * @param $min_cost
     * @param $percent
     *
     * @return array|float|int|null
     */
    public function equalPriceCsGoBack($input_array, $max_cost, $min_cost, $percent)
    {
        try {
            if ($input_array['opskins.com']['price'] <= $max_cost && $input_array['opskins.com']['price'] >= $min_cost) {
                if (100 - ((($input_array['opskins.com']['price'] * 100) / $input_array['cs.money']['price'])) >= $percent) {
                    return array(
                        'name' => $input_array['name'],
                        'ops.cost' => $input_array['opskins.com']['price'] * 100,
                        'money.cost' => $input_array['cs.money']['price'] * 100
                    );
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @return mixed|string
     */
    public function getInfoToBye() {
        try {
            $qb = $this
                ->em
                ->createQueryBuilder('a');
            $query = $qb->select('a.name, a.cost, a.percent, a.time')
                ->from('ApiBundle:AllInfo', 'a')
                ->setMaxResults(1)
                ->orderBy('a.id', 'DESC')
                ->getQuery()
                ->getSingleResult();

            return $query;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param $record
     *
     * @return bool|string
     */
    public function removeRecord($record)
    {
        try {
            $record_from_db = $this
                ->em
                ->getRepository("ApiBundle:AllInfo")
                ->findOneBy(
                    array(
                        'name' => $record["name"],
                        'time' => $record['time'],
                    )
                );
            $this->em->remove($record_from_db);
            $this->em->flush();
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function opsByeItem($item_list)
    {
        try {
            $url = "https://api.opskins.com/ISales/BuyItems/v1/";
            $ch = curl_init();

            $item =
                "key=" . $this->container->getParameter("ops_api_key") .
                "&saleids=" . $item_list["sales"][0]["id"] . "&total=" . $item_list["sales"][0]["amount"];

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/x-www-form-urlencoded',
                )
            );
            curl_setopt($ch, CURLOPT_POSTFIELDS, $item);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $output = curl_exec($ch);

            curl_close($ch);

            return $output;

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getBalance()
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.opskins.com/IUser/GetBalance/v1/?key=" . $this->container->getParameter("ops_api_key"),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "Cache-Control: no-cache",
                    "Postman-Token: a2c784a5-f779-2239-9771-35fc23591c44"
                ),
            ));

            $response = curl_exec($curl);
            $response = json_decode($response, 1);
            $response = $response['balance'] / 100;

            curl_close($curl);

            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    public function bot($text)
    {
        try {
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.telegram.org/bot550447710:AAHF1lL93_7Zj3PjeVFBLfDcj9mYIMZFfN8/sendMessage?chat_id=-1001184076461&text=" . urlencode($text),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "postman-token: 524fd55a-5caa-0eb1-9149-f7567b039b67"
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return $response;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}