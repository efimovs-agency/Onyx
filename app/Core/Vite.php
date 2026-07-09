<?php

namespace App\Core;

class Vite {
    public static function assets($entries) {
        $isDev = true; // Установи false перед деплоем
        $devServer = 'http://localhost:5173';
        
        $entries = (array) $entries;
        $output = '';

        if ($isDev) {
            $output .= "<script type=\"module\" src=\"{$devServer}/@vite/client\"></script>\n";
            
            foreach ($entries as $entry) {
                if (str_ends_with($entry, '.css')) {
                    // Убрали ?t=time(), чтобы не перегружать локальный сервер при спаме F5
                    $output .= "<link rel=\"stylesheet\" href=\"{$devServer}/{$entry}\">\n";
                } else {
                    $output .= "<script type=\"module\" src=\"{$devServer}/{$entry}\"></script>\n";
                }
            }
            return $output;
        }

        // Логика для продакшена
        $manifestPath = __DIR__ . '/../../public/build/.vite/manifest.json';
        
        if (!file_exists($manifestPath)) {
            return '';
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        foreach ($entries as $entry) {
            if (!isset($manifest[$entry])) continue;
            
            $fileData = $manifest[$entry];

            if (isset($fileData['css'])) {
                foreach ($fileData['css'] as $css) {
                    $output .= "<link rel=\"stylesheet\" href=\"/build/{$css}\">\n";
                }
            }

            if (str_ends_with($entry, '.css')) {
                $output .= "<link rel=\"stylesheet\" href=\"/build/{$fileData['file']}\">\n";
            } else {
                $output .= "<script type=\"module\" src=\"/build/{$fileData['file']}\"></script>\n";
            }
        }

        return $output;
    }
}