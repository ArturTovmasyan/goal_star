<?php
/**
 * Created by PhpStorm.
 * User: aram
 * Date: 9/19/16
 * Time: 12:59 PM
 */
namespace AppBundle\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\SqlWalker;
use Doctrine\ORM\Query\Parser;

/**
 * Class SubstringIndex
 * @package AppBundle\DQL
 */
class SubstringIndex extends FunctionNode
{
    public $str = null;
    public $delim = null;
    public $count = null;

    /**
     * @override
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'SUBSTRING_INDEX(' .
        $this->str->dispatch($sqlWalker) . ', ' .
        $this->delim->dispatch($sqlWalker) . ', ' .
        $this->count->dispatch($sqlWalker) .
        ')';
    }

    /**
     * @override
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->str = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->delim = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->count = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}