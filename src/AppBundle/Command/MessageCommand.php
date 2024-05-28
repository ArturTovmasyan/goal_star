<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 7/20/16
 * Time: 2:40 PM
 */

namespace AppBundle\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
/**
 * Class MessageCommand
 * @package AppBundle\Command
 */
class MessageCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:message:send')
            ->setDescription('Send message to all users')
        ;
    }
    private function generateMessage()
    {
        $message = 'Attention LuvByrd members, this message is to alert you about an important app update.
        This update will allow your app to run faster and enable/disable notifications!';
        return $message;
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // starting
        $output->writeln("<info>Starting to send message</info>");
        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();
        $filters = $em->getFilters();
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;
        // find admin user
        $adminUser = $em->getRepository("LBUserBundle:User")->findOneBy(array('username' => 'admin'));
        // find all users
        $users = $em->getRepository("LBUserBundle:User")->findAll();
        $count = count($users);
        $content = $this->generateMessage();
        $subject = "Update app";
        $output->writeln("<info>$count</info>");
        // loop for users
        foreach($users as $key => $user){
            if($user->getUsername() != 'admin'){
                // insert message
                $em->getRepository("LBMessageBundle:Message")->insertMessage($adminUser->getId(), $user->getId(),
                    $subject, $content);
            }
            $output->writeln("<info>$key</info>");
        }
        $output->writeln("<info>Success sending message</info>");
    }
}