<?php

namespace app\services;

use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_Filter;

class HtmlableText
{
    protected $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    public function toHtml()
    {
        $text = $this->text;

        if (! is_string($text)) {
            return '';
        }

        if (empty($text)) {
            return $text;
        }

        return $this->purify($text);
    }

    protected function purify($text)
    {
        $allowedTags = $this->getAllowedTags();

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', $allowedTags . ',div');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('Attr.EnableID', true); // Allow `id` attributes
        $config->set('AutoFormat.Linkify', true);
        $config->set('Filter.Custom', [$this->getLinksFilter()]);

        $purifier = new HTMLPurifier($config);

        return $purifier->purify($text);
    }

    protected function getLinksFilter()
    {
        return new class () extends HTMLPurifier_Filter {
            public $name = 'TargetBlankFilter';

            public function preFilter($html, $config, $context)
            {
                return preg_replace_callback('/<a\s+([^>]+)>/i', function ($matches) {
                    $attrs = $matches[1];

                    // Ensure target="_blank" is set
                    if (! preg_match('/\btarget=/', $attrs)) {
                        $attrs .= ' target="_blank"';
                    }

                    return '<a ' . $attrs . '>';
                }, $html);
            }

            public function postFilter($html, $config, $context)
            {
                return $html;
            }
        };
    }

    protected function getAllowedTags()
    {
        return collect(common_allowed_html_tags())->map(function ($attributes, $tag) {
            if (empty($attributes)) {
                return $tag;
            }

            return $tag . '[' . implode('|', $attributes) . ']';
        })->values()->implode(', ');
    }
}
