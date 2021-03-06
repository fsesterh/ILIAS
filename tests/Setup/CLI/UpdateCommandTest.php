<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\Setup\CLI;

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;

class UpdateCommandTest extends TestCase
{
    public function testBasicFunctionality()
    {
        $this->basicFunctionality(false);
    }

    public function testBasicFunctionalityAlreadyAchieved()
    {
        $this->basicFunctionality(true);
    }
    public function basicFunctionality(bool $is_applicable) : void
    {
        $refinery = new Refinery($this->createMock(DataFactory::class), $this->createMock(\ilLanguage::class));

        $agent = $this->createMock(Setup\AgentCollection::class);
        $config_reader = $this->createMock(Setup\CLI\ConfigReader::class);
        $agent_finder = $this->createMock(Setup\AgentFinder::class);
        $command = new Setup\CLI\UpdateCommand($agent_finder, $config_reader, []);

        $tester = new CommandTester($command);

        $config = $this->createMock(Setup\ConfigCollection::class);
        $config_file = "config_file";
        $config_file_content = ["config_file"];

        $objective = $this->createMock(Setup\Objective::class);
        $env = $this->createMock(Setup\Environment::class);

        $config_reader
            ->expects($this->once())
            ->method("readConfigFile")
            ->with($config_file)
            ->willReturn($config_file_content);

        $agent_finder
            ->expects($this->once())
            ->method("getAgents")
            ->with()
            ->willReturn($agent);

        $agent
            ->expects($this->once())
            ->method("getArrayToConfigTransformation")
            ->with()
            ->willReturn($refinery->custom()->transformation(function ($v) use ($config_file_content, $config) {
                $this->assertEquals($v, $config_file_content);
                return $config;
            }));

        $agent
            ->expects($this->never())
            ->method("getInstallObjective")
            ->with($config)
            ->willReturn(new Setup\Objective\NullObjective());

        $agent
            ->expects($this->never())
            ->method("getBuildArtifactObjective")
            ->with()
            ->willReturn(new Setup\Objective\NullObjective());

        $agent
            ->expects($this->once())
            ->method("getUpdateObjective")
            ->with($config)
            ->willReturn($objective);

        $objective
            ->expects($this->once())
            ->method("getPreconditions")
            ->willReturn([]);

        $expects = $this->never();
        $return = false;

        if ($is_applicable) {
            $expects = $this->once();
            $return = true;
        }

        $objective
            ->expects($expects)
            ->method("achieve")
            ->willReturn($env);

        $objective
            ->expects($this->once())
            ->method("isApplicable")
            ->willReturn($return);
        
        $tester->execute([
            "config" => $config_file
        ]);
    }
}
