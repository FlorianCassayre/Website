<?php

namespace FlorianCassayre\Api\Controllers;

use DOMDocument;
use PDO;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class MissingTeachersController
{
    const URL_SCRAPING = 'https://www.weblycee.fr/mermoz/abs_scroll.php';

    public function list_teachers(Application $app)
    {
        $sql = 'SELECT * FROM teachers ORDER BY last_name';
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

    public function list_missing(Application $app)
    {
        $sql = 'SELECT * FROM teachers_missing WHERE date >= CURDATE() ORDER BY date';
        $rows = $app['pdo']->query($sql);

        $objects = array();

        foreach($rows as $row)
        {
            $teacher_id = $row['teacher_id'];
            $date = $row['date'];
            $is_morning = $row['is_morning'];
            $is_afternoon = $row['is_afternoon'];
            $date_added = $row['date_added'];

            array_push($objects, (object) array(
                'teacher_id' => $teacher_id,
                'date' => $date,
                'is_morning' => $is_morning,
                'is_afternoon' => $is_afternoon,
                'date_added' => $date_added
            ));
        }

        return$app->json($objects);
    }

    public function scrap(Application $app, Request $request)
    {
        $secret = $request->get('secret', '');

        if(!($secret === $app['config']['mermoz_task_secret']))
        {
            return $app->json((object) array('error' => 'invalid_credentials'), 401);
        }

        try
        {
            $contents = file_get_contents(self::URL_SCRAPING);

            if($contents === false)
            {
                return $app->json((object) array('error' => 'contents_false'), 504);
            }

            $contents = '<HTML>' . explode('</SCRIPT>', $contents)[1]; // Incorrect html source page...

            $contents = mb_convert_encoding($contents, 'HTML-ENTITIES', "UTF-8");

            $dom = new DOMDocument();
            $dom->loadHTML($contents);

            $table = $dom->getElementsByTagName('table')[0];

            $timestamp = null;

            $selectStatement = $app['pdo']->prepare('SELECT id FROM teachers WHERE name_original = :name_original');
            $insertStatement = $app['pdo']->prepare('INSERT INTO teachers_missing (teacher_id, date, is_morning, is_afternoon) VALUES (:teacher_id, FROM_UNIXTIME(:date), :is_morning, :is_afternoon)');

            foreach($table->getElementsByTagName('tr') as $tr)
            {
                $children = $tr->getElementsByTagName('td');

                if($children->length == 1)
                {
                    $day_info = $children->item(0)->textContent;
                    $date_formatted = explode(' ', $day_info)[1];

                    $datetime = \DateTime::createFromFormat('d/m/Y', $date_formatted);
                    $timestamp = $datetime->getTimestamp();
                }
                elseif($children->length == 3)
                {
                    $period_td = $children->item(0)->textContent;

                    if($period_td === 'Journée')
                    {
                        $is_morning = true;
                        $is_afternoon = true;
                    }
                    elseif($period_td === 'Matin')
                    {
                        $is_morning = true;
                        $is_afternoon = false;
                    }
                    elseif($period_td === 'Apres-Midi')
                    {
                        $is_morning = false;
                        $is_afternoon = true;
                    }
                    else
                    {
                        $is_morning = false;
                        $is_afternoon = false;
                    }

                    $name_original = $children->item(1)->textContent; // Serves as an id


                    $selectStatement->bindParam(':name_original', $name_original);
                    $selectStatement->execute();
                    $teacher_id = $selectStatement->fetchColumn();

                    $insertStatement->bindParam(':teacher_id', $teacher_id);
                    $insertStatement->bindParam(':date', $timestamp);
                    $insertStatement->bindParam(':is_morning', $is_morning, PDO::PARAM_BOOL);
                    $insertStatement->bindParam(':is_afternoon', $is_afternoon, PDO::PARAM_BOOL);

                    // No better way? ...
                    try
                    {
                        $insertStatement->execute();

                        // New value

                        // mermoz_gcm_key
                    }
                    catch(\PDOException $exception)
                    {
                        // Existing value
                    }

                }
                else
                {
                    throw new \ParseError('Found length of ' . $children->length);
                }
            }

            return $app->json((object) array('result' => 'ok'));
        }
        catch(\Exception $exception)
        {
            return $app->json((object) array('error' => 'exception'), 504);
        }
    }
}