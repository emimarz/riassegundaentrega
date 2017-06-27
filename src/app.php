<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
//use Silex\Provider\UrlGeneratorServiceProvider;

define('FACEBOOK_API_KEY',    '1568555486521366');
define('FACEBOOK_API_SECRET', '92b78c17aabe5090c97689b7c2374cfd');
define('TWITTER_API_KEY',     'PaioVzGZMFnbPxgp7bbf46TKQ');
define('TWITTER_API_SECRET',  'KaIEwLaIk1EFzk5MCSabD3b5wAIKZUATax1eh9g2pKPIc3WXo1');
define('GOOGLE_API_KEY',      '490136397731-rkud5tnv9hn11ke4cffgqb7flomdq3kl.apps.googleusercontent.com');
define('GOOGLE_API_SECRET',   'h3ZbK9PU1zGfipYsR8TKbfXU');

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
//$app->register(new UrlGeneratorServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
});
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), array(
'swiftmailer.options' => array(
'host' => 'smtp.gmail.com',
'port' => 465,
'username' => 'maldito.snorlax@gmail.com',
'password' => 'Martin11',
'encryption' => 'ssl',
'auth_mode' => 'login')
));
$app['swiftmailer.use_spool'] = false;

$app->register(new Gigablah\Silex\OAuth\OAuthServiceProvider(), array(
    'oauth.services' => array(
        'Facebook' => array(
            'key' => FACEBOOK_API_KEY,
            'secret' => FACEBOOK_API_SECRET,
            'scope' => array('email'),
            'user_endpoint' => 'https://graph.facebook.com/me'
        ),
        'Twitter' => array(
            'key' => TWITTER_API_KEY,
            'secret' => TWITTER_API_SECRET,
            'scope' => array(),
            // Note: permission needs to be obtained from Twitter to use the include_email parameter
            'user_endpoint' => 'https://api.twitter.com/1.1/account/verify_credentials.json?include_email=true',
            'user_callback' => function ($token, $userInfo, $service) {
                $token->setUser($userInfo['name']);
                if(isset($userInfo['email'])){
                	$token->setEmail($userInfo['email']);
                }
                $token->setUid($userInfo['id']);
            }
        ),
        'Google' => array(
            'key' => GOOGLE_API_KEY,
            'secret' => GOOGLE_API_SECRET,
            'scope' => array(
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile'
            ),
            'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo'
        )
    )
));

//$app->register(new Silex\Provider\FormServiceProvider());

// Provides session storage
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => '/tmp'
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'oauth' => array(
                //'login_path' => '/auth/{service}',
                //'callback_path' => '/auth/{service}/callback',
                //'check_path' => '/auth/{service}/check',
                'failure_path' => '/login',
                'with_csrf' => true
            ),
            'logout' => array(
                'logout_path' => '/logout',
                'with_csrf' => true
            ),
            // OAuthInMemoryUserProvider returns a StubUser and is intended only for testing.
            // Replace this with your own UserProvider and User class.
            'users' => new Gigablah\Silex\OAuth\Security\User\Provider\OAuthInMemoryUserProvider()
        )
    ),
    'security.access_rules' => array(
        array('^/auth', 'ROLE_USER')
    )
));

return $app;
