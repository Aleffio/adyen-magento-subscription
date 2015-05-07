<?php
/**
 * Ho_Recurring
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE_HO.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category  Ho
 * @package   Ho_Recurring
 * @author    Paul Hachmang – H&O <info@h-o.nl>
 * @copyright 2015 Copyright © H&O (http://www.h-o.nl/)
 * @license   H&O Commercial License (http://www.h-o.nl/license)
 */

require_once 'abstract.php';

class Ho_Recurring_Shell extends Mage_Shell_Abstract
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
   	public function convertOrderToProfileAction() {
		if (! $order = $this->getArg('order')) {
			exit("Please specifity an --order 12345\n");
		}

		$order = Mage::getModel('sales/order')->loadByIncrementId($order);
		if (! $order->getId()) {
			exit("Could not load order\n");
		}


		Mage::getSingleton('ho_recurring/service_order')->createProfile($order);
   	}
}


$shell = new Ho_Recurring_Shell();
$shell->run();