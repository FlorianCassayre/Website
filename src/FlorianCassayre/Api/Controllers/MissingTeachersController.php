<?php

namespace FlorianCassayre\Api\Controllers;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MissingTeachersController
{
    public function list_teachers(Application $app)
    {
        $sql = 'SELECT * FROM teachers';
        $rows = $app['pdo']->query($sql);

        $objects = array();

        foreach($rows as $row)
        {
            $id = $row['id'];
            $title = $row['title'];
            $last_name = $row['last_name'];
            $first_name = $row['first_name'];
            $subject = $row['subject'];

            array_push($objects, (object) array(
                'id' => $id,
                'title' => $title,
                'last_name' => $last_name,
                'first_name' => $first_name,
                'subject' => $subject
            ));
        }

        return $app->json($objects);
    }
}