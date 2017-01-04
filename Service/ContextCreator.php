<?php

namespace Terox\SmsCampaignBundle\Service;

class ContextCreator
{
    /**
     * @param string $context
     *
     * @return string
     */
    public function fromString($context)
    {
        if(empty($context)) {
            return [];
        }

        $pairs = explode(',', $context);

        $result = [];
        foreach($pairs as $kv) {
            $array = explode('=', $kv);
            $result[$array[0]] = trim($array[1]);
        }

        return $result;
    }
}