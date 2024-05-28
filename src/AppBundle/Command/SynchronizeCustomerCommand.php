<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 6/27/16
 * Time: 11:41 AM
 */

namespace AppBundle\Command;

use LB\PaymentBundle\Entity\Subscriber;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class SynchronizeCustomerCommand extends ContainerAwareCommand
{
    /**
     *
     */
    protected function configure()
    {
        $this
            ->setName('lv:synchronize:customer')
            ->setDescription('Synchronize customers with stripe')
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
        $output->writeln("<info>Starting ... </info>");

        $customerData = $this->parseCSV();
        $customersIds = array_map(function ($customer){return $customer[0];}, $customerData);

        $em = $this->getContainer()->get('doctrine')->getManager();
        $filters = $em->getFilters();
        $filters->isEnabled("user_deactivate_filter") ?  $filters->disable("user_deactivate_filter") : null;

        // get users
        $luvCustomers = $em->createQuery(" SELECT cus
                            FROM LBPaymentBundle:Customer cus
                            ")
            ->getResult()
        ;
        $i = 0;

        $filePath = __DIR__ . '/Files/deletedCustomers.txt';

        // create file
        $myFile = fopen($filePath, "a+");

        // check customer
        foreach ($luvCustomers as $luvCustomer){
            $stripeId = $luvCustomer->getStripeCustomerId();

            if(!in_array($stripeId, $customersIds)){
                $i ++;
                $output->writeln("<info>$stripeId</info>");

                $txt = "id = {$luvCustomer->getId()} \n";

                $customerData =$luvCustomer->getStripeCustomer();
                $customerData = json_encode($customerData);

                $planData = $luvCustomer->getStripeCustomer();
                $planData = json_encode($planData);

                $userId = $luvCustomer->getUser()->getId();

                $txt .= "stripeId = $stripeId \n";
                $txt .= "customerData = $customerData \n";
                $txt .= "planData = $planData \n";
                $txt .= "userId = $userId \n";

                fwrite($myFile, $txt);
                $txt = "============================================================================================\n";
                fwrite($myFile, $txt);
                $em->remove($luvCustomer);
                $output->writeln("<info>$i</info>");
            }
        }
        $em->flush();

        $output->writeln("<info>Finish ..</info>");
        $output->writeln("<info>$i</info>");

        return 1;

    }

    /**
     * @var array
     */
    private $csvParsingOptions = array(
//        'finder_in' => __DIR__. '/Files/',
        'finder_name' => 'customers.csv',
        'ignoreFirstLine' => true
    );

    /**
     * @return array
     */
    private function parseCSV()
    {
        $ignoreFirstLine = $this->csvParsingOptions['ignoreFirstLine'];

        $finder = new Finder();
        $finder->files()
            ->in($this->csvParsingOptions['finder_in'])
            ->name($this->csvParsingOptions['finder_name'])
        ;
        $csv = null;
        foreach ($finder as $file) { $csv = $file; }

        $rows = array();

        if($csv){
            if (($handle = fopen($csv->getRealPath(), "r")) !== FALSE) {
                $i = 0;
                while (($data = fgetcsv($handle, null, ",")) !== FALSE) {
                    $i++;
                    if ($ignoreFirstLine && $i == 1) { continue; }
                    $rows[] = $data;
                }
                fclose($handle);
            }
        }


        return $rows;
    }
}