<?php
require 'parser.php';

use PHPUnit\Framework\TestCase;

class LexicalAnalyzerTest extends TestCase
{
    public function assertAnalyze(string $input, array $expectedtokens): void
    {
        $analyzer = new LexicalAnalyzer();

        // Test with empty string
        $this->assertEquals($expectedtokens, $analyzer->analyze($input));
    }
    public function testAnalyze(): void
    {
        $cases = [
            [
                ".IPPcode23", [new Header]
            ],
           //[
           //    "", []
           //],
           //[
           //    "\n", [new NewLine()]
           //],
            [
                "GF@gg", [new Variable(Frame::Global, "gg")]
            ],
            [
                "bool", [new Type(VarType::Bool)]
            ],
            [
                "LABEL gg", [new Label("LABEL gg")]
            ],
            [
                "WRITE", [new Operation(Instruction::Write)]
            ]
        ];

        foreach ($cases as $case) {
            $this->assertAnalyze($case[0], $case[1]);
        }
    }
}

class VariableTest extends TestCase
{
    public function testParse(): void
    {
        // Test with a valid variable token
        $token = "LF@x";
        $var = Variable::parse($token);
        $this->assertInstanceOf(Variable::class, $var);
        $this->assertEquals(Frame::Local, $var->frame);
        $this->assertEquals("x", $var->varname);

        // Test with an invalid variable token
        $token = "not a variable token";
        $var = Variable::parse($token);
        $this->assertFalse($var);

        // Add more tests for other operand types
        // ...
    }
}
