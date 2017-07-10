<?php
namespace App\AppBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;

abstract class AbstractTest extends WebTestCase
{

    protected $domain;
    protected $em;
    protected $container;
    protected $client;

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    // ~

    public function __construct()
    {
        $client = static::createClient();
        $this->container = $client->getContainer();
        $this->em = $this->container->get('doctrine')->getManager();
        $this->domain = $this->getDomain();
        $this->client = $this->createAuthorizedClient();
    }

    // ~

    protected final function getDomain()
    {
        $domain = '';
        try {
            $domain = 'http://' . $this->container->getParameter('domain_name');
        } catch (\Exception $exc) {
            
        }
        return $domain;
    }

    // ~

    protected final function createAuthorizedClient()
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $session = $container->get('session');

        try {
            $userManager = $container->get('fos_user.user_manager');

            $loginManager = $container->get('fos_user.security.login_manager');
            $firewallName = $container->getParameter('fos_user.firewall_name');

            $user = $userManager->findUserBy(array('username' => 'administrator1'));
            $loginManager->loginUser($firewallName, $user);

            // save the login token into the session and put it in a cookie
            $container->get('session')->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
            $container->get('session')->save();
            $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
        } catch (\Exception $exc) {
            
        }

        return $client;
    }
}
