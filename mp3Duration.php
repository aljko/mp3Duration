<?php
    function getMp3Duration($file) {
        // Ouvre le fichier en mode binaire pour lecture
        $handle = fopen($file, 'rb');
        // Initialise la durée à 0
        $duration = 0;
    
        // Boucle jusqu'à la fin du fichier
        while (!feof($handle)) {
            // Lit les 4 premiers octets de l'en-tête de la trame
            $frameHeader = fread($handle, 4);
            // Si moins de 4 octets sont lus, on sort de la boucle
            if (strlen($frameHeader) < 4) {
                break;
            }
    
            // Décompresse les 4 octets en un entier non signé
            $frameHeader = unpack('N', $frameHeader)[1];
            // Vérifie si c'est un en-tête de trame valide
            if (($frameHeader & 0xFFE00000) != 0xFFE00000) {
                // Si ce n'est pas valide, passe à la trame suivante
                continue;
            }
    
            // Extrait l'index du débit binaire
            $bitrateIndex = ($frameHeader >> 12) & 0xF;
            // Tableau de correspondance des débits binaires
            $bitrateLookup = [
                0 => 0, 1 => 32, 2 => 40, 3 => 48, 4 => 56, 5 => 64, 6 => 80, 7 => 96,
                8 => 112, 9 => 128, 10 => 160, 11 => 192, 12 => 224, 13 => 256, 14 => 320, 15 => 0
            ];
            // Convertit l'index en débit binaire en kbps
            $bitrate = $bitrateLookup[$bitrateIndex] * 1000;
    
            // Extrait l'index de la fréquence d'échantillonnage
            $samplingRateIndex = ($frameHeader >> 10) & 0x3;
            // Tableau de correspondance des fréquences d'échantillonnage
            $samplingRateLookup = [0 => 44100, 1 => 48000, 2 => 32000, 3 => 0];
            // Convertit l'index en fréquence d'échantillonnage
            $samplingRate = $samplingRateLookup[$samplingRateIndex];
    
            // Si le débit binaire ou la fréquence d'échantillonnage est invalide, passe à la trame suivante
            if ($bitrate == 0 || $samplingRate == 0) {
                continue;
            }
    
            // Extrait le bit de bourrage
            $padding = ($frameHeader >> 9) & 0x1;
            // Calcule la longueur de la trame
            $frameLength = (144 * $bitrate / $samplingRate) + $padding;
            // Ajoute la durée de la trame à la durée totale
            $duration += (1152 / $samplingRate);
    
            // Avance le pointeur de fichier à la prochaine trame
            fseek($handle, $frameLength - 4, SEEK_CUR);
        }
    
        // Ferme le fichier
        fclose($handle);
        // Retourne la durée totale
        return $duration;
    }
    

    $file = 'audio.mp3';
    $duration = getMp3Duration($file);
    echo "La durée du fichier MP3 est de " . ceil($duration) . "secondes";
    ?>
