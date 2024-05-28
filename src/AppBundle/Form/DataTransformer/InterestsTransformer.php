<?php
/**
 * Created by PhpStorm.
 * User: pc-4
 * Date: 10/28/15
 * Time: 1:30 PM
 */

namespace AppBundle\Form\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class InterestsTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an interests to an array
     *
     * @param mixed $interests
     * @return string
     */
    public function transform($interests)
    {
        if (null === $interests) {
            return '';
        }

        $interestsArray = [];
        foreach($interests as $interest){
            if (!isset($interestsArray[$interest->getGroup()->getId()])){
                $interestsArray[$interest->getGroup()->getId()] = [];
            }

            $interestsArray[$interest->getGroup()->getId()][$interest->getId()] = $interest->getId();
        }

        return $interestsArray;
    }

    /**
     * Transforms an array to interests.
     *
     * @param mixed $interestsArray
     * @return object|void
     */
    public function reverseTransform($interestsArray)
    {
        if (!$interestsArray) {
            return [];
        }

        $interestIds = [];
        foreach($interestsArray as $interestGroup){
            foreach($interestGroup as $key => $interest){
                $interestIds[] = $key;
            }
        }

        $interests = $this->manager
            ->getRepository('AppBundle:Interest')->findByIds($interestIds);

        return $interests;
    }
}