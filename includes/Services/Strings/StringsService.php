<?php

namespace Smartcat\Includes\Services\Strings;

class StringsService
{
    public function getDomains(): array
    {
        $domains = icl_st_get_contexts(
            $this->getDomainsFilter()
        );

        return array_map(function ($d) {
            return $d->context;
        }, $domains);
    }

    public function export($locale, $domains): array
    {
        return smartcat_wpml()->getStrings($locale, $domains);
    }

    private function getDomainsFilter()
    {
        return filter_input(INPUT_GET, 'status', FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_NULL_ON_FAILURE);
    }
}