<?php

declare(strict_types=1);

namespace Pdir\ContaoSeoPlugin\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Model;
# use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsHook('isVisibleElement')]
class IsVisibleElementListener
{
    private RequestStack $requestStack;

    private ScopeMatcher $scopeMatcher;

    public function __construct(RequestStack $requestStack, ScopeMatcher $scopeMatcher)
    {
        $this->requestStack = $requestStack;
        $this->scopeMatcher = $scopeMatcher;
    }
    public function __invoke(Model $element, bool $isVisible): bool
    {
        // Is back end request
        if (!($request = $this->requestStack->getCurrentRequest()) || $this->scopeMatcher->isBackendRequest($request)) {

            // Check if condition is active
            if ($element->contaoSeoIsConditionalEntity && trim($element->contaoSeoEntityCondition) !== '') {
                $element->text = '<span style="color: var(--form-border, #7abfbc);" title="'.($GLOBALS['TL_LANG']['MSC']['contaoSeoActiveCondition']?? '').'">⚠（ ' .$element->contaoSeoEntityCondition.' ）</span>'.$element->text;
            }

            return $isVisible;
        }

        # $expressionLanguage = new ExpressionLanguage();
        # var_dump($expressionLanguage->evaluate($element->contaoSeoEntityCondition));

        // Check if this element can be shown
        if ($element->contaoSeoIsConditionalEntity && null !== $element->contaoSeoEntityCondition && trim($element->contaoSeoEntityCondition) !== '') {
            return eval('return '.$element->contaoSeoEntityCondition.';');
        }

        // Otherwise we don't want to change the visibility state
        return $isVisible;
    }
}
