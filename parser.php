<?php

class Parser
{
    var $errCode;
    public function run()
    {
        $source = stdin();
        $out = $this->parse($source);
        if ($out instanceof int) {
            exit($this->errCode);
        }
        echo $out;
    }
    public function parse($source)
    {
        $lex = new LexicalAnalyzer();
        $tokens = $lex->analyze($source);
        if (!$tokens) {
            $this->errCode = 23;
            return $this->errCode;
        }
        $insEls = $this->processAll($tokens);
        if (!$insEls) {
            return $this->errCode;
        }
        return $this->writeXML($insEls);
    }
    public function writeXML($insEls)
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument();
        $writer->startElement('program');
        $writer->startAttribute('language');
        $writer->text('IPPcode23');
        $writer->endAttribute();
        $writer->endElement();
        $order = 1;
        foreach ($insEls as $insEl) {

            $insEl->writeXML($writer, $order);
            $order++;
        }
        $writer->endDocument();
        return $writer->outputMemory();
    }
    public function processAll($tokens)
    {
        $insEls = [];
        if (count($tokens) == 0) {
            $this->errCode = 21;
            return false;
        }
        if ($tokens[0] != ".IPPcode23") {
            $this->errCode = 21;
            return false;
        }
        $tokens = array_slice($tokens, 1);
        $line = [];
        foreach ($tokens as $token) {
            if ($token instanceof NewLine) {
                if (count($line) > 0) {
                    $el = $this->processLine($line);
                    if (!$el) {
                        return false;
                    }
                    $insEls[] = $el;
                }
                $line = [];
            } else {
                $line[] = $token;
            }
        }
        if (count($line) > 0) {
            $el = $this->processLine($line);
            if (!$el) {
                return false;
            }
            $insEls[] = $el;
        }
        return $insEls;
    }
    public function processLine($tokens)
    {
        if (!($tokens[0] instanceof Operation)) {
            return false;
        }
        $ins = $tokens[0]->ins;
        $insEl = new InstructionElement();
        $insEl->opcode = $ins;
        switch ($ins) {
                //no args
            case Instruction::CreateFrame:
            case Instruction::PushFrame:
            case Instruction::PopFrame:
            case Instruction::Break:
            case Instruction::Return:
                break;
                // <var> argument
            case Instruction::DefVar:
            case Instruction::Pops:
                if (count($tokens) != 2) {
                    return false;
                }
                $arg = VarArg($tokens[1]);
                if (!$arg) {
                    return false;
                }
                $insEl->args[] = $arg;
                break;
                // <label> argument
            case Instruction::Label:
            case Instruction::Jump:
            case Instruction::Call:
                if (count($tokens) != 2) {
                    return false;
                }
                $arg = LabelArg($tokens[1]);
                if (!$arg) {
                    return false;
                }
                $insEl->args[] = $arg;
                break;
                break;
                // <symb> argument
            case Instruction::Pushs:
            case Instruction::Write:
            case Instruction::Exit:
            case Instruction::DPrint:
                if (count($tokens) != 2) {
                    return false;
                }
                $arg = SymbArg($tokens[1]);
                if (!$arg) {
                    return false;
                }
                $insEl->args[] = $arg;
                break;
                // <var> <symb> arguments
            case Instruction::Move:
            case Instruction::Not:
            case Instruction::Int2Char:
            case Instruction::StrLen:
            case Instruction::Type:
                if (count($tokens) != 3) {
                    return false;
                }
                $arg1 = VarArg($tokens[1]);
                if (!$arg1) {
                    return false;
                }
                $insEl->args[] = $arg1;
                $arg2 = SymbArg($tokens[2]);
                if (!$arg2) {
                    return false;
                }
                $insEl->args[] = $arg2;
                break;
                // <var> <type> arguments
            case Instruction::Read:
                if (count($tokens) != 3) {
                    return false;
                }
                $arg1 = VarArg($tokens[1]);
                if (!$arg1) {
                    return false;
                }
                $insEl->args[] = $arg1;
                $arg2 = SymbArg($tokens[2]);
                if (!$arg2) {
                    return false;
                }
                $insEl->args[] = $arg2;
                break;
                // <label> <symb1> <symb2> arguments
            case Instruction::JumpIfEq:
            case Instruction::JumpIfNeq:
                if (count($tokens) != 3) {
                    return false;
                }
                $arg1 = LabelArg($tokens[1]);
                if (!$arg1) {
                    return false;
                }
                $insEl->args[] = $arg1;
                $arg2 = SymbArg($tokens[2]);
                if (!$arg2) {
                    return false;
                }
                $insEl->args[] = $arg2;
                $arg3 = SymbArg($tokens[3]);
                if (!$arg3) {
                    return false;
                }
                $insEl->args[] = $arg3;
                break;
                // <var> <symb1> <symb2> arguments 
            case Instruction::Add:
            case Instruction::Sub:
            case Instruction::Mul:
            case Instruction::Idiv:
            case Instruction::Lt:
            case Instruction::Gt:
            case Instruction::Eq:
            case Instruction::And:
            case Instruction::Or:
            case Instruction::String2Char:
            case Instruction::Concat:
            case Instruction::GetChar:
            case Instruction::SetChar:
                if (count($tokens) != 3) {
                    return false;
                }
                $arg1 = VarArg($tokens[1]);
                if (!$arg1) {
                    return false;
                }
                $insEl->args[] = $arg1;
                $arg2 = SymbArg($tokens[2]);
                if (!$arg2) {
                    return false;
                }
                $insEl->args[] = $arg2;
                $arg3 = SymbArg($tokens[3]);
                if (!$arg3) {
                    return false;
                }
                $insEl->args[] = $arg3;
                break;
            default:
                fwrite(
                    STDERR,
                    "Invalid instruction passed, exiting...\n"
                );
                exit(22);
        }
        return $insEl;
    }
}



class InstructionElement
{
    public Instruction $opcode;
    public array $args;
    public function writeXML($writer, $order)
    {
        $num = 1;
        $writer->startElement('instruction');
        $writer->startAttribute('order');
        $writer->text($order);
        $writer->endAttribute();
        $writer->startAttribute('opcode');
        $writer->text($this->opcode->value);
        $writer->endAttribute();
        foreach ($this->args as $arg) {
            $arg->writeXML($writer, $num);
            $num++;
        }
        $writer->endElement();
    }
}

class ArgumentElement
{
    public string $type;
    public string $value;

    public function writeXML($writer, $num)
    {
        $writer->startElement("arg$num");
        $writer->startAttribute('type');
        $writer->text($this->type);
        $writer->endAttribute();
        $writer->text($this->value);
        $writer->endElement();
    }
}

class LexicalAnalyzer
{
    var $tokenorder = [
        Header::class, NewLine::class, Variable::class,
        Constant::class, Operation::class, Label::class
    ];
    public function analyze(string $source): array|bool
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
            echo $strtoken;
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
        if (preg_match("/^([A-Z]+)/", $token, $matches)) {
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
        if (preg_match("/^(LF|TF|GF)@([a-zA-Z_\$&%\-\*!\?][a-zA-Z_$&%\-\*!?0-9]*)/", $token, $matches)) {
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
        if (preg_match("/^(bool|int|string|nil)@(.*)/", $token, $matches)) {
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
        if (preg_match("/^([a-zA-Z_\$&%\-\*!\?][a-zA-Z_$&%\-\*!?0-9]*)/", $token, $matches)) {
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
        if (preg_match("/^(bool|int|string|nil)/", $token, $matches)) {
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
        //no args
    case CreateFrame = 'CREATEFRAME';
    case PushFrame = 'PUSHFRAME';
    case PopFrame = 'POPFRAME';
    case Break = 'BREAK';
    case Return = 'RETURN';
        //<var> arg
    case Pops = 'POPS';
    case DefVar = 'DEFVAR';
        //<label> arg
    case Label = 'LABEL';
    case Call = 'CALL';
    case Jump = 'JUMP';
        //<symb> arg
    case Pushs = 'PUSHS';
    case Write = 'WRITE';
    case Exit = 'EXIT';
    case DPrint = 'DPRINT';
        //<var> <symb> args
    case Not = 'NOT';
    case Int2Char = 'INT2CHAR';
    case Move = 'MOVE';
    case StrLen = 'STRLEN';
    case Type = 'TYPE';
        //<var> <type> arguments
    case Read = 'READ';
        //<label> <symb1> <symb2> arguments
    case JumpIfEq = 'JUMPIFEQ';
    case JumpIfNeq = 'JUMPIFNEQ';
        //<var> <symb1>     <symb2> arguments 
    case Add = 'ADD';
    case Sub = 'SUB';
    case Mul = 'MUL';
    case Idiv = 'IDIV';
    case Lt = 'LT';
    case Gt = 'GT';
    case Eq = 'EQ';
    case And = 'AND';
    case Or = 'OR';
    case String2Char = 'STRING2CHAR';
    case Concat = 'CONCAT';
    case GetChar = 'GETCHAR';
    case SetChar = 'SETCHAR';
}

function NoArguments()
{
}

function VarArg($token)
{
    if (!($token instanceof Variable)) {
        return false;
    }
    $frame = $token->frame->value;
    $varname = $token->varname;
    $l = new ArgumentElement();
    $l->type = "var";
    $l->value = "$frame@$varname";
    return $l;
}
function LabelArg($token)
{
    if (preg_match("/^([a-zA-Z_\$&%\-\*!\?][a-zA-Z_$&%\-\*!?0-9]*)/", $token)) {
        $label = $token;
        $l = new ArgumentElement();
        $l->type = "label";
        $l->value = "$label";
    }
    return $l;
}

function SymbArg($token)
{
    if (!($token instanceof VarType)) {
        return false;
    }
    $constant = $token->constant->value;
    $varname = $token->varname;
    $l = new ArgumentElement();
    $l->type = "$constant";
    $l->value = "$varname";
    return $l;
}

function VarSymArg()
{
}

function VarTypeArg()
{
}

function LabelSymSymArg()
{
}

function VarSymSymArg()
{
}
