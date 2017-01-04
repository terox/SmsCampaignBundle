<?php

namespace Terox\SmsCampaignBundle\Service;

class ContextReplacer
{
    /**
     * @param string $content
     * @param array  $context
     *
     * @return string
     */
    public function replace($content, array $context)
    {
        foreach($context as $key => $value) {
            $content = str_replace('%'.$key.'%', $value, $content);
        }

        return $content;
    }
}