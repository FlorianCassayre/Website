<?php

namespace FlorianCassayre\Florian\Controllers;

use Silex\Application;

class MainPagesController
{
    public function homepage(Application $app)
    {
        return $app['twig']->render('general/homepage.html.twig', array());
    }

    public function donation(Application $app)
    {
        return $app['twig']->render('general/donation.html.twig', array());
    }

    public function contact(Application $app)
    {
        return $app['twig']->render('general/contact.html.twig', array(
            'submit' => false
        ));
    }

    public function contact_submit(Application $app)
    {
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $email = isset($_POST['email']) ? $_POST['email'] : '';
        $message = isset($_POST['message']) ? $_POST['message'] : '';

        if(empty($name) || empty($email) || empty($message))
        {
            $success = false;
        }
        else
        {
            $success = true;

            $sql = 'INSERT INTO contact (name, email, message) VALUES (:name, :email, :message)';
            $sth = $app['pdo']->prepare($sql);
            $sth->bindParam(':name', $name);
            $sth->bindParam(':email', $email);
            $sth->bindParam(':message', $message);
            $sth->execute();
        }

        return $app['twig']->render('general/contact.html.twig', array(
            'submit' => true,
            'success' => $success,
            'name' => $name,
            'email' => $email,
            'message' => $message
        ));
    }

    public function api(Application $app)
    {
        return $app['twig']->render('general/api.html.twig');
    }
}