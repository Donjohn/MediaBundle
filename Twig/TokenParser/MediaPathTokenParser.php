<?php
/**
 * @author jgn
 * @date 04/07/2016
 * @description For ...
 */

namespace Donjohn\MediaBundle\Twig\TokenParser;

use Donjohn\MediaBundle\Twig\Node\MediaPathNode;

/**
 * Class MediaPathTokenParser.
 */
class MediaPathTokenParser extends \Twig_TokenParser
{
    /** @var string $extensionName */
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
    public function parse(\Twig_Token $token): MediaPathNode
    {
        $media = $this->parser->getExpressionParser()->parseExpression();
        $filter = new \Twig_Node_Expression_Constant(null, $token->getLine());
        $fullPath = new \Twig_Node_Expression_Constant(false, $token->getLine());

        if ($this->parser->getStream()->nextIf(\Twig_Token::PUNCTUATION_TYPE)) {
            $filter = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($this->parser->getStream()->nextIf(\Twig_Token::PUNCTUATION_TYPE)) {
            $fullPath = $this->parser->getExpressionParser()->parseExpression();
        }
        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new MediaPathNode($this->extensionName, $media, $filter, $fullPath, $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag(): string
    {
        return 'media_path';
    }
}
