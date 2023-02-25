<?php

class Parser
{

}


class LexicalAnalyzer
{
    
}

class Token
{
}

class Header extends Token
{
}

class NewLine extends Token
{
}

class Operation extends Token
{
}

class Operand extends Token
{
}

class Variable extends Operand
{
}

class Literal extends Operand
{
}

class Label extends  Operand
{
}

class Type extends Operand
{
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
