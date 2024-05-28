<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/19/16
 * Time: 12:09 PM
 */
namespace AppBundle\Command;
use AppBundle\Entity\State;
use Symfony\Component\Config\FileLocator;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;


/**
 * Class StateCommand
 * @package AppBundle\Command
 */
class StateCommand extends ContainerAwareCommand
{

    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:state:create')
            ->setDescription('Create us states')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // starting
        $output->writeln("<info>Starting to create states</info>");

        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        // get all states
        $states = Yaml::parse(file_get_contents(__DIR__ .  '/Files/states.yml'));

        // get all states from db
        $dbStates = $em->getRepository("AppBundle:State")->findAllWithAbbr();


        // loop for states
        foreach ($states as $abbr => $name){

            $abbr = trim(strtolower($abbr));
            $name = trim(ucfirst($name));

            if(!array_key_exists($abbr, $dbStates)){

                $state = new State();
                $state->setName($name);
                $state->setAbbr($abbr);
                $em->persist($state);
            }
        }

        $em->flush();

        $output->writeln("<info>Success creating states</info>");

    }
}
