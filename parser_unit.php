<?php
require 'parser.php';

use PHPUnit\Framework\TestCase;
use PSpell\Config;

class LexicalAnalyzerTest extends TestCase
{
    public function assertAnalyze(string $input, array|bool $expectedtokens): void
    {
        $analyzer = new LexicalAnalyzer();
        $expectedtokensstr = print_r($expectedtokens, true);
        $actualTokens = $analyzer->analyze($input);
        $actualtokensstr = print_r($actualTokens, true);
        $this->assertEquals($expectedtokens, $actualTokens, "expected: $expectedtokensstr, actual: $actualtokensstr");
    }
    public function testAnalyze(): void
    {

        $testcase = <<<XML
        .IPPcode23
        DEFVAR GF@counter
        MOVE GF@counter string@ # Inicializace proměnné na prázdný řetězec
        # Jednoduchá iterace , dokud nebude splněna zadaná podmínka
        LABEL while
        JUMPIFEQ end GF@counter string@aaa
        WRITE string@Proměnná\\032GF@counter\\032obsahuje\\032
        WRITE GF@counter
        WRITE string@\\010
        CONCAT GF@counter GF@counter string@a
        JUMP while
        LABEL end
        XML;

        $expected = [
            new Header(), new NewLine(),
            new Operation(Instruction::DefVar), new Variable(Frame::Global, "counter"), new NewLine(),
            new Operation(Instruction::Move), new Variable(Frame::Global, "counter"), new Constant(VarType::String, ""), new NewLine(), new NewLine(),
            new Operation(Instruction::Label), new Label("while"), new NewLine(),
            new Operation(Instruction::JumpIfEq), new Label("end"), new Variable(Frame::Global, "counter"), new Constant(VarType::String, "aaa"), new NewLine(),
            new Operation(Instruction::Write), new Constant(VarType::String, "Proměnná\\032GF@counter\\032obsahuje\\032"), new NewLine(),
            new Operation(Instruction::Write), new Variable(Frame::Global, "counter"), new NewLine(),
            new Operation(Instruction::Write), new Constant(VarType::String, "\\010"), new NewLine(),
            new Operation(Instruction::Concat), new Variable(Frame::Global, "counter"), new Variable(Frame::Global, "counter"), new Constant(VarType::String, "a"), new NewLine(),
            new Operation(Instruction::Jump), new Label("while"), new NewLine(),
            new Operation(Instruction::Label), new Label("end")
        ];
        $cases = [
            [
                "-9askfdljrsk", [new Label("-9askfdljrsk")]
            ],
            [
                "9askfdljrsk", false
            ],
            [
                "$&%-?", [new Label("$&%-?")]
            ],
            [
                $testcase, $expected
            ],
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
