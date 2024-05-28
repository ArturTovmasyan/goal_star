<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 11/2/15
 * Time: 7:54 PM
 */
namespace LB\UserBundle\Twig;

use Symfony\Component\DependencyInjection\Container;

class TwigExtension extends \Twig_Extension
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('areFriends', array($this, 'areFriends')),
            new \Twig_SimpleFunction('getActivity', array($this, 'getActivity')),
        );
    }

    /**
     * @param $lastActivity
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function getActivity($lastActivity)
    {
        $result = array('minute' => -1, 'title' => null);

        // now
        $now = new \DateTime('now');

        if (!$lastActivity) {
            return $result;
        }

        // get date diff
        $dateDiff = date_diff($now, $lastActivity);


        // activity result
        switch ($dateDiff) {
            case $dateDiff->y > 0:
                $result = array('minute' => $dateDiff->d * 365 * 1440 + $dateDiff->i, 'title' => 'active within 1 year');
                break;
            case $dateDiff->m >= 6:
                $result = array('minute' => $dateDiff->d * 30 * 1440 + $dateDiff->i, 'title' => 'active within 6 months');
                break;
            case $dateDiff->m > 0:
                $result = array('minute' => $dateDiff->d * 30 * 1440 + $dateDiff->i, 'title' => 'active within one month');
                break;
            case $dateDiff->d >= 7:
                $result = array('minute' => $dateDiff->d * 1440 + $dateDiff->i, 'title' => 'active within one week');
                break;
            case $dateDiff->d >= 3:
                $result = array('minute' => $dateDiff->d * 1440 + $dateDiff->i, 'title' => 'active within 72 hrs');
                break;
            case $dateDiff->d > 0:
                $result = array('minute' => $dateDiff->d * 1440 + $dateDiff->i, 'title' => 'active within 24 hrs');
                break;
            case $dateDiff->h > 0:
                $result = array('minute' => $dateDiff->h * 60 + $dateDiff->i, 'title' => 'active within 1 hr');
                break;
            default:
                $result = array('minute' => $dateDiff->i, 'title' => 'active less than 1 hr');
                break;
        }

        return $result;
    }

    public function getName()
    {
        return 'twig_extension';
    }
}