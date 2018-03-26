<?php
class ToolboxTest extends Common_TestCase {

   public $formatJson_input = [
      'test_text' => 'Lorem Ipsum',
      'test_number' => 1234,
      'test_float' => 1234.5678,
      'test_array' => [ 1,2,3,4, 'lorem_ipsum' ],
      'test_hash' => ['lorem' => 'ipsum', 'ipsum' => 'lorem']
   ];

   public $formatJson_expected = <<<JSON
{
    "test_text": "Lorem Ipsum",
    "test_number": 1234,
    "test_float": 1234.5678,
    "test_array": [
        1,
        2,
        3,
        4,
        "lorem_ipsum"
    ],
    "test_hash": {
        "lorem": "ipsum",
        "ipsum": "lorem"
    }
}
JSON;


   /**
    * @test
    */
   public function formatJson() {

      $this->assertEquals(
         $this->formatJson_expected,
         PluginFusioninventoryToolbox::formatJson(json_encode($this->formatJson_input))
      );
   }


   /**
    * @test
    */
   public function isAFusionInventoryDevice() {
      $computer = new Computer();

      $this->assertFalse(PluginFusioninventoryToolbox::isAFusionInventoryDevice($computer));

      $values = ['name'         => 'comp',
                 'is_dynamic'   => 1,
                 'entities_id'  => 0,
                 'is_recursive' => 0];
      $computers_id = $computer->add($values);
      $computer->getFromDB($computers_id);

      $this->assertFalse(PluginFusioninventoryToolbox::isAFusionInventoryDevice($computer));

      $pfComputer = new PluginFusioninventoryInventoryComputerComputer();
      $pfComputer->add(['computers_id' => $computers_id]);
      $this->assertTrue(PluginFusioninventoryToolbox::isAFusionInventoryDevice($computer));

      $printer = new Printer();
      $values  = ['name'         => 'printer',
                  'is_dynamic'   => 1,
                  'entities_id'  => 0,
                  'is_recursive' => 0];
      $printers_id = $printer->add($values);
      $printer->getFromDB($printers_id);
      $this->assertFalse(PluginFusioninventoryToolbox::isAFusionInventoryDevice($printer));

      $pfPrinter = new PluginFusioninventoryPrinter();
      $pfPrinter->add(['printers_id' => $printers_id]);
      $this->assertTrue(PluginFusioninventoryToolbox::isAFusionInventoryDevice($printer));

      $values  = ['name'         => 'printer2',
                  'is_dynamic'   => 0,
                  'entities_id'  => 0,
                  'is_recursive' => 0];
      $printers_id_2 = $printer->add($values);
      $printer->getFromDB($printers_id_2);
      $pfPrinter->add(['printers_id' => $printers_id_2]);
      $this->assertFalse(PluginFusioninventoryToolbox::isAFusionInventoryDevice($printer));

   }


   /**
    * @test
    */
   public function addDefaultStateIfNeeded() {
      $input = [];

      $state = new State();
      $states_id_computer = $state->importExternal('state_computer');
      $states_id_snmp = $state->importExternal('state_snmp');
      $states_id_printer = $state->importExternal('state_printer');
      $states_id_monitor = $state->importExternal('state_monitor');
      $states_id_peripheral = $state->importExternal('state_peripheral');

      $config = new PluginFusioninventoryConfig();
      $config->updateValue('states_id_snmp_default', $states_id_snmp);
      $config->updateValue('states_id_printer_default', $states_id_printer);
      $config->updateValue('states_id_monitor_default', $states_id_monitor);
      $config->updateValue('states_id_peripheral_default', $states_id_peripheral);
      $config->updateValue('states_id_default', $states_id_computer);


      $result = PluginFusioninventoryToolbox::addDefaultStateIfNeeded('computer', $input);
      $this->assertEquals(['states_id' => $states_id_computer], $result);

      $result = PluginFusioninventoryToolbox::addDefaultStateIfNeeded('snmp', $input);
      $this->assertEquals(['states_id' => $states_id_snmp], $result);

      $result = PluginFusioninventoryToolbox::addDefaultStateIfNeeded('printer', $input);
      $this->assertEquals(['states_id' => $states_id_printer], $result);

      $result = PluginFusioninventoryToolbox::addDefaultStateIfNeeded('monitor', $input);
      $this->assertEquals(['states_id' => $states_id_monitor], $result);

      $result = PluginFusioninventoryToolbox::addDefaultStateIfNeeded('peripheral', $input);
      $this->assertEquals(['states_id' => $states_id_peripheral], $result);

      $result = PluginFusioninventoryToolbox::addDefaultStateIfNeeded('foo', $input);
      $this->assertEquals([], $result);

   }


}
