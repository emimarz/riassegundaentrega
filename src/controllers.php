<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function (Request $request) use ($app) {
    return $app['twig']->render('index.html.twig', array(
        'login_paths' => $app['oauth.login_paths'],
        'logout_path' => $app['url_generator']->generate('logout', array(
            '_csrf_token' => $app['oauth.csrf_token']('logout')
        )),
        'error' => $app['security.last_error']($request)
    	));
})
->bind('homepage')
;

$app->get('/contacto', function () use ($app) {
    return $app['twig']->render('contacto.html.twig', array());
})
->bind('contactpage')
;

$app->get('/tarea', function (Request $request) use ($app) {
    return $app['twig']->render('login.html.twig', array(
        'login_paths' => $app['oauth.login_paths'],
        'logout_path' => $app['url_generator']->generate('logout', array(
            '_csrf_token' => $app['oauth.csrf_token']('logout')
        )),
        'error' => $app['security.last_error']($request)
    	));
})
->bind('loguadopage')
;


$app->post('/contacto', function (Request $request) use ($app) {
	$email = $request->get("email");
	$name = $request->get("name");
	$comment = $request->get("comment");

	$body = '
	<html>
		<body>
			<p>El usuario ' . $name .' ha escrito un comentario</p>
			<p>Su correo es: '.$email.'</p>
			<p>El commentario: '.$comment.'</p>
		</body>
	</html>
	';

	$message = \Swift_Message::newInstance()
	    ->setSubject('Un comentario ha sido enviado a Rias')
	    ->setFrom(array('no-reply@rias.com'=>'no-reply@rias.com'))
	    ->setTo(array('emimarz@gmail.com'))
	    ->setBody($body)
	    ->setContentType("text/html");

	$app['mailer']->send($message);	
    return $app['twig']->render('contacto.html.twig', array('emailsuccess'=>'true'));
})
->bind('contactpagepost')
;


$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});

$app->before(function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
    if (isset($app['security.token_storage'])) {
        $token = $app['security.token_storage']->getToken();
    } else {
        $token = $app['security']->getToken();
    }

    $app['user'] = null;

    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
        $app['user'] = $token->getUser();
    }
});

$app->get('/login', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {})->bind('loginpage');

$app->match('/logout', function () {})->bind('logout');