<?php

class Parser
{
}


class LexicalAnalyzer
{
    var $tokenorder = [Header::class, Variable::class, Label::class, Type::class];
    public function analyze(string $source): array
    {
        $result = [];
        $comment = '/#.*/';
        $empty_string = '';
        $newphrase = str_replace($comment, $empty_string, $source);
        $pattern = '/[\t\f\r ]+/';
        $str_tokens = preg_split(
            $pattern,
            $newphrase,
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        foreach ($str_tokens as $strtoken) {
            foreach ($this->tokenorder as $type) {
                $parsedtoken =  $type::parse($strtoken);
                if ($parsedtoken) {
                    $result[] = $parsedtoken;
                    break;
                }
            }
        }
        return $result;
    }
}

class Token
{
    public static function parse(string $token): Token|false
    {
        return false;
    }
}

class Header extends Token
{
    public static function parse(string $token): Token|false
    {
        if (preg_match("/^\.IPPcode23$/", $token))
            return new Header();
        else
            return false;
    }
}

class NewLine extends Token
{
    public static function parse(string $token): Token|false
    {
        if (preg_match("/$/", $token))
            return new NewLine();
        else
            return false;
    }
}

class Operation extends Token
{
    public Instruction $ins;
    public function __construct(Operation $ins)
    {
        $this->ins = $ins;
    }
    public static function parse(string $token): Token|false
    {
        if (preg_match("/([A-Z]+)/", $token, $matches)) {
            $ins = Instruction::from($matches[1]);
            return new Operation($ins);
        } else
            return false;
    }
}

class Operand extends Token
{
}

class Variable extends Operand
{
    public Frame $frame;
    public string $varname;
    public function __construct(Frame $frame, string $varname)
    {
        $this->frame = $frame;
        $this->varname = $varname;
    }
    public static function parse(string $token): Token|false
    {
        if (preg_match("/(LF|TF|GF)@([a-zA-Z_$&%-*!?][a-zA-Z_$&%-*!?0-9]*)/", $token, $matches)) {
            $frame = Frame::from($matches[1]);
            $varname = $matches[2];
            return new Variable($frame, $varname);
        } else
            return false;
    }
}

class Literal extends Operand
{
}

class Label extends  Operand
{
    public string $label;
    public function __construct(string $label)
    {
        $this->label = $label;
    }
    public static function parse(string $token): Token|false
    {
        if (preg_match("/(LABEL [a-zA-Z_$&%-*!?0-9]*)/", $token, $matches)) {
            $label = $matches[1];
            return new Label($label);
        } else
            return false;
    }
}

class Type extends Operand
{
    public VarType $vartype;
    public function __construct(VarType $vartype)
    {
        $this->vartype = $vartype;
    }
    public static function parse(string $token): Token|false
    {
        if (preg_match("/(bool|int|string|nil)/", $token, $matches)) {
            $vartype = VarType::from($matches[0]);
            return new Type($vartype);
        } else
            return false;
    }
}

enum VarType: string
{
    case Bool = 'bool';
    case Int = 'int';
    case String = 'string';
    case Nil = 'nil';
}

enum Frame: string
{
    case Global = 'GF';
    case Local = 'LF';
    case Temporary = 'TF';
}

enum Instruction: string
{
    case Move = 'MOVE';
    case CreateFrame = 'CREATEFRAME';
    case PushFrame = 'PUSHFRAME';
    case PopFrame = 'POPFRAME';
    case DefVar = 'DEFVAR';
    case Call = 'CALL';
    case Return = 'RETURN';
    case Pushs = 'PUSHS';
    case Pops = 'POPS';
    case Add = 'ADD';
    case Sub = 'SUB';
    case Mul = 'MUL';
    case Idiv = 'IDIV';
    case Lt = 'LT';
    case Gt = 'GT';
    case Eq = 'EQ';
    case And = 'AND';
    case Or = 'OR';
    case Not = 'NOT';
    case Int2Char = 'INT2CHAR';
    case String2Char = 'STRING2CHAR';
    case Read = 'READ';
    case Write = 'WRITE';
    case Concat = 'CONCAT';
    case StrLen = 'STRLEN';
    case GetChar = 'GETCHAR';
    case SetChar = 'SETCHAR';
    case Type = 'TYPE';
    case Label = 'LABEL';
    case Jump = 'JUMP';
    case JumpIfEq = 'JUMPIFEQ';
    case JumpIfNeq = 'JUMPIFNEQ';
    case Exit = 'EXIT';
    case DPrint = 'DPRINT';
    case Break = 'BREAK';
}
