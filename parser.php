<?php

class Parser
{
}


class LexicalAnalyzer
{
    public function analyze(string $source): array
    {
        $comment = '#.*';
        $empty_string = '';
        $newphrase = str_replace($comment, $empty_string, $source);
        $pattern = '[\t\f\r ]+';
        $str_tokens = preg_split(
            $pattern,
            $newphrase,
        );
      // foreach($str_tokens){
      //     
      // }
       return array();
    }
}

class Token
{
    public static function parse(string $toekn): Token|false
    {
        return false;
    }
}

class Header extends Token
{
    public static function parse(string $token): Token|false
    {
        if (preg_match("^\.IPPcode23$", $token))
            return new Header();
        else
            return false;
    }
}

class NewLine extends Token
{
    public static function parse(string $token): Token|false
    {
        if (preg_match("$", $token))
            return new NewLine();
        else
            return false;
    }
}

class Operation extends Token
{
}

class Operand extends Token
{
    
}

class Variable extends Operand
{
    public static function parse(string $token): Token|false{
        if (preg_match("(LF|TF|GF)@[a-zA-Z_$&%-*!?][a-zA-Z_$&%-*!?0-9]*", $token))
            return new Variable();
        else
            return false;
    }
}

class Literal extends Operand
{
}

class Label extends  Operand
{
    public static function parse(string $token): Token|false{
        if (preg_match("LABEL [a-zA-Z_$&%-*!?][a-zA-Z_$&%-*!?0-9]*/gm", $token))
            return new Operand();
    }
}

class Type extends Operand
{
    public static function parse(string $token): Token|false{
        if(preg_match("(bool|int|string|nil)", $token))
            return new Type();
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
