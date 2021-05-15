<?php


namespace App\Twig;


use Symfony\Component\Intl\Countries;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TwigExtensions extends AbstractExtension
{
    public function getFilters(): array
    {
        $defaults = [
            'is_safe' => ['html'],
        ];

        return [
            new TwigFilter('convert_country_name', [$this, 'convertCountryName'], $defaults),
            new TwigFilter('md5_hash', '\md5', $defaults),
        ];
    }

    public function convertCountryName(string $countryCode, bool $isAlpha2 = true, string $displayLocale = 'fr'): string
    {
        return $isAlpha2 ?
            Countries::getName($countryCode, $displayLocale) :
            Countries::getName(Countries::getAlpha2Code($countryCode), $displayLocale);
    }
}
