<?php

namespace StackWirtz\WordpressPlugin;

use StackWirtz\WordpressPlugin\Models\WirtzData;

class StaticReports
{

    static function groupByYearProductionStats(array $data): array
    {
        $result = [];

        foreach ($data as $entry) {
            $year = $entry['Year'];
            $production = $entry['Production'];
            
            $career = (!isset($entry['Career']) || $entry['Career'] === '' || $entry['Career'] === null) ? 'Empty' : $entry['Career'];
            $team = (!isset($entry['Team']) || $entry['Team'] === '' || $entry['Team'] === null) ? 'Empty' : $entry['Team'];
            $grad = (!isset($entry['Grad']) || $entry['Grad'] === '' || $entry['Grad'] === null) ? 'Empty' : $entry['Grad'];

            if (!isset($result[$year])) {
                $result[$year] = [];
            }
            if (!isset($result[$year][$production])) {
                $result[$year][$production] = [
                    'Career' => [],
                    'Team' => [],
                    'Grad' => []
                ];
            }

            // Count Career
            if (!isset($result[$year][$production]['Career'][$career])) {
                $result[$year][$production]['Career'][$career] = 0;
            }
            $result[$year][$production]['Career'][$career]++;

            // Count Team
            if (!isset($result[$year][$production]['Team'][$team])) {
                $result[$year][$production]['Team'][$team] = 0;
            }
            $result[$year][$production]['Team'][$team]++;

            // Count Grad
            if (!isset($result[$year][$production]['Grad'][$grad])) {
                $result[$year][$production]['Grad'][$grad] = 0;
            }
            $result[$year][$production]['Grad'][$grad]++;
        }

        // Sort by Year and Production, then sort the unique items in each category
        ksort($result);
        foreach ($result as $year => $productions) {
            ksort($productions);
            foreach ($productions as $production => $stats) {
                ksort($result[$year][$production]['Team']);
                ksort($result[$year][$production]['Career']);
                ksort($result[$year][$production]['Grad']);
            }
        }

        return $result;
    }

    static function renderYearProductionStatsAsHTML(array $yearProductionStats): string
    {
        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates');
        $twig = new \Twig\Environment($loader);
        
        $twig->addFilter(new \Twig\TwigFilter('repeat', function ($string, $times) {
            return str_repeat($string, $times);
        }));
        
        return $twig->render('year-production-stats.html.twig', [
            'yearProductionStats' => $yearProductionStats
        ]);
    }
}
