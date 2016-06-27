<?php
/**
 *                       ######
 *                       ######
 * ############    ####( ######  #####. ######  ############   ############
 * #############  #####( ######  #####. ######  #############  #############
 *        ######  #####( ######  #####. ######  #####  ######  #####  ######
 * ###### ######  #####( ######  #####. ######  #####  #####   #####  ######
 * ###### ######  #####( ######  #####. ######  #####          #####  ######
 * #############  #############  #############  #############  #####  ######
 *  ############   ############  #############   ############  #####  ######
 *                                      ######
 *                               #############
 *                               ############
 *
 * Adyen Subscription module (https://www.adyen.com/)
 *
 * Copyright (c) 2015 H&O E-commerce specialists B.V. (http://www.h-o.nl/)
 * See LICENSE.txt for license details.
 *
 * Author: Adyen <magento@adyen.com>, H&O E-commerce specialists B.V. <info@h-o.nl>
 */

require_once 'abstract.php';

class Adyen_Subscription_Shell extends Mage_Shell_Abstract
{

    /**
   	 * Run script
   	 *
   	 * @return void
   	 */
   	public function run() {
   		$action = $this->getArg('action');
   		if (empty($action)) {
   			echo $this->usageHelp();
   		} else {
   			$actionMethodName = $action.'Action';
   			if (method_exists($this, $actionMethodName)) {
   				$this->$actionMethodName();
   			} else {
   				echo "Action $action not found!\n";
   				echo $this->usageHelp();
   				exit(1);
   			}
   		}
   	}

    /**
   	 * Retrieve Usage Help Message
   	 *
   	 * @return string
   	 */
   	public function usageHelp() {
   		$help = 'Available actions: ' . "\n";
   		$methods = get_class_methods($this);
   		foreach ($methods as $method) {
   			if (substr($method, -6) == 'Action') {
   				$help .= '    -action ' . substr($method, 0, -6);
   				$helpMethod = $method.'Help';
   				if (method_exists($this, $helpMethod)) {
   					$help .= $this->$helpMethod();
   				}
   				$help .= "\n";
   			}
   		}
   		return $help;
   	}


    /**
   	 * @return void
   	 */
   	public function convertOrderToSubscriptionAction() {
		if (! $order = $this->getArg('order')) {
			exit("Please specifity an --order 12345\n");
		}

		$order = Mage::getModel('sales/order')->loadByIncrementId($order);
		if (! $order->getId()) {
			exit("Could not load order\n");
		}


		Mage::getSingleton('adyen_subscription/service_order')->createSubscription($order);
   	}


	public function createQuotesAction()
	{
		$message = Mage::getSingleton('adyen_subscription/cron')->createQuotes();
		echo $message."\n";
	}

	public function createOrdersAction()
	{
		$message = Mage::getSingleton('adyen_subscription/cron')->createOrders();
		echo $message."\n";
	}

    /**
     * Generate README.md file as index of all README.md files in module
     */
    public function generateReadmeIndexAction()
    {
        $baseDir = Mage::getBaseDir();
        $moduleDir = '/app/code/community/Adyen/Subscription/';
        $files = $this->rsearch($baseDir . $moduleDir, '/.*README.md/');

        $fileNames = [];
        foreach ($files as $filename) {
            $handle = fopen($filename, 'r');
            $content = fread($handle, filesize($filename));
            preg_match('# (.+) #', $content, $matches);
            $fileNames[substr($filename, strlen($baseDir), strlen($filename))] = $matches[0];
        }

        $readme = fopen($baseDir . $moduleDir . 'README.md', 'w');
        fwrite($readme, '# Index of README.md files #' . PHP_EOL . PHP_EOL);
        foreach ($fileNames as $filename => $fileHeader) {
            if ($moduleDir . 'README.md' == $filename) {
                continue;
            }

            $line = '[' . trim($fileHeader) . '](' . trim($filename) . ')  ' . PHP_EOL;

            fwrite($readme, $line);
        }
        fclose($readme);

        echo 'Finished generating README.md index: ' . $moduleDir . 'README.md' . PHP_EOL;
    }

    /**
     * @param string $folder
     * @param string $pattern
     * @return array
     */
    public function rsearch($folder, $pattern)
    {
        $dir = new RecursiveDirectoryIterator($folder);
        $ite = new RecursiveIteratorIterator($dir);
        $files = new RegexIterator($ite, $pattern, RegexIterator::GET_MATCH);
        $fileList = array();
        foreach($files as $file) {
            $fileList = array_merge($fileList, $file);
        }
        return $fileList;
    }
}


$shell = new Adyen_Subscription_Shell();
$shell->run();