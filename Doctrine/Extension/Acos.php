<?php

namespace App\AppBundle\Doctrine\Extension;

use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;

class Acos extends FunctionNode {

    public $arithmeticExpression;

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker) {

        return 'ACOS(' . $sqlWalker->walkSimpleArithmeticExpression(
                        $this->arithmeticExpression
                ) . ')';
    }

    public function parse(\Doctrine\ORM\Query\Parser $parser) {

        $lexer = $parser->getLexer();

        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->arithmeticExpression = $parser->SimpleArithmeticExpression();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

}
