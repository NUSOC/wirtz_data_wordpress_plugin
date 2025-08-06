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

        // Sort the unique items in each category
        foreach ($result as $year => $productions) {
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
        $content = '';

        foreach ($yearProductionStats as $year => $productions) {
            $content .= '<h2>' . esc_html($year) . '</h2>';
            
            foreach ($productions as $production => $stats) {
                $content .= '<h3>' . esc_html($production) . '</h3>';

                // create anchor
                $content .= '<a name="' . esc_attr($year . '-' . $production) . '"></a>';

                // Career table
                $content .= '<h4>Career Distribution</h4>';
                $content .= '<table class="wp-list-table widefat fixed striped">';
                $content .= '<thead><tr><th>Career</th><th>Count</th><th>Chart</th></tr></thead><tbody>';
                $careerTotal = array_sum($stats['Career']);
                foreach ($stats['Career'] as $career => $count) {
                    $barLength = $careerTotal > 0 ? round(($count / $careerTotal) * 20) : 0;
                    $percentage = $careerTotal > 0 ? round(($count / $careerTotal) * 100, 1) : 0;
                    $bar = str_repeat('█', $barLength);
                    $content .= '<tr><td>' . esc_html($career) . '</td><td>' . esc_html($count) . '</td><td style="font-family:monospace;width:200px;">' . $bar . ' ' . $percentage . '%</td></tr>';
                }
                $content .= '</tbody></table>';
                
                // Team table
                $content .= '<h4>Team Distribution</h4>';
                $content .= '<table class="wp-list-table widefat fixed striped">';
                $content .= '<thead><tr><th>Team</th><th>Count</th><th>Chart</th></tr></thead><tbody>';
                $teamTotal = array_sum($stats['Team']);
                foreach ($stats['Team'] as $team => $count) {
                    $barLength = $teamTotal > 0 ? round(($count / $teamTotal) * 20) : 0;
                    $percentage = $teamTotal > 0 ? round(($count / $teamTotal) * 100, 1) : 0;
                    $bar = str_repeat('█', $barLength);
                    $content .= '<tr><td>' . esc_html($team) . '</td><td>' . esc_html($count) . '</td><td style="font-family:monospace;width:200px;">' . $bar . ' ' . $percentage . '%</td></tr>';
                }
                $content .= '</tbody></table>';
                
                // Grad table
                $content .= '<h4>Graduation Year Distribution</h4>';
                $content .= '<table class="wp-list-table widefat fixed striped">';
                $content .= '<thead><tr><th>Grad Year</th><th>Count</th><th>Chart</th></tr></thead><tbody>';
                $gradTotal = array_sum($stats['Grad']);
                foreach ($stats['Grad'] as $grad => $count) {
                    $barLength = $gradTotal > 0 ? round(($count / $gradTotal) * 20) : 0;
                    $percentage = $gradTotal > 0 ? round(($count / $gradTotal) * 100, 1) : 0;
                    $bar = str_repeat('█', $barLength);
                    $content .= '<tr><td>' . esc_html($grad) . '</td><td>' . esc_html($count) . '</td><td style="font-family:monospace;width:200px;">' . $bar . ' ' . $percentage . '%</td></tr>';
                }
                $content .= '</tbody></table><br>';
            }
        }

        return $content;
    }
}
