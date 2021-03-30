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
        $reason = isset($_POST['reason']) ? $_POST['reason'] : '';
        $is_bot = !empty($reason);

        if(empty($name) || empty($email) || empty($message) || $is_bot)
        {
            $success = false;
        }
        else
        {
            $success = true;

            // Database insert
            $sql = 'INSERT INTO contact (name, email, message) VALUES (:name, :email, :message)';
            $sth = $app['pdo']->prepare($sql);
            $sth->bindParam(':name', $name);
            $sth->bindParam(':email', $email);
            $sth->bindParam(':message', $message);
            $sth->execute();

            // Email send
            $mail_to = 'florian.cassayre@gmail.com';
            $mail_subject = $name . ' - Formulaire de contact florian.cassayre.me';
            $mail_message = 'Nom complet : ' . $name . "\r\nAdresse mail : " . $email . "\r\n\r\n" . $message . "\r\n\r\n" . '-- ' . "\r\n" . 'Cet email a été envoyé à partir du formulaire de contact https://florian.cassayre.me/contact.';
            $mail_headers = 'From: admin2@cassayre.me' . "\r\n" .
                'Reply-To: ' . $email . "\r\n" .
                'X-Mailer: PHP/' . phpversion();

            mail($mail_to, $mail_subject, $mail_message, $mail_headers);
        }

        return $app['twig']->render('general/contact.html.twig', array(
            'submit' => true,
            'success' => $success,
            'is_bot' => $is_bot,
            'name' => $name,
            'email' => $email,
            'message' => $message,
            'reason' => $reason
        ));
    }

    public function api(Application $app)
    {
        return $app['twig']->render('general/api.html.twig');
    }

    public function projects(Application $app)
    {
        $filename = $app['contents_directory'] . '/projects.json';

        if(!file_exists($filename)) {
            return $app->abort(404);
        }
        $json = json_decode(file_get_contents($filename));

        return $app['twig']->render('general/projects.html.twig', array(
            'data' => $json
        ));
    }

    public function brand(Application $app)
    {
        return $app['twig']->render('general/brand.html.twig');
    }

    public function pgp(Application $app)
    {
        return $app['twig']->render('general/pgp.html.twig');
    }
}