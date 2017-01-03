<?php
/**
 * @author jgn
 * @date 04/07/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\TokenParser;




use Donjohn\MediaBundle\Twig\Node\PathNode;

class PathTokenParser extends \Twig_TokenParser
{
    protected $extensionName;

    /**
     * @param string $extensionName
     */
    public function __construct($extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(\Twig_Token $token)
    {
        $media = $this->parser->getExpressionParser()->parseExpression();
        $filter = new \Twig_Node_Expression_Constant('reference',$token->getLine());

        if (!$this->parser->getStream()->nextIf(\Twig_Token::BLOCK_END_TYPE)) {
            $this->parser->getStream()->next();
            $filter = $this->parser->getExpressionParser()->parseExpression();
            $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);
        }

        return new PathNode($this->extensionName, $media, $filter, $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag()
    {
        return 'path';
    }
}
