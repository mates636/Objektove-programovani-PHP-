<?php

class Parser
{
}


class LexicalAnalyzer
{
    var $tokenorder = [
        Header::class, NewLine::class, Variable::class,
        Constant::class, Operation::class, Label::class
    ];
    public function analyze(string $source): array
    {
        $result = [];
        $comment = '/#.*/';
        $empty_string = '';
        $newphrase = preg_replace($comment, $empty_string, $source);
        $pattern = '/[\t\f\r ]+/';
        $tokenstmp = preg_split(
            $pattern,
            $newphrase,
            -1,
            PREG_SPLIT_NO_EMPTY
        );
        $str_tokens = [];
        foreach ($tokenstmp as $token) {
            $splittokens = preg_split("/(\n)/", $token, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $str_tokens = array_merge($str_tokens, $splittokens);
        }
        foreach ($str_tokens as $strtoken) {

            $found = false;
            foreach ($this->tokenorder as $type) {
                $parsedtoken =  $type::parse($strtoken);
                if ($parsedtoken) {
                    $found = true;
                    $result[] = $parsedtoken;
                    break;
                }
            }
            if ($found == false) {
                return false;
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
        if ($token == "\n")
            return new NewLine();
        else
            return false;
    }
}

class Operation extends Token
{
    public Instruction $ins;
    public function __construct(Instruction $ins)
    {
        $this->ins = $ins;
    }
    public static function parse(string $token): Token|false
    {
        if (preg_match("/([A-Z]+)/", $token, $matches)) {
            $ins = Instruction::tryFrom($matches[1]);
            if ($ins == null) {
                return false;
            }
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
        if (preg_match("/^(LF|TF|GF)@([a-zA-Z_$&%-*!?][a-zA-Z_$&%-*!?0-9]*)/", $token, $matches)) {
            $frame = Frame::from($matches[1]);
            $varname = $matches[2];
            return new Variable($frame, $varname);
        } else
            return false;
    }
}

class Constant extends Operand
{
    public VarType $constant;
    public string $varname;
    public function __construct(VarType $constant, string $varname)
    {
        $this->constant = $constant;
        $this->varname = $varname;
    }
    public static function parse(string $token): Token|false
    {
        if (preg_match("/(bool|int|string|nil)@(.*)/", $token, $matches)) {
            $constant = VarType::from($matches[1]);
            $varname = $matches[2];
            return new Constant($constant, $varname);
        } else
            return false;
    }
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
        if (preg_match("/([a-zA-Z_$&%-*!?0-9]*)/", $token, $matches)) {
            $label = $matches[0];
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
            $vartype = VarType::from($matches[1]);
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
    case Label = 'LABEL';
    case Int2Char = 'INT2CHAR';
    case String2Char = 'STRING2CHAR';
    case Read = 'READ';
    case Write = 'WRITE';
    case Concat = 'CONCAT';
    case StrLen = 'STRLEN';
    case GetChar = 'GETCHAR';
    case SetChar = 'SETCHAR';
    case Type = 'TYPE';
    case Jump = 'JUMP';
    case JumpIfEq = 'JUMPIFEQ';
    case JumpIfNeq = 'JUMPIFNEQ';
    case Exit = 'EXIT';
    case DPrint = 'DPRINT';
    case Break = 'BREAK';
}
