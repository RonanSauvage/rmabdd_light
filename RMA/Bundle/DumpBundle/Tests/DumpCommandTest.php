<?php

namespace RMA\Bundle\DumpBundle\Tests;

use RMA\Bundle\DumpBundle\Command\DumpCommand;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Kernel;

/**
 * Méthode de tests de la commande rma:dump:database
 *
 * @author rmA
 */
Class DumpCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        $kernel = $this->createKernel();
        $kernel->boot();

        $app = new Application($kernel);
        
        $app->add(new DumpCommand());
        
        $command = $app->find('rma:dump:database');
        $tester = new CommandTester($command);
        $tester->execute(array('command' => $command->getName()));
        $this->assertRegexp('mis à disposition', $tester->getDisplay());
    }
    
    /**
     * Gets Kernel mock instance
     *
     * @return Symfony\Component\HttpKernel\Kernel
     */
    private function getMockKernel()
    {
        return $this->getMock('Symfony\Component\HttpKernel\Kernel', array(), array(), '', false, false);
    }
    
            
        // We mock the DialogHelper
        /*$dialog = $this->getMock('Symfony\Component\Console\Helper\DialogHelper', array('askConfirmation'));
        $dialog->expects($this->at(0))
            ->method('askConfirmation')
            ->will($this->returnValue(true)); // The user confirms
            */
        // We override the standard helper with our mock
        //$command->getHelperSet()->set($dialog, 'dialog');
}