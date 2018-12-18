<?php
/**
 * @author Donjohn
 * @date 04/07/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\TokenParser;

use Donjohn\MediaBundle\Twig\Node\MediaNode;

/**
 * Class MediaTokenParser.
 */
class MediaTokenParser extends \Twig_TokenParser
{
    /**
     * @var string
     */
    protected $extensionName;

    /**
     * @param string $extensionName
     */
    public function __construct(string $extensionName)
    {
        $this->extensionName = $extensionName;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(\Twig_Token $token): MediaNode
    {
        $media = $this->parser->getExpressionParser()->parseExpression();
        $filter = new \Twig_Node_Expression_Constant(null, $token->getLine());
        $attributes = new \Twig_Node_Expression_Array(array(), $token->getLine());

        if ($this->parser->getStream()->nextIf(\Twig_Token::PUNCTUATION_TYPE)) {
            $filter = $this->parser->getExpressionParser()->parseExpression();
        }

        // attributes
        if ($this->parser->getStream()->nextIf(\Twig_Token::NAME_TYPE, 'with')) {
            $attributes = $this->parser->getExpressionParser()->parseExpression();
        }
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new MediaNode($this->extensionName, $media, $filter, $attributes, $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag(): string
    {
        return 'media';
    }
}
