<?php

namespace FlorianCassayre\Api\Controllers;


use Exception;
use PDO;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class PublibikeController
{
    const API_URL = 'https://api.publibike.ch/v1/public/stations/';

    public function scrap(Application $app, Request $request)
    {
        $secret = $request->get('secret', '');

        if(!($secret === $app['config']['publibike_task_secret']) && !$app['debug'])
        {
            return $app->json((object) array('error' => 'invalid_credentials'), 401);
        }


        $array = json_decode($this->curl($app, self::API_URL));

        $insertStation = $app['pdo']->prepare('INSERT IGNORE INTO publibike_stations (id, name, address, zip, city, latitude, longitude) VALUES (:id, :name, :address, :zip, :city, :latitude, :longitude)');
        $selectEvent = $app['pdo']->prepare('SELECT t.bikes FROM (SELECT * FROM `publibike_events` WHERE station_id = :id ORDER BY timestamp DESC LIMIT 1) AS t WHERE t.bikes = :bikes');
        $insertEvent = $app['pdo']->prepare('INSERT INTO publibike_events (station_id, bikes) VALUES (:id, :bikes)');
        $insertBike = $app['pdo']->prepare('INSERT IGNORE INTO publibike_bikes (id, name, type_id, type_name) VALUES (:id, :name, :type_id, :type_name)');

        foreach($array as $station)
        {
            $details = json_decode($this->curl($app, self::API_URL . $station->id));

            if(isset($details->id)) // Avoid warnings
            {
                $station_id = $details->id;
                $station_name = $details->name;
                $station_address = $details->address;
                $station_zip = $details->zip;
                $station_city = $details->city;
                $station_latitude = $details->latitude;
                $station_longitude = $details->longitude;

                $bikes = array();
                foreach($details->vehicles as $bike)
                {
                    $bike_id = $bike->id;
                    $bike_name = $bike->name;
                    $bike_type_id = $bike->type->id;
                    $bike_type_name = $bike->type->name;

                    $insertBike->bindParam(':id', $bike_id, PDO::PARAM_INT);
                    $insertBike->bindParam(':name', $bike_name, PDO::PARAM_STR);
                    $insertBike->bindParam(':type_id', $bike_type_id, PDO::PARAM_INT);
                    $insertBike->bindParam(':type_name', $bike_type_name, PDO::PARAM_STR);

                    $insertBike->execute();

                    array_push($bikes, $bike_id);
                }

                sort($bikes);
                $bikes_str = join(',', $bikes);

                $selectEvent->bindParam(':id', $station_id, PDO::PARAM_INT);
                $selectEvent->bindParam(':bikes', $bikes_str, PDO::PARAM_STR);

                $selectEvent->execute();

                if($selectEvent->rowCount() == 0)
                {
                    $insertEvent->bindParam(':id', $station_id, PDO::PARAM_INT);
                    $insertEvent->bindParam(':bikes', $bikes_str, PDO::PARAM_STR);

                    $insertEvent->execute();
                }

                $insertStation->bindParam(':id', $station_id, PDO::PARAM_INT);
                $insertStation->bindParam(':name', $station_name, PDO::PARAM_STR);
                $insertStation->bindParam(':address', $station_address, PDO::PARAM_STR);
                $insertStation->bindParam(':zip', $station_zip, PDO::PARAM_INT);
                $insertStation->bindParam(':city', $station_city, PDO::PARAM_STR);
                $insertStation->bindParam(':latitude', $station_latitude, PDO::PARAM_STR);
                $insertStation->bindParam(':longitude', $station_longitude, PDO::PARAM_STR);

                $insertStation->execute();
            }
        }

        return $app->json((object) array('result' => 'ok'));
    }

    private function curl($app, $url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic cHVibGliaWtlLWZyb250ZW5kczphZG1rdjl1MmEhQXA0TGEhdFRi', // Mendatory, otherwise asks for credentials...
            'Origin: https://www.publibike.ch',
            'Refer: https://www.publibike.ch/publibike/stations/'
        ));
        curl_setopt($curl, CURLOPT_USERAGENT, 'PublibikeCrawler/1.0 (compatible; +https://florian.cassayre.me/contact)');

        if ($app['debug'])
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);

        if ($result === false)
            throw new Exception(curl_error($curl), curl_errno($curl));

        curl_close($curl);

        return $result;
    }
}