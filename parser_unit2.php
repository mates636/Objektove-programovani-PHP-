<?php declare(strict_types=1);

require 'parser.php';

use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testEmptySource(): void
    {
        $source = '';
        $expected = [];
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testHeaderOnly(): void
    {
        $source = '.IPPcode23';
        $expected = [
            new Header(),
        ];
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testInvalidHeader(): void
    {
        $source = '.IPPcode22';
        $expected = false;
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testMultipleHeaders(): void
    {
        $source = ".IPPcode23\n.IPPcode23";
        $expected = [
            new Header(),
            new NewLine(),
            new Header(),
        ];
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testComment(): void
    {
        $source = "# This is a comment.";
        $expected = [
        ];
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testVariable(): void
    {
        $source = "LF@%var";
        $expected = [
            new Variable(Frame::Local, "%var"),
        ];
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testConstant(): void
    {
        $source = "int@42";
        $expected = [
            new Constant(VarType::Int, "42"),
        ];
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testOperation(): void
    {
        $source = "ADD";
        $expected = [
            new Operation(Instruction::Add),
        ];
        $this->assertEquals($expected, $this->parse($source));
    }

    public function testLabel(): void
    {
        $source = "loop";
        $expected = [
            new Label("loop"),
        ];
        $this->assertEquals($expected, $this->parse($source));
    }

    private function parse(string $source): array|false
    {
        $analyzer = new LexicalAnalyzer();
        return $analyzer->analyze($source);
    }
}
