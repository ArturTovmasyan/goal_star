<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 3/14/16
 * Time: 10:53 AM
 */

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class SyncMailChimpCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:sync:mail-chimp')
            ->setDescription('Sync MailChimp data')
        ;
    }

    private function getChimpUsers()
    {
        // get mailchimp api key and mailchimp list id from parameters
        $apiKey = $this->getContainer()->getParameter('mailchimp_api_key');
        $listId = $this->getContainer()->getParameter('mailchimp_list_id');
        $chunk_size = 4096; //in bytes
        $mailChimpEmails = array();


        // get API kay prefix
        $dataCenter = substr($apiKey, strpos($apiKey, '-') + 1);

        // create connection url
        $url = 'https://' . $dataCenter . '.api.mailchimp.com/export/1.0/list?apikey='.$apiKey.'&id='.$listId;

        $handle = @fopen($url,'r');
        if (!$handle) {
            echo "failed to access url\n";
        } else {
            $i = 0;
            $header = array();
            while (!feof($handle)) {
                $buffer = fgets($handle, $chunk_size);
                if (trim($buffer)!=''){
                    $obj = json_decode($buffer);
                    if ($i==0){
                        //store the header row
                        $header = $obj;
                    } else {
                        //echo, write to a file, queue a job, etc.

                        $mailChimpEmails[] = $obj[0];

                    }
                    $i++;
                }
            }
            fclose($handle);
        }

        return $mailChimpEmails;

    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // starting
        $output->writeln("<info>Starting to create users</info>");

        // get entity manager
        $em = $this->getContainer()->get('doctrine')->getManager();

        $mailChimpUsers = $this->getChimpUsers();

        // get users
        $users = $em->createQuery(" SELECT u.email, u.firstName, u.lastName, u.birthday, u.zipCode
                            FROM LBUserBundle:User u
                            WHERE u.email not in (:emails)
                            ")
            ->setParameter('emails', $mailChimpUsers)
            ->getResult()
        ;

         if($users){
            foreach($users as $user){

                $birthDay = $user['birthday'];

                // create mailchimp user Data
                $mailChimpData = [
                    'email'     => $user['email'],
                    'status'    => 'subscribed',
                    'firstname' => $user['firstName'] ? $user['firstName'] : '',
                    'lastname'  => $user['lastName'] ? $user['firstName'] : '',
                    'birthday'  => $birthDay ? $birthDay->format('m/d/Y') : '',
                    'zip_code'  => $user['zipCode']
                ];


                $sync = $this->getContainer()->get('app.mailchimp')->syncMailchimp($mailChimpData);

                $output->writeln("<info>$sync</info>");
            }
        }

        $output->writeln("<info>Success creating users</info>");

    }
}