<?php


use PHPUnit\Framework\TestCase;
use PSpell\Config;
require 'parser.php';

class ParserTest extends PHPUnit_Framework_TestCase {

public function testProcessLine() {
    $parser = new Parser();
    $tokens = array(new Operation('CreateFrame'));
    $instruction = $parser->processLine($tokens);
    $this->PassertEquals($instruction->opcode->value, 'CREATEFRAME');

    $tokens = array(new Operation('DefVar'), new Variable('GF@var'));
    $instruction = $parser->processLine($tokens);
    $this->assertEquals($instruction->opcode->value, 'DEFVAR');
    $this->assertEquals($instruction->args[0]->name, 'GF@var');

    $tokens = array(new Operation('Pops'), new Variable('LF@var'));
    $instruction = $parser->processLine($tokens);
    $this->assertFalse($instruction);

    $tokens = array(new Operation('Label'), new Label('myLabel'));
    $instruction = $parser->processLine($tokens);
    $this->assertEquals($instruction->opcode->value, 'LABEL');
    $this->assertEquals($instruction->args[0]->name, 'myLabel');

    $tokens = array(new Operation('Pushs'), new Constant('int', 42));
    $instruction = $parser->processLine($tokens);
    $this->assertEquals($instruction->opcode->value, 'PUSHS');
    $this->assertEquals($instruction->args[0]->type, 'int');
    $this->assertEquals($instruction->args[0]->value, 42);

    $tokens = array(new Operation('Move'), new Variable('GF@var'), new Constant('bool', true));
    $instruction = $parser->processLine($tokens);
    $this->assertEquals($instruction->opcode->value, 'MOVE');
    $this->assertEquals($instruction->args[0]->name, 'GF@var');
    $this->assertEquals($instruction->args[1]->type, 'bool');
    $this->assertEquals($instruction->args[1]->value, true);
}
}

class InstructionElementTest extends PHPUnit_Framework_TestCase {

public function testWriteXML() {
    $writer = new XMLWriter();
    $instruction = new InstructionElement();
    $instruction->opcode = Instruction::CreateFrame;
    $instruction->args = array();
    $instruction->writeXML($writer, 1);
    $this->assertEquals($writer->outputMemory(true), '<instruction opcode="CREATEFRAME" order="1"/>');

    $writer = new XMLWriter();
    $instruction = new InstructionElement();
    $instruction->opcode = Instruction::DefVar;
    $instruction->args = array(new Variable('GF@var'));
    $instruction->writeXML($writer, 2);
    $this->assertEquals($writer->outputMemory(true), '<instruction opcode="DEFVAR" order="2"><arg1 type="var">GF@var</arg1></instruction>');

    $writer = new XMLWriter();
    $instruction = new InstructionElement();
    $instruction->opcode = Instruction::Pushs;
    $instruction->args = array(new Constant('int', 42));
    $instruction->writeXML($writer, 3);
    $this->assertEquals($writer->outputMemory(true), '<instruction opcode="PUSHS" order="3"><arg1 type="int">42</arg1></instruction>');
}
}
